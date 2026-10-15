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
        // 1. Create Vehicles Table
        Schema::create('vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('model_name', 255);
            $table->string('plate_number', 50)->unique();
            $table->string('plate_code', 20)->default('Dubai');
            $table->string('plate_city', 50)->default('Dubai');
            $table->unsignedSmallInteger('year')->default(2024);
            $table->unsignedTinyInteger('passenger_capacity')->default(6);
            $table->string('color', 50)->default('White');
            $table->string('status', 50)->default('active')->index(); // active, maintenance, inactive
            $table->date('insurance_expiry_date')->nullable();
            $table->date('mulkiya_expiry_date')->nullable(); // Registration card expiry
            $table->string('dtcm_permit_number', 100)->nullable();
            $table->unsignedInteger('default_driver_id')->nullable()->index();
            $table->json('features')->nullable(); // ["4x4", "Roll Cage", "Dual A/C", "First Aid Kit", "GPS"]
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create Drivers Table
        Schema::create('drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('license_number', 100)->nullable();
            $table->string('phone', 50)->index();
            $table->string('email', 255)->nullable();
            $table->string('photo', 255)->nullable();
            $table->json('languages')->nullable(); // ["English", "Arabic", "Urdu"]
            $table->string('status', 50)->default('active')->index(); // active, on_leave, inactive
            $table->unsignedInteger('default_vehicle_id')->nullable()->index();
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->date('visa_expiry_date')->nullable();
            $table->decimal('rating', 3, 2)->default(5.00);
            $table->unsignedInteger('total_trips')->default(0);
            $table->string('access_token', 64)->nullable()->unique(); // For mobile run-sheet portal access
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('default_vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });

        // Foreign key from vehicles to drivers
        Schema::table('vehicles', function (Blueprint $table) {
            $table->foreign('default_driver_id')->references('id')->on('drivers')->onDelete('set null');
        });

        // 3. Add Driver & Vehicle columns to Bookings Table
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('driver_id')->nullable()->after('tier_name')->index();
            $table->unsignedInteger('vehicle_id')->nullable()->after('driver_id')->index();
            $table->string('pickup_time_slot', 100)->nullable()->after('pickup_location');
            $table->string('pickup_status', 50)->default('scheduled')->after('status')->index(); // scheduled, en_route, arrived, picked_up, completed, no_show
            $table->timestamp('driver_assigned_at')->nullable()->after('pickup_status');
            $table->timestamp('driver_notified_at')->nullable()->after('driver_assigned_at');
            $table->timestamp('guest_notified_at')->nullable()->after('driver_notified_at');
            $table->text('driver_notes')->nullable()->after('special_requests');

            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['driver_id']);
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn([
                'driver_id',
                'vehicle_id',
                'pickup_time_slot',
                'pickup_status',
                'driver_assigned_at',
                'driver_notified_at',
                'guest_notified_at',
                'driver_notes',
            ]);
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['default_driver_id']);
        });

        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
    }
};
