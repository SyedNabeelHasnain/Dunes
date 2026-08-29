<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Tour;
use App\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function __construct()
    {
        $this->ensureSchema();
    }

    /**
     * Ensure coupons and coupon_usages tables exist on production.
     */
    protected function ensureSchema(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('coupons') || !\Illuminate\Support\Facades\Schema::hasTable('coupon_usages')) {
                $migration = require database_path('migrations/2026_08_30_000001_create_coupons_table.php');
                $migration->up();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Coupon schema auto-creation error: " . $e->getMessage());
        }
    }

    /**
     * Display a listing of coupons with statistics.
     */
    public function index(Request $request)
    {
        $this->ensureSchema();

        $status = $request->input('status');
        $type = $request->input('type');
        $search = $request->input('search');

        try {
            $query = Coupon::with(['tour', 'tier'])->withCount('usages');

            if ($status) {
                $query->where('status', $status);
            }
            if ($type) {
                $query->where('discount_type', $type);
            }
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $coupons = $query->orderBy('created_at', 'desc')->get();

            // 4 KPI Statistics
            $totalActive = Coupon::where('status', 'active')->count();
            $totalRedemptions = CouponUsage::count();
            $totalDiscountGiven = (float)CouponUsage::sum('discount_amount');
            $totalRevenueViaPromos = (float)CouponUsage::sum('order_final_total');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error loading coupons in index: " . $e->getMessage());
            $coupons = collect([]);
            $totalActive = 0;
            $totalRedemptions = 0;
            $totalDiscountGiven = 0;
            $totalRevenueViaPromos = 0;
        }

        $stats = [
            'total_active' => $totalActive,
            'total_redemptions' => $totalRedemptions,
            'total_discount_given' => $totalDiscountGiven,
            'total_promo_revenue' => $totalRevenueViaPromos,
        ];

        $tours = Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tiers = Tier::where('status', 'active')->orderBy('priority', 'asc')->get();

        return view('admin.coupons.index', compact('coupons', 'stats', 'tours', 'tiers', 'status', 'type', 'search'));
    }

    /**
     * Show form for creating a new coupon.
     */
    public function create()
    {
        $tours = Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tiers = Tier::where('status', 'active')->orderBy('priority', 'asc')->get();
        return view('admin.coupons.create', compact('tours', 'tiers'));
    }

    /**
     * Store a newly created coupon in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed,per_person',
            'discount_value' => 'required|numeric|min:0.01',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_guests' => 'nullable|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'tour_date_from' => 'nullable|date',
            'tour_date_to' => 'nullable|date',
            'tour_id' => 'nullable|integer|exists:tours,id',
            'tier_id' => 'nullable|integer|exists:tiers,id',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper(trim($request->input('code')));
        $data['min_spend'] = $request->input('min_spend') ?: 0.00;
        $data['min_guests'] = $request->input('min_guests') ?: 1;
        $data['usage_limit_per_user'] = $request->input('usage_limit_per_user') ?: 1;
        $data['first_time_only'] = $request->has('first_time_only');
        $data['is_featured'] = $request->has('is_featured');

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$data['code']} created successfully!");
    }

    /**
     * Show form for editing a coupon.
     */
    public function edit(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $tours = Tour::where('status', 'active')->orderBy('priority', 'asc')->get();
        $tiers = Tier::where('status', 'active')->orderBy('priority', 'asc')->get();
        return view('admin.coupons.edit', compact('coupon', 'tours', 'tiers'));
    }

    /**
     * Update the specified coupon in storage.
     */
    public function update(Request $request, int $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed,per_person',
            'discount_value' => 'required|numeric|min:0.01',
            'min_spend' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_guests' => 'nullable|integer|min:1',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date',
            'tour_date_from' => 'nullable|date',
            'tour_date_to' => 'nullable|date',
            'tour_id' => 'nullable|integer|exists:tours,id',
            'tier_id' => 'nullable|integer|exists:tiers,id',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $data['code'] = strtoupper(trim($request->input('code')));
        $data['min_spend'] = $request->input('min_spend') ?: 0.00;
        $data['min_guests'] = $request->input('min_guests') ?: 1;
        $data['usage_limit_per_user'] = $request->input('usage_limit_per_user') ?: 1;
        $data['first_time_only'] = $request->has('first_time_only');
        $data['is_featured'] = $request->has('is_featured');

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$coupon->code} updated successfully!");
    }

    /**
     * Delete a coupon (Soft Delete).
     */
    public function destroy(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $code = $coupon->code;
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', "Coupon {$code} archived successfully.");
    }

    /**
     * AJAX Toggle active / inactive status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->status = $coupon->status === 'active' ? 'inactive' : 'active';
        $coupon->save();

        return response()->json([
            'success' => true,
            'status' => $coupon->status,
            'message' => "Coupon {$coupon->code} status changed to " . ucfirst($coupon->status)
        ]);
    }

    /**
     * 1-Click Duplicate a Coupon.
     */
    public function duplicate(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        
        $newCode = $coupon->code . '_COPY_' . strtoupper(Str::random(3));
        $newCoupon = $coupon->replicate();
        $newCoupon->code = $newCode;
        $newCoupon->name = $coupon->name . ' (Copy)';
        $newCoupon->used_count = 0;
        $newCoupon->status = 'inactive';
        $newCoupon->save();

        return redirect()->route('admin.coupons.edit', $newCoupon->id)->with('success', "Coupon duplicated as {$newCode}. Please configure and activate.");
    }

    /**
     * Fetch customer redemption usages for a specific coupon (AJAX / Modal).
     */
    public function usages(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $usages = CouponUsage::with('booking')
            ->where('coupon_id', $coupon->id)
            ->orderBy('used_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'coupon' => $coupon,
            'usages' => $usages
        ]);
    }

    /**
     * Export Coupons or Usage Log to CSV.
     */
    public function exportCsv(Request $request)
    {
        $fileName = 'dunes-coupons-export-' . date('Y-m-d-His') . '.csv';
        $coupons = Coupon::with(['tour', 'tier'])->withCount('usages')->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID', 'Code', 'Name', 'Type', 'Value', 'Min Spend', 'Max Discount', 'Min Guests', 'Usage Limit', 'Redemptions', 'Valid From', 'Valid Until', 'Applicable Tour', 'Applicable Tier', 'First Time Only', 'Status', 'Created Date'];

        $callback = function() use ($coupons, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
            fputcsv($file, $columns);

            foreach ($coupons as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->code,
                    $c->name,
                    ucfirst($c->discount_type),
                    $c->discount_type === 'percentage' ? $c->discount_value . '%' : 'AED ' . number_format($c->discount_value, 2),
                    'AED ' . number_format($c->min_spend, 2),
                    $c->max_discount ? 'AED ' . number_format($c->max_discount, 2) : 'No Cap',
                    $c->min_guests,
                    $c->usage_limit ?: 'Unlimited',
                    $c->used_count,
                    $c->valid_from ? $c->valid_from->format('Y-m-d H:i') : 'Immediate',
                    $c->valid_until ? $c->valid_until->format('Y-m-d H:i') : 'Never',
                    $c->tour ? $c->tour->name : 'All Tours',
                    $c->tier ? $c->tier->display_name : 'All Tiers',
                    $c->first_time_only ? 'Yes' : 'No',
                    ucfirst($c->status),
                    $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
