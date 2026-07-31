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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_rates') {
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized action. Admin privileges required.']);
        exit;
    }

    try {
        $season = $_POST['season'];
        $tier = $_POST['tier'];
        $rates = json_decode($_POST['rates'], true);
        $ip = $_SERVER['REMOTE_ADDR'];
        
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

        $logAction = "Modified system rate matrix for $season ($tier).";
        $pdo->prepare("INSERT INTO system_logs (username, role, action_code, action, ip_address) VALUES (?, ?, 'RATES_UPDATE', ?, ?)")
            ->execute([$_SESSION['username'], $_SESSION['role'], $logAction, $ip]);

        ob_clean();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_rates') {
    try {
        $stmt = $pdo->query("SELECT * FROM system_rates");
        $dbRates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Auto-updating Dynamic Year Engine
        $y1 = date('Y');
        $y2 = $y1 + 1;

        $contractRatesDatabase = [
            "Festive Season" => ["label" => "Festive Seasons Rates<br><span class='text-[10px] font-semibold text-slate-500 mt-1 block'>21st Dec $y1 &ndash; 3rd Jan, $y2</span>"],
            "High Season" => ["label" => "HIGH SEASON<br><span class='text-[10px] font-semibold text-slate-500 mt-1 block'>4th Jan - 15th March, $y2</span>"],
            "Low Season" => ["label" => "LOW SEASON<br><span class='text-[10px] font-semibold text-slate-500 mt-1 block'>16th March - 30th June, $y2<br>1st Oct - 20th Dec, $y2</span>"],
            "Peak Season" => ["label" => "PEAK SEASON<br><span class='text-[10px] font-medium text-slate-500 mt-1 block'>1st July - 30th Sep, $y2</span>"]
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