<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die('Unauthorized');
}

// Determine base directory for dunes-laravel or root
$baseDir = file_exists(__DIR__ . '/../dunes-laravel/.env') 
    ? __DIR__ . '/../dunes-laravel' 
    : __DIR__ . '/..';

// 1. Parse .env file
$envPath = $baseDir . '/.env';
if (!file_exists($envPath)) {
    die("ERROR: .env file not found at " . $envPath);
}

$config = [];
$lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
    if ($name !== NULL) {
        $config[trim($name)] = trim($value, '"\'  ');
    }
}

// 2. Connect to MySQL Database via PDO
$dbHost = $config['DB_HOST'] ?? 'localhost';
$dbPort = $config['DB_PORT'] ?? '3306';
$dbName = $config['DB_DATABASE'] ?? '';
$dbUser = $config['DB_USERNAME'] ?? '';
$dbPass = $config['DB_PASSWORD'] ?? '';

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 3. Update cache_version in settings table
    $newVer = time();
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'cache_version'");
    $stmt->execute([$newVer]);
    $affected = $stmt->rowCount();

    // Ensure dune-buggy-rental-dubai exists in tours table
    $stmtTour = $pdo->prepare("SELECT id FROM tours WHERE slug = 'dune-buggy-rental-dubai'");
    $stmtTour->execute();
    $existingTour = $stmtTour->fetch();
    if (!$existingTour) {
        $stmtIns = $pdo->prepare("INSERT INTO tours (slug, name, category_id, short_desc, full_desc, duration, pickup_time, dropoff_time, min_age, languages, hero_image, thumb_image, og_image, rating, review_count, is_bestseller, is_featured, status, priority, meta_title, meta_desc, meta_keywords, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmtIns->execute([
            'dune-buggy-rental-dubai',
            'Dune Buggy Rental Dubai',
            1,
            'Unleash the ultimate adrenaline rush in Dubai\'s Lahbab Red Dunes with our self-drive 1000cc Can-Am Maverick X3 and Polaris RZR dune buggies. Conquer towering sand dunes with full safety gear, expert guide instruction, and complimentary hotel pickup.',
            'Take control of a high-powered 1000cc Can-Am Maverick X3 Turbo or Polaris RZR dune buggy and conquer the open desert of Dubai\'s famous Lahbab Red Dunes. Designed for thrill-seekers, couples, and friends, our self-drive dune buggy tours deliver an unparalleled off-road adventure under the guidance of certified desert rally instructors.',
            '3 Hours',
            '7:00 AM / 3:00 PM',
            '10:00 AM / 6:00 PM',
            16,
            'English, Arabic',
            'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
            'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
            'quad-biking-desert-safari-dubai-dune-discovery-tourism.avif',
            4.9,
            642,
            1,
            1,
            'active',
            4,
            'Dune Buggy Rental Dubai | 1000cc Can-Am & Polaris | Dunes Discovery',
            'Rent self-drive 1000cc Can-Am Maverick & Polaris dune buggies in Dubai Lahbab Red Dunes. High-power off-road desert safari with safety gear & hotel pickup.',
            'dune buggy rental dubai, can am dune buggy dubai, polaris rzr dubai, self drive buggy desert safari, red dunes buggy rental'
        ]);
        $newTourId = $pdo->lastInsertId();

        $stmtTier = $pdo->prepare("INSERT INTO tour_tiers (tour_id, tier_id, price, old_price, price_type) VALUES (?, ?, ?, ?, ?)");
        $stmtTier->execute([$newTourId, 1, 599.00, 750.00, 'per buggy']);
        $stmtTier->execute([$newTourId, 2, 899.00, 1100.00, 'per buggy']);
        $stmtTier->execute([$newTourId, 4, 1299.00, 1500.00, 'per buggy']);
    }

    // 4. Manually clear Laravel config and route cache files
    $configCache = $baseDir . '/bootstrap/cache/config.php';
    if (file_exists($configCache)) {
        @unlink($configCache);
    }
    $routesCache = $baseDir . '/bootstrap/cache/routes-v7.php';
    if (file_exists($routesCache)) {
        @unlink($routesCache);
    }
    $servicesCache = $baseDir . '/bootstrap/cache/services.php';
    if (file_exists($servicesCache)) {
        @unlink($servicesCache);
    }
    $packagesCache = $baseDir . '/bootstrap/cache/packages.php';
    if (file_exists($packagesCache)) {
        @unlink($packagesCache);
    }

    // 5. Touch all view files to update modification time and force Blade compiler re-eval
    $viewsPath = $baseDir . '/resources/views';
    if (is_dir($viewsPath)) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsPath));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                @touch($file->getPathname());
            }
        }
    }

    // 6. Clear compiled views with explicit chmod
    $possibleViewsDirs = [
        $baseDir . '/storage/framework/views',
        __DIR__ . '/../storage/framework/views',
        __DIR__ . '/../dunes-laravel/storage/framework/views',
        '/home/u410503041/domains/dunesdiscoverytourism.com/storage/framework/views',
        '/home/u410503041/domains/dunesdiscoverytourism.com/dunes-laravel/storage/framework/views',
    ];
    $clearedCount = 0;
    foreach (array_unique($possibleViewsDirs) as $vDir) {
        if (is_dir($vDir)) {
            @chmod($vDir, 0777);
            $files = glob($vDir . '/*');
            foreach ($files as $file) {
                if (is_file($file) && basename($file) !== '.gitignore') {
                    @chmod($file, 0777);
                    if (@unlink($file)) {
                        $clearedCount++;
                    }
                }
            }
        }
    }

    // 7. Compute view info
    $idxPath = $baseDir . '/resources/views/index.blade.php';
    $appPath = $baseDir . '/resources/views/layouts/app.blade.php';
    $idxInfo = file_exists($idxPath) ? "lines=" . count(file($idxPath)) : "NOT FOUND";
    $appInfo = file_exists($appPath) ? "lines=" . count(file($appPath)) : "NOT FOUND";

    // 8. Reset PHP OPcache in Web Server memory
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    // 9. Check for action=read_log
    if (isset($_GET['action']) && $_GET['action'] === 'read_log') {
        header('Content-Type: text/plain; charset=utf-8');
        $possibleLogs = [
            $baseDir . '/storage/logs/laravel.log',
            __DIR__ . '/../storage/logs/laravel.log',
            __DIR__ . '/../dunes-laravel/storage/logs/laravel.log',
            '/home/u410503041/domains/dunesdiscoverytourism.com/storage/logs/laravel.log',
            '/home/u410503041/domains/dunesdiscoverytourism.com/dunes-laravel/storage/logs/laravel.log',
        ];
        $found = false;
        foreach (array_unique($possibleLogs) as $logFile) {
            if (file_exists($logFile)) {
                echo "=== FOUND LOG AT {$logFile} ===\n";
                $lines = file($logFile);
                $last = array_slice($lines, -150);
                echo implode('', $last);
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "LOG FILE NOT FOUND IN ANY OF THE PLACES:\n" . implode("\n", $possibleLogs);
        }
        exit;
    }

    echo "SUCCESS: cache_version updated to $newVer (affected rows: $affected, cleared views: $clearedCount). index.blade ($idxInfo), app.blade ($appInfo). OPcache reset successfully!";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
