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
        $config[trim($name)] = trim($value, '"\' ');
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

    // 6. Reset PHP OPcache in Web Server memory
    if (function_exists('opcache_reset')) {
        @opcache_reset();
    }

    $idxPath = $baseDir . '/resources/views/index.blade.php';
    $appPath = $baseDir . '/resources/views/layouts/app.blade.php';
    
    $idxInfo = file_exists($idxPath) ? "lines=" . count(file($idxPath)) : "NOT FOUND";
    $appInfo = file_exists($appPath) ? "lines=" . count(file($appPath)) . ", @@context=" . (strpos(file_get_contents($appPath), '@@context') !== false ? 'YES' : 'NO') : "NOT FOUND";

    echo "SUCCESS: cache_version updated to $newVer (affected rows: $affected, cleared views: $clearedCount). index.blade ($idxInfo), app.blade ($appInfo). OPcache reset successfully!";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
