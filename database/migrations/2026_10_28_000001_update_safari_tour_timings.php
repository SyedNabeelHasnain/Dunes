<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Evening Desert Safari (Pickup: 2:30 PM to 3:00 PM, Drop Off: 9:00 PM to 9:30 PM)
        DB::table('tours')
            ->where('slug', 'evening-desert-safari-dubai')
            ->orWhere('id', 1)
            ->update([
                'pickup_time' => '2:30 PM - 3:00 PM',
                'dropoff_time' => '9:00 PM - 9:30 PM',
            ]);

        // 2. Morning Desert Safari (Pickup: 7:00 AM to 7:30 AM, Drop Off: 10:30 AM to 11:00 AM)
        DB::table('tours')
            ->where('slug', 'morning-desert-safari-dubai')
            ->orWhere('id', 2)
            ->update([
                'pickup_time' => '7:00 AM - 7:30 AM',
                'dropoff_time' => '10:30 AM - 11:00 AM',
                'duration' => '4 Hours',
            ]);

        // 3. Overnight Desert Safari (Pickup: 2:45 PM to 3:15 PM, Drop Off: 6:30 AM to 7:00 AM Next Day)
        DB::table('tours')
            ->where('slug', 'overnight-desert-safari-dubai')
            ->orWhere('id', 3)
            ->update([
                'pickup_time' => '2:45 PM - 3:15 PM',
                'dropoff_time' => '6:30 AM - 7:00 AM (next day)',
                'duration' => '16 Hours',
            ]);

        // 4. Desert Safari with Quad Biking (Pickup: 2:00 PM to 2:30 PM, Drop Off: 9:00 PM to 9:30 PM)
        DB::table('tours')
            ->where('slug', 'desert-safari-quad-biking-dubai')
            ->orWhere('id', 4)
            ->update([
                'pickup_time' => '2:00 PM - 2:30 PM',
                'dropoff_time' => '9:00 PM - 9:30 PM',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
