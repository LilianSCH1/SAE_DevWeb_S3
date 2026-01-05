<?php
// Script to rotate status every Monday: 'classement' -> 'archive_top', 'valide' -> 'classement'
// Run every Monday at 6:00 AM UTC via cron: 0 6 * * 1 /usr/bin/php /path/to/promote_to_top.php

require_once __DIR__ . '/../class/Database.php';

$pdo = Database::getConnection();

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

echo "Status rotation completed successfully: 'classement' -> 'archive_top', 'valide' -> 'classement'.\n";
?>
