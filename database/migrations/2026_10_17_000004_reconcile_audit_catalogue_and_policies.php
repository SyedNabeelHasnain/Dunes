<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tours')) {
            DB::table('tours')->where('id', 1)->update([
                'meta_desc' => '🔥 #1 Rated Red Dunes Evening Desert Safari in Dubai. Dune Bashing (15-45 Mins by Tier), Camel Rides, Sandboarding, BBQ Buffet & 3 Live Shows. 4x4 Hotel Pickup. Reserve Direct!',
            ]);

            DB::table('tours')->where('id', 2)->update([
                'meta_desc' => '🌅 Best Morning Desert Safari in Dubai\'s Red Dunes. 25-30 Min High Dune Bashing, Sandboarding & Camel Rides. Crisp Morning Photography & 4x4 Pickup!',
            ]);

            DB::table('tours')->where('id', 3)->update([
                'meta_title' => 'Overnight Desert Safari Dubai 2026 | Bedouin Camping & Breakfast | From AED 798 (2 Guests)',
                'meta_desc' => '⛺ Authentic Overnight Desert Camping in Dubai. Sunset Dune Bashing, Stargazing, Shisha, Deluxe Private Tents & Fresh Breakfast. Book Your Desert Stay Direct!',
            ]);
        }

        if (Schema::hasTable('tour_content')) {
            DB::table('tour_content')
                ->where('tour_id', 4)
                ->whereIn('content_id', [27, 28, 29])
                ->delete();
        }

        if (Schema::hasTable('addons')) {
            DB::table('addons')->where('id', 5)->update([
                'name' => 'Arabic Royal Studio Portrait & 3 Prints',
                'description' => 'Studio lighting portrait in traditional royal Emirati attire with 3 mounted prints and high-res digital gallery delivered to your phone.',
            ]);

            DB::table('addons')->where('id', 6)->update([
                'name' => 'Intricate Bridal & Full-Hand Henna Art',
                'description' => 'Elaborate multi-pattern bridal henna artwork extending from fingertips to wrists on both hands by master henna artists (standard single-motif hand henna is already included free).',
            ]);

            DB::table('addons')->where('id', 10)->update([
                'name' => 'Pro Sandboarding Masterclass & Wax Board',
                'description' => 'Professional fiberglass speed board with ski wax and dedicated 1-on-1 slope coaching for advanced carved runs (standard wooden sandboard sliding is already included free).',
            ]);
        }

        if (Schema::hasTable('legal_items')) {
            DB::table('legal_items')->where('id', 119)->update([
                'content' => 'Cash on Pickup Option: Guests selecting Cash on Pickup pay AED 0.00 online at checkout; 100% of the tour total is collected in cash (AED, USD, EUR, GBP) by your licensed safari captain upon hotel pickup. Guests choosing Advance Deposit pay 25% online and settle the remaining 75% in cash at pickup.',
                'content_ar' => 'خيار الدفع نقداً عند الحضور: عند اختيار الدفع نقداً عند الاستقبال، لا يُدفع أي مبلغ إلكترونياً عند إتمام الحجز (0.00 درهم)، ويتم سداد 100% من المبلغ نقداً للمرشد السياحي عند الوصول إلى الفندق. أو يمكن للضيوف دفع عربون إلكتروني بنسبة 25% وسداد المتبقي 75% نقداً.',
            ]);

            DB::table('legal_items')->where('id', 149)->update([
                'content' => 'Minimum Operating Age: Drivers of quad bikes and buggies must be at least 16 years old. No road driving licence is required on our designated off-road desert tracks. Children aged 6 and older may ride as passengers in 2-seater and 4-seater buggies or designated quad tours with an adult driver.',
                'content_ar' => 'السن الأدنى للقيادة: يجب ألا يقل عمر سائق الدراجة أو البجي عن 16 عاماً، ولا يلزم وجود رخصة قيادة على المسارات الصحراوية المغلقة. ويجوز للأطفال من سن 6 سنوات فأكثر الركوب كمرافقين في عربات البجي ذات المقعدين أو 4 مقاعد أو جولات الكواد المزدوجة مع سائق بالغ.',
            ]);
        }

        if (Schema::hasTable('tour_tiers')) {
            DB::table('tour_tiers')->where('tour_id', 8)->update([
                'price_type' => 'per buggy',
            ]);
        }
    }

    public function down(): void
    {
    }
};
