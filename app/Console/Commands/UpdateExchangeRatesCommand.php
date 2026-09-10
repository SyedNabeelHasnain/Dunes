<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateExchangeRatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:sync-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize live foreign exchange rates (USD, EUR, GBP, SAR, INR) against base AED.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Connecting to Open Exchange Rates API (Base: AED)...');

        $apiUrl = 'https://open.er-api.com/v6/latest/AED';

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: DunesDiscoveryTourism-RateEngine/2.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || empty($response)) {
            $msg = "Currency sync API request failed (HTTP {$httpCode}): " . ($curlError ?: 'Empty response');
            $this->error($msg);
            Log::warning($msg);
            return Command::FAILURE;
        }

        $data = json_decode($response, true);

        if (!isset($data['result']) || $data['result'] !== 'success' || empty($data['rates'])) {
            $msg = "Currency sync API returned malformed or non-success payload.";
            $this->error($msg);
            Log::warning($msg);
            return Command::FAILURE;
        }

        $rates = $data['rates'];

        $supportedCurrencies = [
            'usd' => isset($rates['USD']) ? round((float)$rates['USD'], 4) : 0.2723,
            'eur' => isset($rates['EUR']) ? round((float)$rates['EUR'], 4) : 0.2510,
            'gbp' => isset($rates['GBP']) ? round((float)$rates['GBP'], 4) : 0.2150,
            'sar' => isset($rates['SAR']) ? round((float)$rates['SAR'], 4) : 1.0210,
            'inr' => isset($rates['INR']) ? round((float)$rates['INR'], 2) : 22.85,
        ];

        try {
            foreach ($supportedCurrencies as $cur => $rate) {
                Setting::updateOrCreate(
                    ['setting_key' => 'currency_rate_' . $cur],
                    ['setting_value' => (string)$rate]
                );
                $this->line("  ✓ {$cur} Rate: {$rate}");
            }

            $now = now()->toDateTimeString();
            Setting::updateOrCreate(
                ['setting_key' => 'currency_rates_last_synced'],
                ['setting_value' => $now]
            );

            Cache::forget('site_settings_cache');

            $this->info("Currency rates synchronized successfully at {$now}.");
            Log::info("Currency rates synchronized successfully via Open Exchange Rates.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Database update failed: " . $e->getMessage());
            Log::error("Currency rate database update failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
