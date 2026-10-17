<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Correct Tour Meta Titles & Descriptions (CAT-01, CAT-03)
        if (Schema::hasTable('tours')) {
            DB::table('tours')->where('id', 1)->update([
                'meta_title' => 'Evening Desert Safari Dubai 2026 | 4x4 Dune Bashing & BBQ | From AED 79'
            ]);

            DB::table('tours')->where('id', 2)->update([
                'meta_title' => 'Morning Desert Safari Dubai 2026 | Morning Dune Bashing | From AED 140',
                'meta_desc' => '🌅 Best Morning Desert Safari in Dubai\'s Red Dunes. Morning Dune Bashing, Sandboarding & Camel Rides. Complimentary 4x4 Hotel Pickup. Book Direct & Save!'
            ]);
        }

        // 2. Correct Preimum Typo in Tiers (CAT-05)
        if (Schema::hasTable('tiers')) {
            DB::table('tiers')->where('id', 5)->update([
                'name' => 'Premium 2 Adults',
                'display_name' => 'Premium 2 Adults',
                'slug' => 'overnight-desert-safari-premium-2-adults'
            ]);

            DB::table('tiers')->where('id', 7)->update([
                'name' => 'Premium 6 Adults',
                'display_name' => 'Premium 6 Adults',
                'slug' => 'overnight-desert-safari-premium-6-adults'
            ]);
        }

        // 3. Set Price Type to per tent for Overnight Safari Tiers (CAT-06)
        if (Schema::hasTable('tour_tiers')) {
            DB::table('tour_tiers')->whereIn('id', [26, 27, 28])->update([
                'price_type' => 'per tent'
            ]);
        }

        // 4. Remove Cross-Tour Bleed from tour_content (CAT-04, CAT-08)
        if (Schema::hasTable('tour_content')) {
            $toRemove = [
                361, 362, 365, 366, 384, // Tour 1 bleed
                393, 394, 397, 398, 404, 417, // Tour 2 bleed
                438, 439, 442, 443, 463, // Tour 3 bleed
                487, 489, 505, // Tour 4 bleed
                507, 516, 531, // Tour 5 bleed
                543, 544 // Tour 6 bleed
            ];
            DB::table('tour_content')->whereIn('id', $toRemove)->delete();
        }

        // 5. Update FAQ #9 with Standard vs Premium vs VIP distinction (CAT-02)
        if (Schema::hasTable('faqs')) {
            DB::table('faqs')->where('id', 9)->update([
                'answer' => 'Classic/Standard includes shared 4x4 transfers and open camp buffet seating. Premium includes shared 4x4 transfers with reserved table seating and dedicated waiter service at the camp. VIP or Private 4x4 provides an exclusive, dedicated vehicle exclusively for your personal party with custom pickup timing, plus full VIP lounge access and table service at the desert camp.'
            ]);
        }
    }

    public function down(): void
    {
        // Reversible rollback
        if (Schema::hasTable('tours')) {
            DB::table('tours')->where('id', 1)->update([
                'meta_title' => 'Evening Desert Safari Dubai 2026 | 4x4 Dune Bashing & BBQ | From AED 99'
            ]);
            DB::table('tours')->where('id', 2)->update([
                'meta_title' => 'Morning Desert Safari Dubai 2026 | Sunrise Dune Bashing | From AED 120'
            ]);
        }
    }
};
