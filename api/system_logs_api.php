<?php
// api/system_logs_api.php
ob_start(); 
session_start(); 
require '../Includes/database.php'; 
header('Content-Type: application/json');

// Security Check: Only Admins can view audit logs
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') { 
    echo json_encode(['success'=>false, 'message' => 'Unauthorized Access']); 
    exit; 
}

// Fetch the latest 2000 logs to allow deep frontend filtering
$stmt = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 2000");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_clean(); 
echo json_encode(['success' => true, 'data' => $logs]);
?>