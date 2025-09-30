<?php session_start();
header('Content-Type: application/json');

// check authentication and user type
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

$investorID = $_SESSION['user_id'];

$investmentID = isset($_POST['investment_id']) ? (int)$_POST['investment_id'] : 0;

if ($investmentID <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid investment ID provided.']);
    exit();
}

try {
    // check investment and status
    $sql_check = "
        SELECT 
            I.PitchID, 
            I.Amount,
            P.TargetAmount, 
            P.CurrentAmount, 
            P.WindowEndDate
        FROM Investment I
        JOIN Pitch P ON I.PitchID = P.PitchID
        WHERE I.InvestmentID = :investmentID AND I.InvestorID = :investorID
    ";
    
    $stmt_check = $mysql->prepare($sql_check);
    $stmt_check->bindParam(':investmentID', $investmentID, PDO::PARAM_INT);
    $stmt_check->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_check->execute();
    $investment = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$investment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Investment not found or does not belong to you.']);
        exit();
    }

    $currentFunding = (float)$investment['CurrentAmount'];
    $targetFunding = (float)$investment['TargetAmount'];
    $windowEndDate = $investment['WindowEndDate'];
    $investmentAmount = (float)$investment['Amount'];
    $pitchID = (int)$investment['PitchID'];

    // cant be funded (target reached) or closed (window ended)
    $isFunded = $currentFunding >= $targetFunding;
    $isClosed = strtotime($windowEndDate) < time();

    if ($isFunded || $isClosed) {
        http_response_code(403);
        $reason = $isFunded ? 'target reached' : 'funding window closed';
        echo json_encode(['success' => false, 'message' => "Cancellation failed: Pitch is already {$reason}."]);
        exit();
    }

    // start deletion
    $mysql->beginTransaction();

    // delete the investment
    $sql_delete = "DELETE FROM Investment WHERE InvestmentID = :investmentID AND InvestorID = :investorID";
    $stmt_delete = $mysql->prepare($sql_delete);
    $stmt_delete->bindParam(':investmentID', $investmentID, PDO::PARAM_INT);
    $stmt_delete->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_delete->execute();

    // update pitches amount
    $newCurrentAmount = $currentFunding - $investmentAmount;
    $sql_update_pitch = "UPDATE Pitch SET CurrentAmount = :newCurrentAmount WHERE PitchID = :pitchID";
    $stmt_update_pitch = $mysql->prepare($sql_update_pitch);
    $stmt_update_pitch->bindParam(':newCurrentAmount', $newCurrentAmount);
    $stmt_update_pitch->bindParam(':pitchID', $pitchID, PDO::PARAM_INT);
    $stmt_update_pitch->execute();

    // update investor balance
    $sql_update_balance = "UPDATE Investor SET InvestorBalance = InvestorBalance + :amount WHERE InvestorID = :investorID";
    $stmt_update_balance = $mysql->prepare($sql_update_balance);
    $stmt_update_balance->bindParam(':amount', $investmentAmount);
    $stmt_update_balance->bindParam(':investorID', $investorID, PDO::PARAM_INT);
    $stmt_update_balance->execute();

    $mysql->commit();

    echo json_encode(['success' => true, 'message' => 'Investment successfully cancelled and deleted.']);

} catch (PDOException $e) {
    if ($mysql->inTransaction()) {
        $mysql->rollBack();
    }
    error_log("Cancellation Transaction Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred during cancellation.']);
}
?>
