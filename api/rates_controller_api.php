<?php
// api/rates_controller_api.php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require '../Includes/database.php';
header('Content-Type: application/json');

// Handle POST request to update rates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_rates') {
    // Optional: restrict rate modifications strictly to Admins if desired
    // if (strtolower($_SESSION['role'] ?? '') !== 'admin') {
    //     echo json_encode(['success' => false, 'message' => 'Unauthorized action']);
    //     exit;
    // }

    try {
        $season = $_POST['season'];
        $tier = $_POST['tier'];
        $rates = json_decode($_POST['rates'], true);
        
        $stmt = $pdo->prepare("UPDATE system_rates SET ksh_rate = ?, usd_rate = ? WHERE season = ? AND room_tier = ? AND room_config = ?");
        foreach($rates as $config => $vals) {
            $stmt->execute([
                (float)$vals['ksh'], 
                (float)$vals['usd'], 
                $season, 
                $tier, 
                $config
            ]);
        }

        // Log action
        $pdo->prepare("INSERT INTO system_logs (username, action) VALUES (?, ?)")->execute([$_SESSION['username'], "Updated rates for $season ($tier)"]);

        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle GET request to fetch rates matrix
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_rates') {
    try {
        $stmt = $pdo->query("SELECT * FROM system_rates");
        $dbRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $contractRatesDatabase = [
            "Festive Season" => ["label" => "Festive Seasons Rates<br><span class='text-[10px] font-medium text-slate-400 mt-0.5 block'>21st Dec 2025 – 3rd Jan, 2026</span>"],
            "High Season" => ["label" => "HIGH SEASON<br><span class='text-[10px] font-medium text-slate-400 mt-0.5 block'>4th Jan- 15th March, 2026</span>"],
            "Low Season" => ["label" => "LOW SEASON<br><span class='text-[10px] font-medium text-slate-400 mt-0.5 block'>16th March - 30th June, 2026<br>1st Oct - 20th Dec, 2026</span>"],
            "Peak Season" => ["label" => "PEAK SEASON<br><span class='text-[10px] font-medium text-slate-400 mt-0.5 block'>1st July- 30th Sep, 2026</span>"]
        ];

        foreach ($dbRates as $row) {
            $s = $row['season'];
            $t = $row['room_tier'];
            $c = $row['room_config'];
            
            if (!isset($contractRatesDatabase[$s][$t])) {
                $contractRatesDatabase[$s][$t] = [];
            }
            
            $contractRatesDatabase[$s][$t][$c] = [
                'ksh' => (float)$row['ksh_rate'],
                'usd' => (float)$row['usd_rate']
            ];
        }

        ob_clean();
        echo json_encode(['success' => true, 'data' => $contractRatesDatabase]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>