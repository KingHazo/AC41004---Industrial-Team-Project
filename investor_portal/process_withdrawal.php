<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in']) || $_SESSION['userType'] !== 'investor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

include '../sql/db.php'; 

$investorID = $_SESSION['userId'];

$data = json_decode(file_get_contents('php://input'), true);

$accountNumber = trim($data['accountNumber'] ?? '');
$holderName = trim($data['holderName'] ?? '');
$amount = floatval($data['amount'] ?? 0);

if (empty($accountNumber) || empty($holderName) || $amount <= 0 || !is_numeric($amount)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid withdrawal details or amount.']);
    exit();
}

try {
    $mysql->beginTransaction();

    $sql_investor = "UPDATE Investor 
            SET InvestorBalance = InvestorBalance - :amount
            WHERE InvestorID = :investorID AND InvestorBalance >= :amount";

    $stmt_investor = $mysql->prepare($sql_investor);
    $stmt_investor->bindParam(':amount', $amount);
    $stmt_investor->bindParam(':investorID', $investorID);
    $stmt_investor->execute();

    if ($stmt_investor->rowCount() === 0) {
        throw new Exception("Withdrawal failed: Insufficient funds in Investor Balance or Investor record not found.");
    }
    
    $sql_bank = "UPDATE Bank 
                 SET Balance = Balance + :amount
                 WHERE AccountNumber = :accountNumber AND HolderName = :holderName";

    $stmt_bank = $mysql->prepare($sql_bank);
    $stmt_bank->bindParam(':amount', $amount);
    $stmt_bank->bindParam(':accountNumber', $accountNumber);
    $stmt_bank->bindParam(':holderName', $holderName);
    $stmt_bank->execute();
    
    if ($stmt_bank->rowCount() === 0) {
        throw new Exception("Withdrawal failed: Bank account not found or account details are incorrect.");
    }

    $mysql->commit();

    echo json_encode(['success' => true, 'message' => 'Withdrawal successful!', 'amount_withdrawn' => $amount]);

} catch (Exception $e) {
    if ($mysql->inTransaction()) {
        $mysql->rollBack();
    }
    error_log("Withdrawal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
