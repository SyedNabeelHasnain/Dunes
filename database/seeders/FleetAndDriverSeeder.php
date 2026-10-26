<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FleetAndDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehiclesData = [
            [
                'model_name' => 'Toyota Land Cruiser 300 VXR',
                'plate_number' => '48921',
                'plate_code' => 'J',
                'plate_city' => 'Dubai',
                'year' => 2024,
                'passenger_capacity' => 6,
                'color' => 'Pearl White',
                'status' => 'active',
                'insurance_expiry_date' => now()->addMonths(8)->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addMonths(8)->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2024-001',
                'features' => ['4x4 Desert Spec', 'Roll Cage Certified', 'Dual Air Conditioning', 'Garmin GPS Satellite Navigation', 'First Aid Trauma Kit', 'High-Output Tyre Deflators'],
                'notes' => 'Flagship VIP safari vehicle, immaculate condition.',
            ],
            [
                'model_name' => 'Toyota Land Cruiser 300 GXR',
                'plate_number' => '59203',
                'plate_code' => 'K',
                'plate_city' => 'Dubai',
                'year' => 2024,
                'passenger_capacity' => 6,
                'color' => 'Silver Metallic',
                'status' => 'active',
                'insurance_expiry_date' => now()->addMonths(10)->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addMonths(10)->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2024-002',
                'features' => ['4x4 Desert Spec', 'Roll Cage Certified', 'Heavy-Duty Suspension', 'Dual A/C', 'First Aid Kit'],
                'notes' => 'Dedicated evening red dune bashing vehicle.',
            ],
            [
                'model_name' => 'Nissan Patrol 5.6L Platinum V8',
                'plate_number' => '71204',
                'plate_code' => 'M',
                'plate_city' => 'Dubai',
                'year' => 2025,
                'passenger_capacity' => 6,
                'color' => 'White Pearl',
                'status' => 'active',
                'insurance_expiry_date' => now()->addYear()->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addYear()->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2025-003',
                'features' => ['Hydraulic Body Motion Control (HBMC)', 'Roll Cage', 'Bose Premium Audio', 'Leather Seating', 'First Aid Kit'],
                'notes' => 'Premium VIP private safari vehicle.',
            ],
            [
                'model_name' => 'Toyota Land Cruiser 300 VXR',
                'plate_number' => '83419',
                'plate_code' => 'N',
                'plate_city' => 'Dubai',
                'year' => 2024,
                'passenger_capacity' => 6,
                'color' => 'Pearl White',
                'status' => 'active',
                'insurance_expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2024-004',
                'features' => ['4x4 Desert Spec', 'Roll Cage Certified', 'Sand Recovery Trax', 'Dual A/C', 'First Aid Kit'],
                'notes' => 'Equipped for extreme high-dune Lahbab terrain.',
            ],
            [
                'model_name' => 'Toyota Land Cruiser 300 GXR',
                'plate_number' => '92831',
                'plate_code' => 'P',
                'plate_city' => 'Dubai',
                'year' => 2023,
                'passenger_capacity' => 6,
                'color' => 'Black Obsidian',
                'status' => 'active',
                'insurance_expiry_date' => now()->addMonths(7)->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addMonths(7)->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2023-005',
                'features' => ['4x4 Desert Spec', 'Roll Cage Certified', 'First Aid Kit', 'Dual A/C'],
                'notes' => 'Afternoon and morning safari rotations.',
            ],
            [
                'model_name' => 'Toyota HiAce VIP Grandia Luxury Van',
                'plate_number' => '10482',
                'plate_code' => 'R',
                'plate_city' => 'Dubai',
                'year' => 2024,
                'passenger_capacity' => 12,
                'color' => 'Diamond Silver',
                'status' => 'active',
                'insurance_expiry_date' => now()->addMonths(9)->format('Y-m-d'),
                'mulkiya_expiry_date' => now()->addMonths(9)->format('Y-m-d'),
                'dtcm_permit_number' => 'DTCM-SAF-2024-006',
                'features' => ['VIP Reclining Captain Seats', 'Individual High-Output A/C', 'Wi-Fi Hotspot', 'Luggage Capacity', 'First Aid Kit'],
                'notes' => 'Airport, city transfer & cruise direct transfers to desert camp.',
            ],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $v) {
            $vehicles[$v['plate_number']] = Vehicle::updateOrCreate(
                ['plate_number' => $v['plate_number']],
                $v
            );
        }

        $driversData = [
            [
                'name' => 'Captain Tariq Al-Baloushi',
                'license_number' => 'RTA-SAF-10492',
                'phone' => '+971 50 245 6056',
                'email' => 'tariq@dunesdiscoverytourism.com',
                'photo' => 'images/safari-captain-tariq.avif',
                'languages' => ['English', 'Arabic', 'Urdu'],
                'status' => 'active',
                'default_vehicle_plate' => '48921',
                'emergency_contact_name' => 'Fatima Al-Baloushi',
                'emergency_contact_phone' => '+971 50 998 7766',
                'license_expiry_date' => now()->addMonths(14)->format('Y-m-d'),
                'visa_expiry_date' => now()->addMonths(20)->format('Y-m-d'),
                'rating' => 4.98,
                'total_trips' => 342,
                'notes' => 'Lead Desert Operations Captain. 14 years experience across Lahbab and Empty Quarter.',
            ],
            [
                'name' => 'Captain Malik Zubair',
                'license_number' => 'RTA-SAF-10518',
                'phone' => '+971 50 245 6057',
                'email' => 'malik@dunesdiscoverytourism.com',
                'photo' => 'images/safari-captain-malik.avif',
                'languages' => ['English', 'Urdu', 'Hindi'],
                'status' => 'active',
                'default_vehicle_plate' => '59203',
                'emergency_contact_name' => 'Zubair Senior',
                'emergency_contact_phone' => '+971 50 445 1122',
                'license_expiry_date' => now()->addMonths(11)->format('Y-m-d'),
                'visa_expiry_date' => now()->addMonths(18)->format('Y-m-d'),
                'rating' => 4.96,
                'total_trips' => 285,
                'notes' => 'Specialist in extreme high dune bashing and photography angle positioning.',
            ],
            [
                'name' => 'Captain Rehman Khan',
                'license_number' => 'RTA-SAF-10640',
                'phone' => '+971 50 245 6058',
                'email' => 'rehman@dunesdiscoverytourism.com',
                'photo' => 'images/safari-captain-rehman.avif',
                'languages' => ['English', 'Arabic', 'Pashto', 'Urdu'],
                'status' => 'active',
                'default_vehicle_plate' => '71204',
                'emergency_contact_name' => 'Kamran Khan',
                'emergency_contact_phone' => '+971 55 667 8899',
                'license_expiry_date' => now()->addMonths(16)->format('Y-m-d'),
                'visa_expiry_date' => now()->addMonths(22)->format('Y-m-d'),
                'rating' => 4.97,
                'total_trips' => 210,
                'notes' => 'Customer service excellence award winner. Expert in VIP private family tours.',
            ],
            [
                'name' => 'Captain Ali Raza',
                'license_number' => 'RTA-SAF-10702',
                'phone' => '+971 50 245 6059',
                'email' => 'ali@dunesdiscoverytourism.com',
                'photo' => 'images/safari-captain-ali.avif',
                'languages' => ['English', 'Urdu', 'Russian'],
                'status' => 'active',
                'default_vehicle_plate' => '83419',
                'emergency_contact_name' => 'Imran Raza',
                'emergency_contact_phone' => '+971 52 334 5566',
                'license_expiry_date' => now()->addMonths(9)->format('Y-m-d'),
                'visa_expiry_date' => now()->addMonths(15)->format('Y-m-d'),
                'rating' => 4.94,
                'total_trips' => 195,
                'notes' => 'Fluent in Russian conversation. Great with kids and elder passengers.',
            ],
            [
                'name' => 'Captain Furqan Ahmed',
                'license_number' => 'RTA-SAF-10822',
                'phone' => '+971 50 245 6060',
                'email' => 'furqan@dunesdiscoverytourism.com',
                'photo' => 'images/safari-captain-furqan.avif',
                'languages' => ['English', 'Arabic', 'Hindi', 'Urdu'],
                'status' => 'active',
                'default_vehicle_plate' => '92831',
                'emergency_contact_name' => 'Rashid Ahmed',
                'emergency_contact_phone' => '+971 50 112 2334',
                'license_expiry_date' => now()->addMonths(12)->format('Y-m-d'),
                'visa_expiry_date' => now()->addMonths(19)->format('Y-m-d'),
                'rating' => 4.95,
                'total_trips' => 160,
                'notes' => 'Certified Desert First Responder and sandboarding coach.',
            ],
        ];

        foreach ($driversData as $d) {
            $defaultPlate = $d['default_vehicle_plate'] ?? null;
            unset($d['default_vehicle_plate']);

            $existingDriver = Driver::where('phone', $d['phone'])->first();
            $token = ($existingDriver && ! empty($existingDriver->access_token))
                ? $existingDriver->access_token
                : Str::random(32);

            $driver = Driver::updateOrCreate(
                ['phone' => $d['phone']],
                array_merge($d, [
                    'access_token' => $token,
                ])
            );

            if ($defaultPlate && isset($vehicles[$defaultPlate])) {
                $veh = $vehicles[$defaultPlate];
                $driver->default_vehicle_id = $veh->id;
                $driver->save();

                $veh->default_driver_id = $driver->id;
                $veh->save();
            }
        }
    }
}
