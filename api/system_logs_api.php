<?php
ob_start(); session_start(); require '../Includes/database.php'; header('Content-Type: application/json');
if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') { echo json_encode(['success'=>false]); exit; }

$stmt = $pdo->query("SELECT * FROM system_logs ORDER BY created_at DESC LIMIT 500");
ob_clean(); echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
?>