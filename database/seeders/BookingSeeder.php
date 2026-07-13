<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\VerifiedEmail;
use App\Models\EmailOtp;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M');
        // 1. Seed Request Logs (Batch Insert)
        $logsPath = database_path('seeders/data/request_logs.json');
        if (File::exists($logsPath)) {
            $logs = json_decode(File::get($logsPath), true);
            $chunks = array_chunk($logs, 500);
            foreach ($chunks as $chunk) {
                // Ensure datetime fields are formatted correctly
                foreach ($chunk as &$log) {
                    $log['request_timestamp'] = $log['request_timestamp'] ? date('Y-m-d H:i:s', strtotime($log['request_timestamp'])) : now();
                    $log['session_start_time'] = $log['session_start_time'] ? date('Y-m-d H:i:s', strtotime($log['session_start_time'])) : null;
                    $log['session_end_time'] = $log['session_end_time'] ? date('Y-m-d H:i:s', strtotime($log['session_end_time'])) : null;
                    $log['form_load_timestamp'] = $log['form_load_timestamp'] ? date('Y-m-d H:i:s', strtotime($log['form_load_timestamp'])) : null;
                    $log['form_submit_timestamp'] = $log['form_submit_timestamp'] ? date('Y-m-d H:i:s', strtotime($log['form_submit_timestamp'])) : null;
                    $log['created_at'] = $log['created_at'] ? date('Y-m-d H:i:s', strtotime($log['created_at'])) : now();
                }
                DB::table('request_logs')->insert($chunk);
            }
        }

        // 2. Seed Bookings
        $bookingsPath = database_path('seeders/data/bookings.json');
        if (File::exists($bookingsPath)) {
            $bookings = json_decode(File::get($bookingsPath), true);
            foreach ($bookings as $b) {
                Booking::updateOrCreate(
                    ['id' => $b['id']],
                    [
                        'reference' => $b['reference'],
                        'tour_id' => $b['tour_id'] ? (int)$b['tour_id'] : null,
                        'tier_id' => $b['tier_id'] ? (int)$b['tier_id'] : null,
                        'tour_name' => $b['tour_name'],
                        'tier_name' => $b['tier_name'],
                        'tour_date' => $b['tour_date'] ? date('Y-m-d', strtotime($b['tour_date'])) : null,
                        'adults' => (int)($b['adults'] ?? 1),
                        'children' => (int)($b['children'] ?? 0),
                        'name' => $b['name'],
                        'email' => $b['email'],
                        'phone' => $b['phone'],
                        'pickup_location' => $b['pickup_location'],
                        'special_requests' => $b['special_requests'],
                        'subtotal' => (float)$b['subtotal'],
                        'addons_total' => (float)($b['addons_total'] ?? 0.00),
                        'total' => (float)$b['total'],
                        'currency' => $b['currency'] ?: 'AED',
                        'status' => $b['status'] ?: 'pending',
                        'payment_method' => $b['payment_method'] ?: 'cash',
                        'payment_status' => $b['payment_status'] ?: 'unpaid',
                        'payment_amount' => (float)($b['payment_amount'] ?? 0.00),
                        'balance_due' => (float)($b['balance_due'] ?? 0.00),
                        'ziina_payment_intent_id' => $b['ziina_payment_intent_id'],
                        'ziina_status' => $b['ziina_status'],
                        'ziina_redirect_url' => $b['ziina_redirect_url'],
                        'request_log_id' => $b['request_log_id'] ? (int)$b['request_log_id'] : null,
                        'ip_address' => $b['ip_address'],
                        'ip_location' => $b['ip_location'],
                        'gps_lat' => $b['gps_lat'],
                        'gps_lng' => $b['gps_lng'],
                        'gps_address' => $b['gps_address'],
                        'device_type' => $b['device_type'],
                        'browser' => $b['browser'],
                        'platform' => $b['platform'],
                        'user_agent' => $b['user_agent'],
                        'referrer' => $b['referrer'],
                        'utm_source' => $b['utm_source'],
                        'utm_medium' => $b['utm_medium'],
                        'utm_campaign' => $b['utm_campaign'],
                        'utm_term' => $b['utm_term'],
                        'utm_content' => $b['utm_content'],
                        'is_verified' => (bool)($b['is_verified'] ?? false),
                    ]
                );
            }
        }

        // 3. Seed Booking Payments
        $paymentsPath = database_path('seeders/data/booking_payments.json');
        if (File::exists($paymentsPath)) {
            $payments = json_decode(File::get($paymentsPath), true);
            foreach ($payments as $p) {
                BookingPayment::updateOrCreate(
                    ['id' => $p['id']],
                    [
                        'booking_id' => $p['booking_id'] ? (int)$p['booking_id'] : null,
                        'payment_intent_id' => $p['payment_intent_id'],
                        'amount' => (float)$p['amount'],
                        'currency' => $p['currency'] ?: 'AED',
                        'status' => $p['status'],
                        'payment_url' => $p['payment_url'],
                        'notes' => $p['notes'],
                        'customer_name' => $p['customer_name'],
                        'customer_email' => $p['customer_email'],
                        'customer_phone' => $p['customer_phone'],
                        'description' => $p['description'],
                    ]
                );
            }
        }

        // 4. Seed WhatsApp Inquiries
        $whatsappPath = database_path('seeders/data/whatsapp_inquiries.json');
        if (File::exists($whatsappPath)) {
            $inquiries = json_decode(File::get($whatsappPath), true);
            $chunks = array_chunk($inquiries, 500);
            foreach ($chunks as $chunk) {
                foreach ($chunk as &$inq) {
                    $inq['request_log_id'] = $inq['request_log_id'] ? (int)$inq['request_log_id'] : null;
                    $inq['created_at'] = $inq['created_at'] ? date('Y-m-d H:i:s', strtotime($inq['created_at'])) : now();
                }
                DB::table('whatsapp_inquiries')->insert($chunk);
            }
        }

        // 5. Seed Verified Emails
        $verifiedPath = database_path('seeders/data/verified_emails.json');
        if (File::exists($verifiedPath)) {
            $emails = json_decode(File::get($verifiedPath), true);
            foreach ($emails as $email) {
                VerifiedEmail::updateOrCreate(
                    ['email' => $email['email']],
                    ['verified_at' => $email['verified_at'] ? date('Y-m-d H:i:s', strtotime($email['verified_at'])) : now()]
                );
            }
        }

        // 6. Seed Email OTPs
        $otpsPath = database_path('seeders/data/email_otps.json');
        if (File::exists($otpsPath)) {
            $otps = json_decode(File::get($otpsPath), true);
            foreach ($otps as $otp) {
                EmailOtp::updateOrCreate(
                    ['id' => $otp['id']],
                    [
                        'email' => $otp['email'],
                        'otp' => $otp['otp'],
                        'expires_at' => date('Y-m-d H:i:s', strtotime($otp['expires_at'])),
                        'created_at' => date('Y-m-d H:i:s', strtotime($otp['created_at'])),
                    ]
                );
            }
        }
    }
}
