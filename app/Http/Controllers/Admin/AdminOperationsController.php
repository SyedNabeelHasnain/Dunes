<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminOperationsController extends Controller
{
    /**
     * Display the Daily Tour Operations & Driver Dispatch Hub.
     */
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $targetDate = Carbon::parse($selectedDate)->startOfDay();

        $bookings = Booking::with(['tour', 'tier', 'addons', 'driver.defaultVehicle', 'vehicle'])
            ->whereDate('tour_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $drivers = Driver::with('defaultVehicle')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where('status', 'active')
            ->orderBy('model_name')
            ->get();

        // Calculate current guest load per driver for this date
        $driverLoads = Booking::whereDate('tour_date', $selectedDate)
            ->whereNotNull('driver_id')
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->selectRaw('driver_id, SUM(adults + children) as guest_count')
            ->groupBy('driver_id')
            ->pluck('guest_count', 'driver_id')
            ->toArray();

        // Cluster into Dubai Logistics Pickup Zones
        $zones = [
            'Dubai Marina & JBR' => [],
            'Downtown & Business Bay' => [],
            'Palm Jumeirah & Al Sufouh' => [],
            'Deira & Bur Dubai' => [],
            'Al Barsha, JLT & Emirates Hills' => [],
            'Other Dubai Areas' => [],
        ];

        $totalGuests = 0;
        $totalAdults = 0;
        $totalChildren = 0;
        $totalInfants = 0;
        $assignedCount = 0;

        foreach ($bookings as $b) {
            $adults = (int) $b->adults;
            $children = (int) $b->children;
            $infants = (int) ($b->infants ?? 0);

            $totalAdults += $adults;
            $totalChildren += $children;
            $totalInfants += $infants;
            $totalGuests += ($adults + $children);

            if ($b->driver_id || $b->assigned_driver_name) {
                $assignedCount++;
            }

            $loc = strtolower($b->pickup_location.' '.($b->special_requests ?? ''));
            if (str_contains($loc, 'marina') || str_contains($loc, 'jbr') || str_contains($loc, 'beach residence')) {
                $zones['Dubai Marina & JBR'][] = $b;
            } elseif (str_contains($loc, 'downtown') || str_contains($loc, 'burj') || str_contains($loc, 'business bay') || str_contains($loc, 'dubai mall')) {
                $zones['Downtown & Business Bay'][] = $b;
            } elseif (str_contains($loc, 'palm') || str_contains($loc, 'atlantis') || str_contains($loc, 'sufouh')) {
                $zones['Palm Jumeirah & Al Sufouh'][] = $b;
            } elseif (str_contains($loc, 'deira') || str_contains($loc, 'bur dubai') || str_contains($loc, 'creek') || str_contains($loc, 'gold souk')) {
                $zones['Deira & Bur Dubai'][] = $b;
            } elseif (str_contains($loc, 'barsha') || str_contains($loc, 'jlt') || str_contains($loc, 'lake towers') || str_contains($loc, 'emirates')) {
                $zones['Al Barsha, JLT & Emirates Hills'][] = $b;
            } else {
                $zones['Other Dubai Areas'][] = $b;
            }
        }

        // Vehicles estimation (Standard 4x4 Land Cruiser capacity is 6 guests)
        $vehiclesNeeded = $totalGuests > 0 ? (int) ceil($totalGuests / 6) : 0;

        $stats = [
            'total_bookings' => $bookings->count(),
            'total_guests' => $totalGuests,
            'total_adults' => $totalAdults,
            'total_children' => $totalChildren,
            'total_infants' => $totalInfants,
            'vehicles_needed' => $vehiclesNeeded,
            'confirmed_count' => $bookings->where('status', 'confirmed')->count(),
            'pending_count' => $bookings->where('status', 'pending')->count(),
            'assigned_count' => $assignedCount,
            'unassigned_count' => max(0, $bookings->count() - $assignedCount),
        ];

        return view('admin.operations.index', compact('selectedDate', 'targetDate', 'bookings', 'zones', 'stats', 'drivers', 'vehicles', 'driverLoads'));
    }

    /**
     * Update driver & vehicle assignment for a booking.
     */
    public function assignDriver(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'driver_id' => 'nullable|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'pickup_time_slot' => 'nullable|string|max:100',
            'pickup_status' => 'nullable|in:scheduled,en_route,arrived,picked_up,completed,no_show',
            'driver_notes' => 'nullable|string|max:1000',
            // Backward-compatible fallback fields if submitted via text
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
            'pickup_time' => 'nullable|string|max:100',
        ]);

        $driverId = $request->input('driver_id');
        $vehicleId = $request->input('vehicle_id');
        $pickupTime = $request->input('pickup_time_slot') ?: $request->input('pickup_time');

        // If driver selected without explicit vehicle, auto-assign their default vehicle
        if ($driverId && !$vehicleId) {
            $driver = Driver::find($driverId);
            $vehicleId = $driver?->default_vehicle_id;
        }

        // Handle text fallback if driver_name was typed instead of selected
        if (!$driverId && $request->filled('driver_name')) {
            $foundDriver = Driver::where('name', 'like', '%' . trim($request->input('driver_name')) . '%')->first();
            if ($foundDriver) {
                $driverId = $foundDriver->id;
                $vehicleId = $vehicleId ?: $foundDriver->default_vehicle_id;
            }
        }

        $booking->driver_id = $driverId;
        $booking->vehicle_id = $vehicleId;
        if ($pickupTime) {
            $booking->pickup_time_slot = $pickupTime;
        }
        if ($request->filled('pickup_status')) {
            $booking->pickup_status = $request->input('pickup_status');
        }
        if ($request->filled('driver_notes')) {
            $booking->driver_notes = $request->input('driver_notes');
        }
        if ($driverId) {
            $booking->driver_assigned_at = now();
        }

        // Synchronize backward-compatible [DISPATCH: ...] format in special_requests
        $driverObj = $driverId ? Driver::find($driverId) : null;
        $vehicleObj = $vehicleId ? Vehicle::find($vehicleId) : null;

        $driverName = $driverObj?->name ?: $request->input('driver_name');
        $driverPhone = $driverObj?->phone ?: $request->input('driver_phone');
        $plateNum = $vehicleObj?->short_plate ?: $request->input('vehicle_plate');

        $driverInfo = array_filter([
            $pickupTime ? 'Pickup: ' . $pickupTime : null,
            $driverName ? 'Driver: ' . $driverName : null,
            $driverPhone ? 'Phone: ' . $driverPhone : null,
            $plateNum ? 'Plate: ' . $plateNum : null,
            $request->input('driver_notes') ? 'Notes: ' . $request->input('driver_notes') : null,
        ]);

        if (!empty($driverInfo)) {
            $notes = $booking->special_requests ?: '';
            $cleanNotes = preg_replace('/\[DISPATCH:.*?\]/s', '', $notes);
            $booking->special_requests = trim($cleanNotes . "\n[DISPATCH: " . implode(' | ', $driverInfo) . ']');
        }

        $booking->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Driver dispatch info updated.',
                'driver_name' => $driverObj?->name ?: 'Assigned',
                'vehicle_plate' => $vehicleObj?->short_plate ?: '',
                'pickup_time' => $booking->pickup_time,
            ]);
        }

        return back()->with('success', 'Driver and pickup time assigned successfully.');
    }

    /**
     * Batch assign driver and vehicle to multiple bookings.
     */
    public function batchAssign(Request $request)
    {
        $data = $request->validate([
            'booking_ids' => 'required|array',
            'booking_ids.*' => 'exists:bookings,id',
            'driver_id' => 'required|exists:drivers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'pickup_time_slot' => 'nullable|string|max:100',
        ]);

        $driver = Driver::findOrFail($data['driver_id']);
        $vehicleId = (!empty($data['vehicle_id'])) ? $data['vehicle_id'] : $driver->default_vehicle_id;
        $vehicle = $vehicleId ? Vehicle::find($vehicleId) : null;

        $count = 0;
        foreach ($data['booking_ids'] as $bid) {
            $booking = Booking::find($bid);
            if ($booking) {
                $booking->driver_id = $driver->id;
                $booking->vehicle_id = $vehicleId;
                if (!empty($data['pickup_time_slot'])) {
                    $booking->pickup_time_slot = $data['pickup_time_slot'];
                }
                $booking->driver_assigned_at = now();

                $driverInfo = array_filter([
                    $booking->pickup_time ? 'Pickup: ' . $booking->pickup_time : null,
                    'Driver: ' . $driver->name,
                    'Phone: ' . $driver->phone,
                    $vehicle ? 'Plate: ' . $vehicle->short_plate : null,
                ]);

                $cleanNotes = preg_replace('/\[DISPATCH:.*?\]/s', '', $booking->special_requests ?: '');
                $booking->special_requests = trim($cleanNotes . "\n[DISPATCH: " . implode(' | ', $driverInfo) . ']');
                $booking->save();
                $count++;
            }
        }

        return back()->with('success', "Assigned Captain {$driver->name} to {$count} booking(s) successfully.");
    }

    /**
     * Update pickup status for real-time dispatch tracking.
     */
    public function updatePickupStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'status' => 'required|in:scheduled,en_route,arrived,picked_up,completed,no_show',
        ]);

        $booking->pickup_status = $request->input('status');
        $booking->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $booking->pickup_status]);
        }

        return back()->with('success', "Pickup status updated to {$booking->pickup_status}.");
    }

    /**
     * Driver Performance & Cash Reconciliation Report.
     */
    public function reports(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        $drivers = Driver::with(['defaultVehicle'])->orderBy('name')->get();
        $bookingsByDriver = Booking::whereIn('driver_id', $drivers->pluck('id'))
            ->whereBetween('tour_date', [$fromDate, $toDate])
            ->whereIn('status', ['confirmed', 'completed'])
            ->get()
            ->groupBy('driver_id');

        $reportData = [];
        $totalTripsAll = 0;
        $totalGuestsAll = 0;
        $totalCashAll = 0;

        foreach ($drivers as $d) {
            $bookings = $bookingsByDriver->get($d->id, collect());

            $trips = $bookings->count();
            $adults = $bookings->sum('adults');
            $children = $bookings->sum('children');
            $guests = $adults + $children;
            $cashToCollect = $bookings->where('payment_status', '!=', 'paid')->sum('balance_due');
            $cashCollected = $bookings->where('payment_status', 'paid')->sum('payment_amount');

            $totalTripsAll += $trips;
            $totalGuestsAll += $guests;
            $totalCashAll += $cashToCollect;

            $reportData[] = [
                'driver' => $d,
                'trips' => $trips,
                'adults' => $adults,
                'children' => $children,
                'total_guests' => $guests,
                'cash_to_collect' => $cashToCollect,
                'cash_collected' => $cashCollected,
            ];
        }

        $summary = [
            'total_trips' => $totalTripsAll,
            'total_guests' => $totalGuestsAll,
            'total_cash_to_collect' => $totalCashAll,
            'active_drivers' => $drivers->where('status', 'active')->count(),
        ];

        return view('admin.operations.reports', compact('fromDate', 'toDate', 'reportData', 'summary'));
    }

    /**
     * Export driver performance and cash collection report.
     */
    public function exportReportsCsv(Request $request)
    {
        $fromDate = $request->input('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->endOfMonth()->format('Y-m-d'));

        $drivers = Driver::with(['defaultVehicle'])->orderBy('name')->get();
        $bookingsByDriver = Booking::whereIn('driver_id', $drivers->pluck('id'))
            ->whereBetween('tour_date', [$fromDate, $toDate])
            ->whereIn('status', ['confirmed', 'completed'])
            ->get()
            ->groupBy('driver_id');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="dunes-driver-reconciliation-' . $fromDate . '-to-' . $toDate . '.csv"',
        ];

        $callback = function () use ($drivers, $bookingsByDriver) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Driver Name', 'RTA License', 'Phone', 'Assigned Vehicle',
                'Status', 'Trips Count', 'Adults', 'Children', 'Total Guests',
                'Cash to Collect (AED)', 'Paid Online/Collected (AED)', 'Rating',
            ]);

            foreach ($drivers as $d) {
                $bookings = $bookingsByDriver->get($d->id, collect());

                fputcsv($file, sanitize_csv_row([
                    $d->name,
                    $d->license_number ?: 'N/A',
                    $d->phone,
                    $d->defaultVehicle?->display_name ?: 'None',
                    $d->status,
                    $bookings->count(),
                    $bookings->sum('adults'),
                    $bookings->sum('children'),
                    ($bookings->sum('adults') + $bookings->sum('children')),
                    $bookings->where('payment_status', '!=', 'paid')->sum('balance_due'),
                    $bookings->where('payment_status', 'paid')->sum('payment_amount'),
                    $d->rating,
                ]));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export daily operations manifest CSV.
     */
    public function exportManifest(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $bookings = Booking::with(['tour', 'tier', 'addons', 'driver.defaultVehicle', 'vehicle'])
            ->whereDate('tour_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dunes-dispatch-manifest-'.$selectedDate.'.csv"',
        ];

        $sanitize = static fn (array $row): array => sanitize_csv_row($row);

        $callback = function () use ($bookings, $selectedDate, $sanitize) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'Date', 'Reference', 'Customer Name', 'Phone', 'Email',
                'Pickup Location', 'Pickup Time', 'Tour Name', 'Tier',
                'Adults', 'Children', 'Total Guests', 'Addons', 'Total (AED)',
                'Payment Status', 'Balance Due (AED)', 'Assigned Driver',
                'Driver Phone', 'Vehicle Plate', 'Pickup Status', 'Booking Status',
            ]);

            foreach ($bookings as $b) {
                $addonsStr = $b->addons->pluck('addon_name')->implode(', ');
                fputcsv($file, $sanitize([
                    $selectedDate,
                    $b->reference,
                    $b->name,
                    $b->phone,
                    $b->email,
                    $b->pickup_location,
                    $b->pickup_time ?: 'Pending Assign',
                    $b->tour_name,
                    $b->tier_name,
                    $b->adults,
                    $b->children,
                    ($b->adults + $b->children),
                    $addonsStr ?: 'None',
                    $b->total,
                    $b->payment_status,
                    $b->balance_due,
                    $b->driver?->name ?: $b->assigned_driver_name ?: 'Unassigned',
                    $b->driver?->phone ?: '',
                    $b->vehicle?->short_plate ?: $b->assigned_vehicle_plate ?: '',
                    $b->pickup_status ?: 'scheduled',
                    $b->status,
                ]));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
