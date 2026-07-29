<?php
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
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Add New User
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $display_name = trim($_POST['display_name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = strtolower($_POST['role']);
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (display_name, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$display_name, $username, $hashed, $role]);
            
            $logAction = "Registered new workspace identity: $username with $role privileges.";
            $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'USER_CREATED', ?, ?)")
                ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);
                
            ob_clean();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Username already exists in the system.']);
        }
        exit;
    }

    // Edit Existing User
    if (isset($_POST['action']) && $_POST['action'] === 'edit_user') {
        $id = (int)$_POST['user_id'];
        $display_name = trim($_POST['display_name']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = strtolower($_POST['role']);

        // Hard-lock the core admin (ID 2) from ever losing admin privileges
        if ($id === 2) {
            $role = 'admin'; 
        }

        try {
            // Only hash and update the password if the user actually typed a new one
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET display_name = ?, username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$display_name, $username, $hashed, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET display_name = ?, username = ?, role = ? WHERE id = ?");
                $stmt->execute([$display_name, $username, $role, $id]);
            }
            
            $logAction = "Modified workspace identity profile: $username.";
            $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'USER_MODIFIED', ?, ?)")
                ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);

            ob_clean();
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Username is already taken by another account.']);
        }
        exit;
    }
    
    // Delete User
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $id = (int)$_POST['id'];
        
        $uStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $uStmt->execute([$id]);
        $target = $uStmt->fetchColumn();

        // Hard-lock the core admin (ID 2) from being deleted
        if ($target && $id !== 2) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            
            $logAction = "Revoked access and terminated workspace identity: $target.";
            $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'USER_TERMINATED', ?, ?)")
                ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);
                
            ob_clean(); echo json_encode(['success' => true]);
        } else {
            ob_clean(); echo json_encode(['success' => false, 'message' => 'Cannot delete the protected core administrator.']);
        }
        exit;
    }
}

// Fetch Users for the UI Table
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT id, display_name, username, role, created_at FROM users ORDER BY created_at DESC");
    ob_clean();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
?>