<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Tier;
use App\Models\Tour;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverAndFleetLogisticsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Tour $tour;
    protected Tier $tier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email' => 'admin@dunesdiscovery.com',
        ]);

        $this->tour = Tour::create([
            'name' => 'Premium Evening Desert Safari',
            'slug' => 'premium-evening-desert-safari',
            'status' => 'active',
            'price' => 150,
            'hero_image' => 'images/desert-safari-poster.avif',
            'thumb_image' => 'images/desert-safari-poster.avif',
        ]);

        $this->tier = Tier::create([
            'tour_id' => $this->tour->id,
            'name' => 'VIP Experience',
            'slug' => 'vip-experience',
            'display_name' => 'VIP 4x4 Experience',
            'price' => 200,
            'child_price' => 150,
        ]);
    }

    public function test_driver_and_vehicle_models_and_relations(): void
    {
        $vehicle = Vehicle::create([
            'model_name' => 'Toyota Land Cruiser 300 VXR',
            'plate_number' => '14285',
            'plate_code' => 'A',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'color' => 'Pearl White',
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Captain Tariq Mahmood',
            'phone' => '+971 50 111 2233',
            'email' => 'tariq@dunesdiscovery.com',
            'license_number' => 'DXB-SAF-99120',
            'default_vehicle_id' => $vehicle->id,
            'status' => 'active',
            'rating' => 4.95,
        ]);

        $this->assertNotEmpty($driver->access_token);
        $this->assertEquals('Dubai A 14285', $vehicle->short_plate);
        $this->assertEquals($vehicle->id, $driver->defaultVehicle->id);
    }

    public function test_admin_can_manage_drivers_crud(): void
    {
        $this->actingAs($this->adminUser);

        // Create driver
        $response = $this->post(route('admin.drivers.store'), [
            'name' => 'Captain Furqan Ali',
            'phone' => '+971 52 333 4455',
            'email' => 'furqan@dunesdiscovery.com',
            'license_number' => 'DXB-SAF-88231',
            'status' => 'active',
            'rating' => 4.98,
        ]);

        $response->assertRedirect(route('admin.drivers.index'));
        $this->assertDatabaseHas('drivers', ['name' => 'Captain Furqan Ali']);

        $driver = Driver::where('name', 'Captain Furqan Ali')->first();

        // Update driver
        $updateResponse = $this->put(route('admin.drivers.update', $driver->id), [
            'name' => 'Captain Furqan Ali Senior',
            'phone' => '+971 52 333 4455',
            'email' => 'furqan@dunesdiscovery.com',
            'status' => 'active',
            'rating' => 5.0,
        ]);

        $updateResponse->assertRedirect(route('admin.drivers.index'));
        $this->assertDatabaseHas('drivers', ['name' => 'Captain Furqan Ali Senior']);

        // Toggle status
        $toggleResponse = $this->post(route('admin.drivers.toggle-status', $driver->id));
        $toggleResponse->assertRedirect();
        $this->assertEquals('inactive', $driver->fresh()->status);
    }

    public function test_admin_can_manage_vehicles_crud(): void
    {
        $this->actingAs($this->adminUser);

        // Create vehicle
        $response = $this->post(route('admin.vehicles.store'), [
            'model_name' => 'Nissan Patrol 5.6L Platinum',
            'plate_number' => '88910',
            'plate_code' => 'K',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'color' => 'Black Diamond',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('admin.vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['plate_number' => '88910']);

        $vehicle = Vehicle::where('plate_number', '88910')->first();

        // Update vehicle
        $updateResponse = $this->put(route('admin.vehicles.update', $vehicle->id), [
            'model_name' => 'Nissan Patrol 5.6L Platinum V8',
            'plate_number' => '88910',
            'plate_code' => 'K',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'status' => 'active',
        ]);

        $updateResponse->assertRedirect(route('admin.vehicles.index'));
        $this->assertDatabaseHas('vehicles', ['model_name' => 'Nissan Patrol 5.6L Platinum V8']);
    }

    public function test_assign_driver_and_batch_assign_in_operations_hub(): void
    {
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::create([
            'model_name' => 'Toyota Land Cruiser 300 VXR',
            'plate_number' => '12345',
            'plate_code' => 'A',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Captain Tariq Mahmood',
            'phone' => '+971 50 123 4567',
            'license_number' => 'DXB-1234',
            'default_vehicle_id' => $vehicle->id,
            'status' => 'active',
        ]);

        $booking1 = Booking::create([
            'reference' => 'DDT261016A1B2C',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => $this->tour->name,
            'tier_name' => $this->tier->name,
            'name' => 'Alexander Wright',
            'email' => 'alex@example.com',
            'phone' => '+971 50 999 8888',
            'tour_date' => now()->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 400,
            'total' => 400,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'status' => 'confirmed',
            'pickup_location' => 'Atlantis The Palm, Dubai',
        ]);

        $booking2 = Booking::create([
            'reference' => 'DDT261016X8Y9Z',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => $this->tour->name,
            'tier_name' => $this->tier->name,
            'name' => 'Elena Rostova',
            'email' => 'elena@example.com',
            'phone' => '+971 55 444 3322',
            'tour_date' => now()->format('Y-m-d'),
            'adults' => 3,
            'children' => 1,
            'subtotal' => 750,
            'total' => 750,
            'payment_status' => 'unpaid',
            'balance_due' => 750,
            'payment_method' => 'cash',
            'status' => 'confirmed',
            'pickup_location' => 'Marina Gate 1, Dubai Marina',
        ]);

        // Test single assign
        $assignResponse = $this->post(route('admin.operations.assign-driver', $booking1->id), [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'pickup_time_slot' => '3:15 PM - 3:45 PM',
            'pickup_status' => 'en_route',
        ]);

        $assignResponse->assertRedirect();
        $fresh1 = $booking1->fresh();
        $this->assertEquals($driver->id, $fresh1->driver_id);
        $this->assertEquals($vehicle->id, $fresh1->vehicle_id);
        $this->assertEquals('en_route', $fresh1->pickup_status);
        $this->assertEquals('Captain Tariq Mahmood', $fresh1->assigned_driver_name);
        $this->assertStringContainsString('[DISPATCH:', $fresh1->special_requests);

        // Test batch assign
        $batchResponse = $this->post(route('admin.operations.batch-assign'), [
            'booking_ids' => [$booking1->id, $booking2->id],
            'driver_id' => $driver->id,
            'pickup_time_slot' => '3:30 PM - 4:00 PM',
        ]);

        $batchResponse->assertRedirect();
        $this->assertEquals($driver->id, $booking2->fresh()->driver_id);
        $this->assertEquals($vehicle->id, $booking2->fresh()->vehicle_id);

        // Test quick pickup status update
        $statusResponse = $this->post(route('admin.operations.update-status', $booking1->id), [
            'status' => 'picked_up',
        ]);
        $statusResponse->assertRedirect();
        $this->assertEquals('picked_up', $booking1->fresh()->pickup_status);
    }

    public function test_driver_portal_run_sheet_and_mobile_status_update(): void
    {
        $vehicle = Vehicle::create([
            'model_name' => 'Toyota Land Cruiser 300 VXR',
            'plate_number' => '12345',
            'plate_code' => 'A',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Captain Tariq Mahmood',
            'phone' => '+971 50 123 4567',
            'license_number' => 'DXB-1234',
            'default_vehicle_id' => $vehicle->id,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'reference' => 'DDT261016M4N5O',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => $this->tour->name,
            'tier_name' => $this->tier->name,
            'name' => 'Marcus Schmidt',
            'email' => 'marcus@example.com',
            'phone' => '+971 50 777 6655',
            'tour_date' => now()->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 400,
            'total' => 400,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'status' => 'confirmed',
            'pickup_location' => 'Burj Al Arab, Jumeirah',
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'pickup_status' => 'scheduled',
        ]);

        // Access mobile run sheet via token
        $portalResponse = $this->get(route('driver.run-sheet', $driver->access_token));
        $portalResponse->assertStatus(200)
            ->assertSee('Captain Tariq Mahmood')
            ->assertSee('#DDT261016M4N5O')
            ->assertSee('Burj Al Arab');

        // Update status from mobile portal
        $updateResponse = $this->post(route('driver.update-status', [
            'token' => $driver->access_token,
            'bookingId' => $booking->id,
        ]), [
            'pickup_status' => 'arrived',
            'driver_notes' => 'Waiting in lobby for guest',
        ]);

        $updateResponse->assertRedirect();
        $this->assertEquals('arrived', $booking->fresh()->pickup_status);
        $this->assertEquals('Waiting in lobby for guest', $booking->fresh()->driver_notes);
    }

    public function test_driver_reports_and_csv_export(): void
    {
        $this->actingAs($this->adminUser);

        $vehicle = Vehicle::create([
            'model_name' => 'Toyota Land Cruiser 300 VXR',
            'plate_number' => '12345',
            'plate_code' => 'A',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Captain Tariq Mahmood',
            'phone' => '+971 50 123 4567',
            'license_number' => 'DXB-1234',
            'default_vehicle_id' => $vehicle->id,
            'status' => 'active',
        ]);

        Booking::create([
            'reference' => 'DDT261016REPORT1',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => $this->tour->name,
            'tier_name' => $this->tier->name,
            'name' => 'Guest Cash',
            'email' => 'cash@example.com',
            'phone' => '+971 50 111 2222',
            'tour_date' => now()->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 400,
            'total' => 400,
            'payment_status' => 'unpaid',
            'balance_due' => 400,
            'payment_method' => 'cash',
            'status' => 'confirmed',
            'driver_id' => $driver->id,
        ]);

        $reportResponse = $this->get(route('admin.operations.reports'));
        $reportResponse->assertStatus(200)
            ->assertSee('Driver Performance & Cash Reconciliation')
            ->assertSee('Captain Tariq Mahmood');

        $csvResponse = $this->get(route('admin.operations.reports.export'));
        $csvResponse->assertStatus(200);
        $this->assertEquals('text/csv; charset=UTF-8', $csvResponse->headers->get('content-type'));
    }

    public function test_digital_voucher_renders_assigned_driver_details(): void
    {
        $vehicle = Vehicle::create([
            'model_name' => 'Toyota Land Cruiser 300 VXR',
            'plate_number' => '12345',
            'plate_code' => 'A',
            'plate_city' => 'Dubai',
            'passenger_capacity' => 6,
            'status' => 'active',
        ]);

        $driver = Driver::create([
            'name' => 'Captain Tariq Mahmood',
            'phone' => '+971 50 123 4567',
            'license_number' => 'DXB-1234',
            'default_vehicle_id' => $vehicle->id,
            'status' => 'active',
            'rating' => 4.95,
        ]);

        $booking = Booking::create([
            'reference' => 'DDT261016VOUCHER',
            'tour_id' => $this->tour->id,
            'tier_id' => $this->tier->id,
            'tour_name' => $this->tour->name,
            'tier_name' => $this->tier->name,
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'phone' => '+971 50 999 0000',
            'tour_date' => now()->addDay()->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'subtotal' => 400,
            'total' => 400,
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'status' => 'confirmed',
            'pickup_location' => 'Fairmont The Palm',
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ]);

        $voucherResponse = $this->get(route('booking.voucher', $booking->reference));
        $voucherResponse->assertStatus(200)
            ->assertSee('Captain Tariq Mahmood')
            ->assertSee('Toyota Land Cruiser 300')
            ->assertSee('Dubai A 12345');
    }
}
