<?php
// Function to rotate status every Monday at 16:00 UTC: 'classement' -> 'archive_top', 'valide' -> 'classement'
// Called from classement.php on page load

require_once __DIR__ . '/../class/Database.php';

function promoteToTopIfNeeded() {
    $pdo = Database::getConnection();

    // Create system_settings table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS system_settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value VARCHAR(255),
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    // Check if it's Monday and after 15:30 UTC
    $now = new DateTime('now', new DateTimeZone('UTC'));
    $dayOfWeek = $now->format('N'); // 1 = Monday
    $hour = $now->format('H');
    $minute = $now->format('i');

    if ($dayOfWeek != 1 || ($hour < 15 || ($hour == 15 && $minute < 30))) {
        return false; // Not time yet
    }

    // Check last promotion date
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'last_promotion_date'");
    $stmt->execute();
    $lastPromotion = $stmt->fetchColumn();

    $today = $now->format('Y-m-d');
    if ($lastPromotion === $today) {
        return false; // Already done today
    }

    // Rotate statuses for all tables
    $tables = ['musique', 'artiste', 'groupe'];
    foreach ($tables as $table) {
        $statusCol = 'Status' . $table;

        // First, move 'classement' items to 'archive_top'
        $stmt = $pdo->prepare("UPDATE {$table} SET {$statusCol} = 'archive_top' WHERE {$statusCol} = 'classement'");
        $stmt->execute();

        // Then, move 'valide' items to 'classement'
        $stmt = $pdo->prepare("UPDATE {$table} SET {$statusCol} = 'classement' WHERE {$statusCol} = 'valide'");
        $stmt->execute();
    }

    // Update last promotion date
    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('last_promotion_date', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$today, $today]);

    return true;
}

// If called directly, run the function
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    if (promoteToTopIfNeeded()) {
        echo "Status rotation completed successfully: 'classement' -> 'archive_top', 'valide' -> 'classement'.\n";
    } else {
        echo "Promotion not needed or already done today.\n";
    }
}
?>
