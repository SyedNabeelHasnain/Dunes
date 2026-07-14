<?php
if (!isset($_GET['key']) || $_GET['key'] !== 'dunes2026') {
    die('Unauthorized');
}

// 1. Parse .env file
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    die("ERROR: .env file not found");
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
    $configCache = __DIR__ . '/../bootstrap/cache/config.php';
    if (file_exists($configCache)) {
        @unlink($configCache);
    }
    $routesCache = __DIR__ . '/../bootstrap/cache/routes-v7.php';
    if (file_exists($routesCache)) {
        @unlink($routesCache);
    }
    $servicesCache = __DIR__ . '/../bootstrap/cache/services.php';
    if (file_exists($servicesCache)) {
        @unlink($servicesCache);
    }
    $packagesCache = __DIR__ . '/../bootstrap/cache/packages.php';
    if (file_exists($packagesCache)) {
        @unlink($packagesCache);
    }

    // 5. Manually clear compiled views
    $viewsDir = __DIR__ . '/../storage/framework/views';
    if (is_dir($viewsDir)) {
        $files = glob($viewsDir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && basename($file) !== '.gitignore') {
                @unlink($file);
            }
        }
    }

    echo "SUCCESS: cache_version updated to $newVer (affected rows: $affected). Server-side configuration, route caches, and compiled views cleared successfully!";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
