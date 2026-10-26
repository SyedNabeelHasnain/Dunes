<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AdminDriverController extends Controller
{
    /**
     * Display a listing of drivers.
     */
    public function index(Request $request)
    {
        $query = Driver::with(['defaultVehicle', 'bookings' => function ($q) {
            $q->whereDate('tour_date', now()->format('Y-m-d'))
              ->whereIn('status', ['confirmed', 'completed', 'pending']);
        }]);

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('license_number', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $drivers = $query->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total' => Driver::count(),
            'active' => Driver::where('status', 'active')->count(),
            'on_leave' => Driver::where('status', 'on_leave')->count(),
            'inactive' => Driver::where('status', 'inactive')->count(),
        ];

        return view('admin.drivers.index', compact('drivers', 'stats'));
    }

    /**
     * Show the form for creating a new driver.
     */
    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->orderBy('model_name')->get();

        return view('admin.drivers.create', compact('vehicles'));
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'phone' => 'required|string|max:50|unique:drivers,phone',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:3072',
            'languages' => 'nullable|array',
            'status' => 'required|in:active,on_leave,inactive',
            'default_vehicle_id' => 'nullable|exists:vehicles,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'visa_expiry_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $uploadDir = public_path('images/drivers');
            if (! file_exists($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = safe_uploaded_image_ext($file, 'jpg');
            $filename = 'captain-' . Str::slug($data['name']) . '-' . time() . '.' . $ext;
            $file->move($uploadDir, $filename);
            $data['photo'] = 'images/drivers/' . $filename;
        }

        $data['access_token'] = Str::random(32);
        $data['rating'] = $data['rating'] ?? 5.00;

        $driver = Driver::create($data);

        // If a default vehicle was assigned, link reciprocal relationship
        if (!empty($data['default_vehicle_id'])) {
            Vehicle::where('id', $data['default_vehicle_id'])->update(['default_driver_id' => $driver->id]);
        }

        return redirect()->route('admin.drivers.index')->with('success', "Safari Captain {$driver->name} registered successfully.");
    }

    /**
     * Display driver dossier with upcoming trips.
     */
    public function show($id)
    {
        $driver = Driver::with(['defaultVehicle'])->findOrFail($id);

        $today = now()->format('Y-m-d');
        $todayBookings = Booking::with(['tour', 'tier'])
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $upcomingBookings = Booking::with(['tour', 'tier'])
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', '>', $today)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('tour_date')
            ->take(10)
            ->get();

        $lifetimeStats = [
            'total_bookings' => Booking::where('driver_id', $driver->id)->whereIn('status', ['confirmed', 'completed'])->count(),
            'total_guests' => Booking::where('driver_id', $driver->id)->whereIn('status', ['confirmed', 'completed'])->selectRaw('SUM(adults + children) as guests')->value('guests') ?? 0,
            'cash_collected' => Booking::where('driver_id', $driver->id)->whereIn('status', ['confirmed', 'completed'])->where('payment_status', 'paid')->sum('total'),
        ];

        return view('admin.drivers.show', compact('driver', 'todayBookings', 'upcomingBookings', 'lifetimeStats'));
    }

    /**
     * Show the form for editing the specified driver.
     */
    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        $vehicles = Vehicle::orderBy('model_name')->get();

        return view('admin.drivers.edit', compact('driver', 'vehicles'));
    }

    /**
     * Update the specified driver.
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'license_number' => 'nullable|string|max:100',
            'phone' => 'required|string|max:50|unique:drivers,phone,' . $id,
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,avif|max:3072',
            'languages' => 'nullable|array',
            'status' => 'required|in:active,on_leave,inactive',
            'default_vehicle_id' => 'nullable|exists:vehicles,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'license_expiry_date' => 'nullable|date',
            'visa_expiry_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:1|max:5',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $uploadDir = public_path('images/drivers');
            if (! file_exists($uploadDir)) {
                @mkdir($uploadDir, 0755, true);
            }
            $ext = safe_uploaded_image_ext($file, 'jpg');
            $filename = 'captain-' . Str::slug($data['name']) . '-' . time() . '.' . $ext;
            $file->move($uploadDir, $filename);
            $data['photo'] = 'images/drivers/' . $filename;
        }

        $driver->update($data);

        if (!empty($data['default_vehicle_id'])) {
            Vehicle::where('id', $data['default_vehicle_id'])->update(['default_driver_id' => $driver->id]);
        }

        return redirect()->route('admin.drivers.index')->with('success', "Driver {$driver->name} updated successfully.");
    }

    /**
     * Remove the specified driver.
     */
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);

        // Check for active upcoming bookings
        $hasUpcoming = Booking::where('driver_id', $driver->id)
            ->whereDate('tour_date', '>=', now()->format('Y-m-d'))
            ->whereIn('status', ['confirmed', 'pending'])
            ->exists();

        if ($hasUpcoming) {
            return back()->with('error', "Cannot delete {$driver->name}: this captain has active upcoming tour departures assigned. Please reassign those bookings first.");
        }

        $driver->delete();

        return redirect()->route('admin.drivers.index')->with('success', "Driver record archived successfully.");
    }

    /**
     * Toggle driver status.
     */
    public function toggleStatus($id)
    {
        $driver = Driver::findOrFail($id);
        $next = $driver->status === 'active' ? 'inactive' : 'active';

        $driver->status = $next;
        $driver->save();

        return back()->with('success', "Captain {$driver->name} status changed to {$next}.");
    }

    /**
     * Driver daily run sheet (for admin and driver link).
     */
    public function runSheet($id, Request $request)
    {
        $driver = Driver::with(['defaultVehicle'])->findOrFail($id);
        $date = $request->input('date', now()->format('Y-m-d'));
        $targetDate = Carbon::parse($date);

        $bookings = Booking::with(['tour', 'tier', 'addons', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', $date)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $totalGuests = $bookings->sum(fn ($b) => (int) $b->adults + (int) $b->children);
        $totalCashToCollect = $bookings->where('payment_status', '!=', 'paid')->sum('balance_due');

        return view('admin.drivers.run-sheet', compact('driver', 'date', 'targetDate', 'bookings', 'totalGuests', 'totalCashToCollect'));
    }
}
