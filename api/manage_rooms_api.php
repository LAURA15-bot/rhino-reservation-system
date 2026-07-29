<?php
// api/manage_rooms_api.php
ob_start(); 
session_start(); 
require '../Includes/database.php'; 
header('Content-Type: application/json');

// Security Check: Case-insensitive check for admin role
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') { 
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $inv = (int)$_POST['total_inventory'];
    $max = (int)$_POST['max_guests'];
    $ip = $_SERVER['REMOTE_ADDR'];
    
    try {
        // Fetch the room name for precise logging
        $nameStmt = $pdo->prepare("SELECT type FROM rooms WHERE id = ?");
        $nameStmt->execute([$id]);
        $roomName = $nameStmt->fetchColumn();

        // Update the room inventory and capacity
        $pdo->prepare("UPDATE rooms SET total_inventory = ?, max_guests_per_room = ? WHERE id = ?")->execute([$inv, $max, $id]);
        
        // Generate Corporate Security Audit Log
        $logAction = "Modified physical property configuration for $roomName [New Inventory: $inv | Max Pax: $max].";
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'PROPERTY_CONFIG_UPDATE', ?, ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);
        
        ob_clean(); 
        echo json_encode(['success' => true]); 
    } catch (Exception $e) {
        ob_clean(); 
        echo json_encode(['success' => false, 'message' => 'Database failure.']); 
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
    ob_clean(); 
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
?>