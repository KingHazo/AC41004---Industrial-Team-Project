<?php 
session_start();
header('Content-Type: application/json');

// check user and user type
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investor' || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

require_once dirname(__DIR__) . '/db.php';

if (!isset($mysql) || !($mysql instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

$investorID = $_SESSION['user_id'];
$pitchID = isset($_POST['pitch_id']) ? (int)$_POST['pitch_id'] : 0;
$investmentID = isset($_POST['investment_id']) ? (int)$_POST['investment_id'] : 0; // 0 for new investment
$newAmount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0.0;
$shares = isset($_POST['shares']) ? (int)$_POST['shares'] : 0;

if ($pitchID <= 0 || $newAmount <= 0 || $shares <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid pitch, investment amount, or calculated shares provided.']);
    exit();
}

try {
    // get pitch details and investor balance
    $sql_data = "
        SELECT 
            P.CurrentAmount, P.TargetAmount, P.WindowEndDate,
            I.InvestorBalance
        FROM Pitch P, Investor I
        WHERE P.PitchID = :pitchID AND I.InvestorID = :investorID
    ";
    $stmt_data = $mysql->prepare($sql_data);
    $stmt_data->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_data->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_data->execute();
    $data = $stmt_data->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Pitch or Investor not found.']);
        exit();
    }
    
    $pitchCurrentAmount = (float)$data['CurrentAmount'];
    $pitchTargetAmount = (float)$data['TargetAmount'];
    $investorBalance = (float)$data['InvestorBalance'];

    // is pitch window is closed or funded
    if (strtotime($data['WindowEndDate']) < time() || $pitchCurrentAmount >= $pitchTargetAmount) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'The funding window is closed or the pitch is already funded.']);
        exit();
    }
    
    $oldAmount = 0.0;
    $isUpdate = false;

    // --- CRITICAL FIX 1: Check for existing investment without resetting $investmentID ---
    if ($investmentID > 0) {
        $sql_check_inv = "SELECT Amount FROM Investment WHERE InvestmentID = :investmentID AND InvestorID = :investorID AND PitchID = :pitchID";
        $stmt_check_inv = $mysql->prepare($sql_check_inv);
        $stmt_check_inv->bindParam(':investmentID', $investmentID, PDO::PARAM_INT);
        $stmt_check_inv->bindParam(':investorID', $investorID, PDO::PARAM_INT);
        $stmt_check_inv->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
        $stmt_check_inv->execute();
        $existing = $stmt_check_inv->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $oldAmount = (float)$existing['Amount'];
            $isUpdate = true;
        }
    }
    // get the change in the investment required
    $netAmountChange = $newAmount - $oldAmount;

    // check if enough balance (only needed if investment is increasing)
    if ($netAmountChange > 0 && $investorBalance < $netAmountChange) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Insufficient balance for the additional investment amount.']);
        exit();
    }
    
    // check if the pitch would exceed target
    if ($netAmountChange > 0 && $pitchCurrentAmount + $netAmountChange > $pitchTargetAmount) {
        http_response_code(400);
        $max_allowed_top_up = $pitchTargetAmount - $pitchCurrentAmount;
        $max_total_investment = $oldAmount + $max_allowed_top_up;
        
        echo json_encode([
            'success' => false, 
            'message' => 'This investment amount would exceed the pitch target amount. Max total investment allowed: £' . number_format($max_total_investment, 2) . '.'
        ]);
        exit();
    }

    // start transaction
    $mysql->beginTransaction();

    $message = $isUpdate ? 'Investment successfully updated.' : 'Investment successfully created.';

    if ($isUpdate) {
        $sql_inv = "
            UPDATE Investment SET Amount = :newAmount, CalculateShare = :shares, DateMade = NOW()
            WHERE InvestmentID = :investmentID
        ";
        $stmt_inv = $mysql->prepare($sql_inv);
        $stmt_inv->bindParam(':investmentID', $investmentID, PDO::PARAM_INT);
    } else if ($investmentID === 0) {
        $sql_inv = "
            INSERT INTO Investment (InvestorID, PitchID, Amount, CalculateShare, DateMade) 
            VALUES (:investorID, :pitchID, :newAmount, :shares, NOW())
        ";
        $stmt_inv = $mysql->prepare($sql_inv);
        $stmt_inv->bindParam(':investorID', $investorID, PDO::PARAM_INT);
        $stmt_inv->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    } else {
         throw new \Exception("Update failed: Investment record not found for update (ID: $investmentID). State error.");
    }
    // shared parameters
    $stmt_inv->bindParam(':newAmount', $newAmount);
    $stmt_inv->bindParam(':shares', $shares, PDO::PARAM_INT);
    $stmt_inv->execute();
    
    // if new insert, get the ID
    if (!$isUpdate) {
        $investmentID = $mysql->lastInsertId();
    }

    // update investor balance 
    $sql_investor = "UPDATE Investor SET InvestorBalance = InvestorBalance - :netAmountChange WHERE InvestorID = :investorID";
    $stmt_investor = $mysql->prepare($sql_investor);
    $stmt_investor->bindParam(':netAmountChange', $netAmountChange);
    $stmt_investor->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_investor->execute();

    // update pitch current amount
    $sql_pitch_update = "UPDATE Pitch SET CurrentAmount = CurrentAmount + :netAmountChange WHERE PitchID = :pitchID";
    $stmt_pitch_update = $mysql->prepare($sql_pitch_update);
    $stmt_pitch_update->bindParam(':netAmountChange', $netAmountChange);
    $stmt_pitch_update->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_pitch_update->execute();
    
    $mysql->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'investment_id' => $investmentID
    ]);

} catch (PDOException $e) {
    if ($mysql->inTransaction()) {
        $mysql->rollBack();
    }
    // Log the full error to your server logs for debugging
    error_log("Investment Transaction Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred during the investment transaction. Please check server logs for details.']);
}
