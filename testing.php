<?php
// process_payment.php
include 'db.php'; // your DB connection

// Safaricom Daraja credentials
$consumerKey = "YOUR_CONSUMER_KEY";
$consumerSecret = "YOUR_CONSUMER_SECRET";
$businessShortCode = "YOUR_SHORTCODE";
$passkey = "YOUR_PASSKEY";
$callbackUrl = "https://yourdomain.com/callback.php";

// Collect form data
$username = mysqli_real_escape_string($conn, $_POST['username']);
$phone    = mysqli_real_escape_string($conn, $_POST['phone']);
$plan     = mysqli_real_escape_string($conn, $_POST['plan']);
$amount   = mysqli_real_escape_string($conn, $_POST['amount']);

// Generate unique account reference
$accountReference = "FASTNET-" . rand(1000, 9999);

// Timestamp
$timestamp = date("YmdHis");
$password = base64_encode($businessShortCode . $passkey . $timestamp);

// 1️⃣ Request access token
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials");
curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode($consumerKey . ":" . $consumerSecret)]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($curl);
curl_close($curl);

$token = json_decode($response)->access_token;

// 2️⃣ STK Push request
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest");
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $token"
]);

$payload = [
    "BusinessShortCode" => $businessShortCode,
    "Password"          => $password,
    "Timestamp"         => $timestamp,
    "TransactionType"   => "CustomerPayBillOnline",
    "Amount"            => $amount,
    "PartyA"            => $phone,
    "PartyB"            => $businessShortCode,
    "PhoneNumber"       => $phone,
    "CallBackURL"       => $callbackUrl,
    "AccountReference"  => $accountReference,
    "TransactionDesc"   => $plan . " Subscription"
];

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
$response = curl_exec($curl);
curl_close($curl);

$result = json_decode($response, true);

// 3️⃣ Save to database as PENDING
if (isset($result['CheckoutRequestID'])) {
    $checkoutId = $result['CheckoutRequestID'];

    $sql = "INSERT INTO transactions 
        (client_id, username, phone, plan, amount, status, method, checkout_request_id, created_at) 
        VALUES (NULL, '$username', '$phone', '$plan', '$amount', 'pending', 'M-Pesa STK', '$checkoutId', NOW())";

    if (mysqli_query($conn, $sql)) {
        echo json_encode([
            "success" => true,
            "message" => "STK Push sent. Enter M-Pesa PIN to complete.",
            "checkout_id" => $checkoutId
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . mysqli_error($conn)
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "STK Push request failed.",
        "details" => $result
    ]);
}
?>
