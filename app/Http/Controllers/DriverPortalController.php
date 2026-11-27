<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DriverPortalController extends Controller
{
    /**
     * Mobile-friendly driver daily pickup manifest.
     */
    public function showRunSheet(Request $request, $token)
    {
        $driver = Driver::with(['defaultVehicle'])
            ->where('access_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $todayDubai = Carbon::now('Asia/Dubai')->startOfDay();
        $minDate = $todayDubai->copy()->subDays(7);
        $maxDate = $todayDubai->copy()->addDays(14);

        $rawDate = trim((string) $request->input('date', ''));
        if ($rawDate === '') {
            $targetDate = $todayDubai->copy();
        } else {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawDate)) {
                abort(400, 'Invalid date format.');
            }
            $targetDate = Carbon::createFromFormat('Y-m-d', $rawDate, 'Asia/Dubai')->startOfDay();
            if ($targetDate->lt($minDate) || $targetDate->gt($maxDate)) {
                abort(403, 'Requested run-sheet date is outside the permitted operational window (-7 to +14 days).');
            }
        }
        $selectedDate = $targetDate->format('Y-m-d');

        $bookings = Booking::with(['tour', 'tier', 'addons', 'vehicle'])
            ->where('driver_id', $driver->id)
            ->whereDate('tour_date', $selectedDate)
            ->whereIn('status', ['confirmed', 'completed', 'pending'])
            ->orderBy('pickup_location')
            ->get();

        $totalGuests = $bookings->sum(fn ($b) => (int) $b->adults + (int) $b->children);
        $totalCashToCollect = $bookings->where('payment_status', '!=', 'paid')->sum('balance_due');
        $pageRobots = 'noindex, nofollow';

        return response()
            ->view('driver.run-sheet', compact('driver', 'selectedDate', 'targetDate', 'bookings', 'totalGuests', 'totalCashToCollect', 'token', 'pageRobots'))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * Update pickup status from driver mobile portal.
     */
    public function updateStatus(Request $request, $token, $bookingId)
    {
        $driver = Driver::where('access_token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        $todayDubai = Carbon::now('Asia/Dubai')->startOfDay();
        $minDate = $todayDubai->copy()->subDays(7)->toDateString();
        $maxDate = $todayDubai->copy()->addDays(14)->toDateString();

        $booking = Booking::where('id', $bookingId)
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('tour_date', '>=', $minDate)
            ->whereDate('tour_date', '<=', $maxDate)
            ->firstOrFail();

        $request->validate([
            'status' => 'nullable|in:scheduled,en_route,arrived,picked_up,completed,no_show',
            'pickup_status' => 'nullable|in:scheduled,en_route,arrived,picked_up,completed,no_show',
            'driver_notes' => 'nullable|string|max:1000',
        ]);

        $status = $request->input('pickup_status', $request->input('status'));
        if ($status) {
            $booking->pickup_status = $status;
        }

        if ($request->has('driver_notes')) {
            $booking->driver_notes = $request->input('driver_notes');
        }

        $booking->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $booking->pickup_status]);
        }

        return back()->with('success', "Pickup status updated to {$booking->pickup_status}.");
    }
}
