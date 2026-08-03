<?php
// db_update.php
session_start();
require_once 'Includes/database.php';

echo "<div style='font-family: sans-serif; padding: 40px; max-width: 600px; margin: auto;'>";

try {
    // 1. Add the booking_date column safely if it doesn't exist
    $pdo->exec("ALTER TABLE `reservations` ADD COLUMN IF NOT EXISTS `booking_date` DATE DEFAULT NULL AFTER `email`");
    
    // 2. Backfill existing records: Set booking_date to match created_at for older bookings
    $pdo->exec("UPDATE `reservations` SET `booking_date` = DATE(`created_at`) WHERE `booking_date` IS NULL");

    echo "<h2 style='color: #046a38;'>✅ Database Update Successful!</h2>";
    echo "<p style='color: #334155;'>The <strong>booking_date</strong> schema modification has been successfully applied to your local database without affecting your existing records.</p>";
    echo "<a href='dashboard.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #046a38; color: white; text-decoration: none; border-radius: 8px;'>Return to Dashboard</a>";

} catch (PDOException $e) {
    echo "<h2 style='color: #e11d48;'>❌ Database Update Failed</h2>";
    echo "<p style='color: #334155;'>Error Details: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</div>";
?>