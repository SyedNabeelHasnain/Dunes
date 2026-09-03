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
        // Fix past records where payment_status is unpaid but payment_amount was set to total/advance
        DB::table('bookings')
            ->where('payment_status', 'unpaid')
            ->update([
                'payment_amount' => 0.00,
                'balance_due' => DB::raw('total')
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse operation needed
    }
};