<?php
session_start();
require 'Includes/database.php';

if(isset($_SESSION['username'])){
    $ip = $_SERVER['REMOTE_ADDR'];
    try {
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'USER_LOGOUT', 'User securely terminated session.', ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], $ip]);
    } catch (Exception $e) { }
}

$_SESSION = array();
session_destroy();
header("Location: login.php");
exit;
?>