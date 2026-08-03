<?php
// db_update.php
session_start();
require_once 'Includes/database.php';

echo "<div style='font-family: sans-serif; padding: 40px; max-width: 600px; margin: auto; background-color: #f8fafc; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;'>";

try {
    echo "<h2 style='color: #0f172a; margin-top: 0;'><i style='color: #3b82f6;'>🔄</i> Applying System Upgrades...</h2>";
    
    $pdo->exec("ALTER TABLE `reservations` ADD COLUMN IF NOT EXISTS `booking_date` DATE DEFAULT NULL AFTER `email`");
    $pdo->exec("UPDATE `reservations` SET `booking_date` = DATE(`created_at`) WHERE `booking_date` IS NULL");
    
    // NEW: Inject the Historical Flag for Auto-Maintenance Bypass
    $pdo->exec("ALTER TABLE `reservations` ADD COLUMN IF NOT EXISTS `is_historical` TINYINT(1) DEFAULT 0 AFTER `is_followed_up`");
    echo "<p style='color: #334155; margin-bottom: 5px;'>✔️ `is_historical` schema and cancellation bypass configured.</p>";

    $stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`id`, `display_name`, `username`, `password`, `role`) VALUES (2, 'System Administrator', 'Admin', '$2y$10$4OqZS/FWDo7InBAoBrEWZuAqSogIPNx2rKv.oHnfHJkk5p9tL4NdS', 'admin')");
    $stmt->execute();

    // Default to '0' (Locked for everyone)
    $pdo->exec("INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES ('allow_retroactive_bookings', '0')");

    echo "<div style='background-color: #ecfdf5; padding: 15px; border-radius: 8px; border: 1px solid #a7f3d0;'>";
    echo "<h3 style='color: #046a38; margin-top: 0;'>✅ Database Update Successful!</h3>";
    echo "<p style='color: #065f46; font-size: 14px;'>The system upgrades have been applied to your local environment. Your existing ledger records were not affected.</p>";
    echo "</div>";
    
    echo "<a href='dashboard.php' style='display: inline-block; margin-top: 25px; padding: 12px 24px; background: #0f172a; color: white; font-weight: bold; text-decoration: none; border-radius: 8px; transition: background 0.3s;'>Return to Dashboard</a>";

} catch (PDOException $e) {
    echo "<div style='background-color: #fff1f2; padding: 15px; border-radius: 8px; border: 1px solid #fecdd3;'>";
    echo "<h3 style='color: #e11d48; margin-top: 0;'>❌ Database Update Failed</h3>";
    echo "<p style='color: #be123c; font-family: monospace; font-size: 12px;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
echo "</div>";
?>