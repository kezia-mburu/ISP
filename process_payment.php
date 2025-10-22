<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// --- Security check ---
if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Safaricom Daraja credentials
$consumerKey = "O134sF588jvnbOlD6XktjoOJOPkruvYJY2UvdGfVG1JL0bIF";
$consumerSecret = "filibGJ9yJbtHRGIqSC70YdBo8h5BV0nZbpPxOI6wpC0nADossBWj2AysF0NqR9a";
$businessShortCode = "YOUR_SHORTCODE";
$passkey = "HqQbuDlb8evjBQ7sIeaUkWVO2B/dSkKVXtht+bB9hM3FO9ynQj2lnY0R/lQsj7B5h4v39rUXqtqnssU2yoa8v5JQnJnrS05G0BLbzn5PqysLRevP6k0B6yetd9Jte/bDGU0QUMs6eBlju6/36b311qhC6TS8Ery7O9S/5BhHWL+7JqfqvJDh8Bu28HPdzZyoMLaRake4OJ/1YBYPh1C3G2G/tzKbIib5o+Tr4tRvNoqbSNiiDWkit8x0r2+GzR7COokqNnw5TEwekef7REJZ6kFfvjBhOyAe053WehqzMqNaIOFh6m96wYwORsowELILNr8ZvLSto9LGi/jN34LJrg==";
$callbackUrl = "https://yourdomain.com/callback.php";


// --- Get POST data ---
$client_id = $_SESSION['client_id'];
$phone = trim($_POST['phone'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$plan = $_POST['plan'] ?? 'Custom';

// Validate
if (!preg_match('/^2547\d{8}$/', $phone) || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone or amount']);
    exit;
}

// --- Generate password for STK ---
$password = base64_encode($businessShortCode . $passkey . $timestamp);

// --- Get access token ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$access_token = json_decode($response)->access_token ?? null;
if (!$access_token) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate access token']);
    exit;
}

// --- STK Push request ---
$stk_data = [
    'BusinessShortCode' => $businessShortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $businessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackURL,
    'AccountReference' => $plan,
    'TransactionDesc' => "Payment for $plan"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $access_token
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

$res = json_decode($result, true);

// --- Handle Daraja response ---
if (isset($res['ResponseCode']) && $res['ResponseCode'] == "0") {
    $checkoutRequestID = $res['CheckoutRequestID'];

    // Save to DB as pending
    $stmt = $conn->prepare("INSERT INTO transactions 
        (client_id, username, phone, plan, amount, status, checkout_request_id, method) 
        VALUES (?, ?, ?, ?, ?, 'pending', ?, 'M-Pesa STK')");
    $stmt->bind_param("isssds", $client_id, $_SESSION['username'], $phone, $plan, $amount, $checkoutRequestID);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['status' => 'success', 'message' => 'STK Push sent. Enter PIN on your phone.', 'checkout_id' => $checkoutRequestID]);
} else {
    echo json_encode(['status' => 'error', 'message' => $res['errorMessage'] ?? 'Failed to initiate STK Push']);
}
