<?php
require 'Includes/database.php'; // Updated to point to your new Includes folder

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = 'admin';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // 1. Delete old admin to avoid duplicates
        $pdo->query("DELETE FROM users WHERE username = 'admin'");

        // 2. Insert fresh admin user with the 'Admin' role
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'Admin')");
        
        if ($stmt->execute([$username, $hashed_password])) {
            $message = "Success! Fresh admin account created.<br><br><b>Username:</b> admin<br><b>Password:</b> admin123<br><b>Role:</b> Admin";
        } else {
            $error = "Failed to create user. Check your database setup.";
        }
    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Setup - Rhino Reservation</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2>Rhino System Initialization</h2>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
            Click below to generate a default administrator account.
        </p>

        <?php if($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if($message): ?>
            <div class="success-msg"><?php echo $message; ?></div>
            <a href="login.php" class="btn-secondary" style="display: block; margin-top: 15px;">Go to Login</a>
        <?php else: ?>
            <form method="POST" action="setup_admin.php">
                <button type="submit" class="btn-primary">Initialize Admin Account</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>