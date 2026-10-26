<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

        try {
            $response = Http::connectTimeout(5)
                ->timeout(12)
                ->withHeaders(['User-Agent' => 'DunesDiscoveryTourism-RateEngine/2.0'])
                ->get($apiUrl);
        } catch (\Throwable $e) {
            $msg = 'Currency sync API request failed: '.$e->getMessage();
            $this->error($msg);
            Log::warning($msg);

            return Command::FAILURE;
        }

        if (! $response->successful()) {
            $msg = "Currency sync API request failed (HTTP {$response->status()}).";
            $this->error($msg);
            Log::warning($msg);

            return Command::FAILURE;
        }

        $data = $response->json();

        if (! isset($data['result']) || $data['result'] !== 'success' || empty($data['rates'])) {
            $msg = 'Currency sync API returned malformed or non-success payload.';
            $this->error($msg);
            Log::warning($msg);

            return Command::FAILURE;
        }

        $rates = $data['rates'];

        $supportedCurrencies = [
            'usd' => isset($rates['USD']) ? round((float) $rates['USD'], 4) : 0.2723,
            'eur' => isset($rates['EUR']) ? round((float) $rates['EUR'], 4) : 0.2510,
            'gbp' => isset($rates['GBP']) ? round((float) $rates['GBP'], 4) : 0.2150,
            'sar' => isset($rates['SAR']) ? round((float) $rates['SAR'], 4) : 1.0210,
            'inr' => isset($rates['INR']) ? round((float) $rates['INR'], 2) : 22.85,
        ];

        try {
            foreach ($supportedCurrencies as $cur => $rate) {
                Setting::updateOrCreate(
                    ['setting_key' => 'currency_rate_'.$cur],
                    ['setting_value' => (string) $rate]
                );
                $this->line("  ✓ {$cur} Rate: {$rate}");
            }

            $now = now()->toDateTimeString();
            Setting::updateOrCreate(
                ['setting_key' => 'currency_rates_last_synced'],
                ['setting_value' => $now]
            );

            Cache::forget('site_settings_cache');
            app(SettingsService::class)->clearCache();

            $this->info("Currency rates synchronized successfully at {$now}.");
            Log::info('Currency rates synchronized successfully via Open Exchange Rates.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Database update failed: '.$e->getMessage());
            Log::error('Currency rate database update failed: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
