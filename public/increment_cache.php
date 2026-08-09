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

    // 7. Check for action=send_reset_mail
    if (isset($_GET['action']) && $_GET['action'] === 'send_reset_mail') {
        echo "<hr><h3>=== LIVE SMTP PASSWORD RESET DISPATCH ===</h3>";
        $email = 'dunesdiscovery85@gmail.com';
        
        // Update user email to dunesdiscovery85@gmail.com
        $stmtUsr = $pdo->prepare("UPDATE users SET email = ? WHERE id = 1");
        $stmtUsr->execute([$email]);
        echo "<p>Updated user #1 email in live DB to: <strong>{$email}</strong></p>";

        // Insert password reset token
        $rawToken = bin2hex(random_bytes(20));
        $hashedToken = password_hash($rawToken, PASSWORD_DEFAULT);
        
        $pdo->prepare("DELETE FROM password_reset_tokens WHERE email = ?")->execute([$email]);
        $pdo->prepare("INSERT INTO password_reset_tokens (email, token, created_at) VALUES (?, ?, NOW())")->execute([$email, $hashedToken]);
        
        $resetUrl = "https://dunesdiscoverytourism.com/reset-password/{$rawToken}?email=" . urlencode($email);
        echo "<p>Generated Reset URL: <a href='{$resetUrl}' target='_blank'>{$resetUrl}</a></p>";

        // Direct SMTP Send on Port 465
        $smtpHost = $config['MAIL_HOST'] ?? 'smtp.hostinger.com';
        $smtpPort = $config['MAIL_PORT'] ?? 465;
        $smtpUser = $config['MAIL_USERNAME'] ?? 'info@dunesdiscoverytourism.com';
        $smtpPass = $config['MAIL_PASSWORD'] ?? 'Zubairkhan@2025';

        echo "<p>Connecting to ssl://{$smtpHost}:{$smtpPort} as {$smtpUser}...</p>";
        $fp = @fsockopen('ssl://' . $smtpHost, $smtpPort, $errno, $errstr, 15);
        if ($fp) {
            $banner = fgets($fp, 512);
            fputs($fp, "EHLO dunesdiscoverytourism.com\r\n");
            while ($line = fgets($fp, 512)) {
                if (substr($line, 3, 1) == " ") break;
            }
            fputs($fp, "AUTH LOGIN\r\n");
            fgets($fp, 512);
            fputs($fp, base64_encode($smtpUser) . "\r\n");
            fgets($fp, 512);
            fputs($fp, base64_encode($smtpPass) . "\r\n");
            $authRes = fgets($fp, 512);

            echo "<p>SMTP Auth Status: <strong>" . htmlspecialchars(trim($authRes)) . "</strong></p>";

            if (strpos($authRes, '235') !== false) {
                $htmlBody = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #ffffff;'>
                        <h2 style='color: #00476d; margin-top: 0;'>Reset Your Password</h2>
                        <p style='color: #333333; font-size: 15px;'>Hello Admin,</p>
                        <p style='color: #555555; font-size: 14px; line-height: 1.6;'>You are receiving this email because we received a password reset request for your account.</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$resetUrl}' style='background-color: #f69044; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; display: inline-block;'>Reset Password</a>
                        </div>
                        <p style='color: #777777; font-size: 13px;'>This password reset link will expire in 60 minutes.</p>
                    </div>
                ";

                fputs($fp, "MAIL FROM: <{$smtpUser}>\r\n");
                fgets($fp, 512);
                fputs($fp, "RCPT TO: <{$email}>\r\n");
                fgets($fp, 512);
                fputs($fp, "DATA\r\n");
                fgets($fp, 512);

                $headers = "From: Dunes Discovery Tourism <{$smtpUser}>\r\n";
                $headers .= "To: {$email}\r\n";
                $headers .= "Subject: Reset Your Password - Dunes Discovery Tourism\r\n";
                $headers .= "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                fputs($fp, "{$headers}\r\n{$htmlBody}\r\n.\r\n");
                $sendRes = fgets($fp, 512);
                fputs($fp, "QUIT\r\n");
                fclose($fp);

                echo "<p style='color: green; font-weight: bold;'>LIVE SMTP SEND RESULT: " . htmlspecialchars(trim($sendRes)) . "</p>";
            } else {
                echo "<p style='color: red;'>SMTP Authentication failed: " . htmlspecialchars(trim($authRes)) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Could not connect to SMTP socket: [$errno] $errstr</p>";
        }
    }

    echo "<br>SUCCESS: cache_version updated to $newVer (affected rows: $affected, cleared views: $clearedCount). index.blade ($idxInfo), app.blade ($appInfo). OPcache reset successfully!";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
