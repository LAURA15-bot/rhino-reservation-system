<?php
// database.php - Core System Configuration & Connection

// 1. Initialize Global Session
// This ensures that any file requiring database.php automatically handles user login states.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Database Connection Parameters
// Update these if your live server credentials differ from your local environment.
$host = '127.0.0.1'; // Use 127.0.0.1 instead of localhost for slightly faster socket resolution
$dbname = 'rhino_reservation';
$username = 'root';
$password = ''; // Default XAMPP/WAMP password is empty
$charset = 'utf8mb4'; // Supports full Unicode (including emojis and international characters)

// 3. Data Source Name (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

// 4. PDO Security and Error Handling Options
$options = [
    // Force PDO to throw exceptions on errors so we can catch them cleanly in our APIs
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Automatically fetch data as associative arrays (e.g., $row['column_name'])
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Disable emulated prepares to ensure true prepared statements (Protects against SQL Injection)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 5. Establish the Database Connection
try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    // If the API fails to connect, return a JSON error rather than breaking the HTML page
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Fatal Error: Database connection failed. ' . $e->getMessage()
    ]);
    exit;
}

// 6. Enforce Secure Session Inactivity Checks Globally
require_once __DIR__ . '/session_check.php';
?>