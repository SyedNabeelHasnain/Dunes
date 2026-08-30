<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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

        $bookings = Booking::with(['tour', 'tier', 'addons'])
            ->whereDate('tour_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

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

        foreach ($bookings as $b) {
            $totalAdults += (int)$b->adults;
            $totalChildren += (int)$b->children;
            $totalGuests += ((int)$b->adults + (int)$b->children);

            $loc = strtolower($b->pickup_location . ' ' . ($b->special_requests ?? ''));
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

        // Vehicles estimation (Standard 4x4 Land Cruiser capacity is 6-7 guests)
        $vehiclesNeeded = ceil($totalGuests / 6);

        $stats = [
            'total_bookings' => $bookings->count(),
            'total_guests' => $totalGuests,
            'total_adults' => $totalAdults,
            'total_children' => $totalChildren,
            'vehicles_needed' => max(1, $vehiclesNeeded),
            'confirmed_count' => $bookings->where('status', 'confirmed')->count(),
            'pending_count' => $bookings->where('status', 'pending')->count(),
        ];

        return view('admin.operations.index', compact('selectedDate', 'targetDate', 'bookings', 'zones', 'stats'));
    }

    /**
     * Update driver & vehicle assignment for a booking.
     */
    public function assignDriver(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'pickup_time' => 'nullable|string|max:100',
            'driver_name' => 'nullable|string|max:100',
            'driver_phone' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:50',
        ]);

        $booking->pickup_time = $request->input('pickup_time');
        
        $driverInfo = array_filter([
            'Driver: ' . $request->input('driver_name'),
            'Phone: ' . $request->input('driver_phone'),
            'Plate: ' . $request->input('vehicle_plate'),
        ]);

        if (!empty($driverInfo)) {
            $notes = $booking->special_requests ?: '';
            $cleanNotes = preg_replace('/\[DISPATCH:.*?\]/s', '', $notes);
            $booking->special_requests = trim($cleanNotes . "\n[DISPATCH: " . implode(' | ', $driverInfo) . "]");
        }

        $booking->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Driver dispatch info updated.']);
        }

        return back()->with('success', 'Driver and pickup time assigned successfully.');
    }

    /**
     * Export daily operations manifest CSV.
     */
    public function exportManifest(Request $request)
    {
        $selectedDate = $request->input('date', now()->format('Y-m-d'));
        $bookings = Booking::with(['tour', 'tier', 'addons'])
            ->whereDate('tour_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="dunes-dispatch-manifest-' . $selectedDate . '.csv"',
        ];

        $callback = function () use ($bookings, $selectedDate) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Date', 'Reference', 'Customer Name', 'Phone', 'Email',
                'Pickup Location', 'Pickup Time', 'Tour Name', 'Tier',
                'Adults', 'Children', 'Total Guests', 'Addons', 'Total (AED)',
                'Payment Status', 'Booking Status'
            ]);

            foreach ($bookings as $b) {
                $addonsStr = $b->addons->pluck('addon_name')->implode(', ');
                fputcsv($file, [
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
                    $b->status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}