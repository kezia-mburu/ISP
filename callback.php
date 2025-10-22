<?php
require_once 'db.php';
header("Content-Type: application/json");

// Get raw JSON from Safaricom
$data = file_get_contents('php://input');
$logFile = "callback_log.json";
file_put_contents($logFile, $data . PHP_EOL, FILE_APPEND);

$callback = json_decode($data, true);

if (!isset($callback['Body']['stkCallback'])) {
    http_response_code(400);
    echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback']);
    exit;
}

$stkCallback = $callback['Body']['stkCallback'];
$checkoutRequestID = $stkCallback['CheckoutRequestID'];
$resultCode = $stkCallback['ResultCode'];
$resultDesc = $stkCallback['ResultDesc'];

if ($resultCode == 0) {
    // Success
    $amount = $stkCallback['CallbackMetadata']['Item'][0]['Value'] ?? 0;
    $mpesaCode = $stkCallback['CallbackMetadata']['Item'][1]['Value'] ?? '';
    $phone = $stkCallback['CallbackMetadata']['Item'][4]['Value'] ?? '';

    $stmt = $conn->prepare("UPDATE transactions 
        SET status='paid', mpesa_code=?, phone=?, amount=?, updated_at=NOW() 
        WHERE checkout_request_id=?");
    $stmt->bind_param("sdss", $mpesaCode, $phone, $amount, $checkoutRequestID);
    $stmt->execute();
    $stmt->close();
} else {
    // Failed or cancelled
    $stmt = $conn->prepare("UPDATE transactions SET status='failed', notes=? WHERE checkout_request_id=?");
    $stmt->bind_param("ss", $resultDesc, $checkoutRequestID);
    $stmt->execute();
    $stmt->close();
}

// Respond back to Safaricom
echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback processed successfully']);
