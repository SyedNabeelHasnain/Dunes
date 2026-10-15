<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class AdminVehicleController extends Controller
{
    /**
     * Display a listing of fleet vehicles.
     */
    public function index(Request $request)
    {
        $query = Vehicle::with(['defaultDriver', 'bookings' => function ($q) {
            $q->whereDate('tour_date', now()->format('Y-m-d'))
              ->whereIn('status', ['confirmed', 'completed', 'pending']);
        }]);

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('model_name', 'like', "%{$s}%")
                  ->orWhere('plate_number', 'like', "%{$s}%")
                  ->orWhere('plate_code', 'like', "%{$s}%")
                  ->orWhere('dtcm_permit_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $vehicles = $query->orderBy('plate_number')->paginate(15)->withQueryString();

        $stats = [
            'total' => Vehicle::count(),
            'active' => Vehicle::where('status', 'active')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'inactive' => Vehicle::where('status', 'inactive')->count(),
            'total_seats' => Vehicle::where('status', 'active')->sum('passenger_capacity'),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'stats'));
    }

    /**
     * Show the form for creating a new vehicle.
     */
    public function create()
    {
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();

        return view('admin.vehicles.create', compact('drivers'));
    }

    /**
     * Store a newly created vehicle.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'model_name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number',
            'plate_code' => 'required|string|max:20',
            'plate_city' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2015|max:' . (date('Y') + 1),
            'passenger_capacity' => 'nullable|integer|min:1|max:50',
            'color' => 'nullable|string|max:50',
            'status' => 'required|in:active,maintenance,inactive',
            'insurance_expiry_date' => 'nullable|date',
            'mulkiya_expiry_date' => 'nullable|date',
            'dtcm_permit_number' => 'nullable|string|max:100',
            'default_driver_id' => 'nullable|exists:drivers,id',
            'features' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['plate_city'] = $data['plate_city'] ?? 'Dubai';
        $data['year'] = $data['year'] ?? 2024;
        $data['color'] = $data['color'] ?? 'White';
        $data['passenger_capacity'] = $data['passenger_capacity'] ?? 6;

        $vehicle = Vehicle::create($data);

        if (!empty($data['default_driver_id'])) {
            Driver::where('id', $data['default_driver_id'])->update(['default_vehicle_id' => $vehicle->id]);
        }

        return redirect()->route('admin.vehicles.index')->with('success', "Vehicle {$vehicle->display_name} added to fleet successfully.");
    }

    /**
     * Display vehicle details and trip history.
     */
    public function show($id)
    {
        $vehicle = Vehicle::with(['defaultDriver'])->findOrFail($id);

        $today = now()->format('Y-m-d');
        $todayBookings = Booking::with(['tour', 'tier', 'driver'])
            ->where('vehicle_id', $vehicle->id)
            ->whereDate('tour_date', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $upcomingBookings = Booking::with(['tour', 'tier', 'driver'])
            ->where('vehicle_id', $vehicle->id)
            ->whereDate('tour_date', '>', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('tour_date')
            ->take(10)
            ->get();

        return view('admin.vehicles.show', compact('vehicle', 'todayBookings', 'upcomingBookings'));
    }

    /**
     * Show the form for editing the specified vehicle.
     */
    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();

        return view('admin.vehicles.edit', compact('vehicle', 'drivers'));
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'model_name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number,' . $id,
            'plate_code' => 'required|string|max:20',
            'plate_city' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:2015|max:' . (date('Y') + 1),
            'passenger_capacity' => 'nullable|integer|min:1|max:50',
            'color' => 'nullable|string|max:50',
            'status' => 'required|in:active,maintenance,inactive',
            'insurance_expiry_date' => 'nullable|date',
            'mulkiya_expiry_date' => 'nullable|date',
            'dtcm_permit_number' => 'nullable|string|max:100',
            'default_driver_id' => 'nullable|exists:drivers,id',
            'features' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        $vehicle->update($data);

        if (!empty($data['default_driver_id'])) {
            Driver::where('id', $data['default_driver_id'])->update(['default_vehicle_id' => $vehicle->id]);
        }

        return redirect()->route('admin.vehicles.index')->with('success', "Vehicle {$vehicle->display_name} updated successfully.");
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $hasUpcoming = Booking::where('vehicle_id', $vehicle->id)
            ->whereDate('tour_date', '>=', now()->format('Y-m-d'))
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        if ($hasUpcoming) {
            return back()->with('error', "Cannot delete vehicle {$vehicle->plate_number}: it is assigned to upcoming tour departures. Reassign those bookings first.");
        }

        $vehicle->delete();

        return redirect()->route('admin.vehicles.index')->with('success', "Vehicle removed from active fleet.");
    }

    /**
     * Toggle vehicle status (active <-> maintenance <-> inactive).
     */
    public function toggleStatus($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $next = match ($vehicle->status) {
            'active' => 'maintenance',
            'maintenance' => 'inactive',
            default => 'active',
        };

        $vehicle->status = $next;
        $vehicle->save();

        return back()->with('success', "Vehicle {$vehicle->short_plate} status changed to {$next}.");
    }
}
