<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create coupons table
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 50)->unique()->index();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed', 'per_person'])->default('percentage');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('min_spend', 10, 2)->default(0.00);
                $table->decimal('max_discount', 10, 2)->nullable();
                $table->unsignedSmallInteger('min_guests')->default(1);
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('usage_limit_per_user')->default(1);
                $table->unsignedInteger('used_count')->default(0);
                $table->dateTime('valid_from')->nullable();
                $table->dateTime('valid_until')->nullable();
                $table->date('tour_date_from')->nullable();
                $table->date('tour_date_to')->nullable();
                $table->unsignedInteger('tour_id')->nullable()->index();
                $table->unsignedInteger('tier_id')->nullable()->index();
                $table->boolean('first_time_only')->default(false);
                $table->boolean('is_featured')->default(false);
                $table->enum('status', ['active', 'inactive'])->default('active')->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Create coupon_usages table
        if (!Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('coupon_id')->index();
                $table->unsignedInteger('booking_id')->nullable()->index();
                $table->string('booking_reference', 50)->nullable()->index();
                $table->string('customer_name', 255)->nullable();
                $table->string('customer_email', 255)->index();
                $table->string('customer_phone', 50)->nullable();
                $table->decimal('discount_amount', 10, 2)->default(0.00);
                $table->decimal('order_subtotal', 10, 2)->default(0.00);
                $table->decimal('order_final_total', 10, 2)->default(0.00);
                $table->dateTime('used_at');
                $table->timestamps();
            });
        }

        // 3. Add coupon columns to bookings table
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('bookings', 'coupon_id')) {
                    $table->unsignedInteger('coupon_id')->nullable()->index()->after('special_requests');
                }
                if (!Schema::hasColumn('bookings', 'coupon_code')) {
                    $table->string('coupon_code', 50)->nullable()->index()->after('coupon_id');
                }
                if (!Schema::hasColumn('bookings', 'discount_type')) {
                    $table->string('discount_type', 20)->nullable()->after('coupon_code');
                }
                if (!Schema::hasColumn('bookings', 'discount_rate')) {
                    $table->decimal('discount_rate', 10, 2)->default(0.00)->after('discount_type');
                }
                if (!Schema::hasColumn('bookings', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0.00)->after('discount_rate');
                }
                if (!Schema::hasColumn('bookings', 'original_total')) {
                    $table->decimal('original_total', 10, 2)->default(0.00)->after('discount_amount');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                $cols = ['coupon_id', 'coupon_code', 'discount_type', 'discount_rate', 'discount_amount', 'original_total'];
                $existing = array_filter($cols, function($c) { return Schema::hasColumn('bookings', $c); });
                if (!empty($existing)) {
                    $table->dropColumn($existing);
                }
            });
        }

        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
