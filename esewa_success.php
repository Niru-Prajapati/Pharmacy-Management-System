<?php
session_start();
include 'connection.php';

// 1. Check if data is received
if (!isset($_GET['data'])) {
    die("Payment verification failed: no data received");
}

// 2. Decode the data
$data_json = base64_decode($_GET['data']);
$data = json_decode($data_json, true);

if (!$data) {
    die("Payment verification failed: invalid data");
}

// 3. Read values
$transaction_uuid = $data['transaction_uuid'] ?? null;
$total_amount     = $data['total_amount'] ?? null;
$status           = $data['status'] ?? null;

// 4. Basic validation
if (!$transaction_uuid || !$total_amount || $status !== "COMPLETE") {
    die("Payment verification failed");
}

// 5. Prevent duplicate payment
$stmt = $conn->prepare("SELECT id FROM payments WHERE transaction_uuid = ?");
$stmt->bind_param("s", $transaction_uuid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Transaction already processed");
}

// 6. Save payment
$stmt = $conn->prepare("
    INSERT INTO payments (transaction_uuid, amount, payment_method, status)
    VALUES (?, ?, 'eSewa', 'Success')
");
$stmt->bind_param("sd", $transaction_uuid, $total_amount);
$stmt->execute();

// 7. Place order for each cart item
foreach ($_SESSION['cart'] as $med_id => $qty) {

    // Get price
    $stmt2 = $conn->prepare("SELECT MED_PRICE FROM meds WHERE MED_ID = ?");
    $stmt2->bind_param("i", $med_id);
    $stmt2->execute();
    $med = $stmt2->get_result()->fetch_assoc();

    $total_price = $med['MED_PRICE'] * $qty;

    $stmt3 = $conn->prepare("
        INSERT INTO orders (customer_id, medicine_id, quantity, total_price, order_date)
        VALUES (?, ?, ?, ?, CURDATE())
    ");
    $stmt3->bind_param("iiid", $_SESSION['customer_id'], $med_id, $qty, $total_price);
    $stmt3->execute();
}

// 8. Clear cart
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Success</title>
    <style>
        body { font-family: Arial; background: #eafaf1; text-align: center; padding-top: 80px; }
        .box { background: #fff; padding: 40px; border-radius: 10px; display: inline-block; }
        h1 { color: #27ae60; }
        a { display: inline-block; margin-top: 20px; text-decoration: none; color: white; background: #27ae60; padding: 10px 20px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="box">
    <h1>✅ Payment Successful</h1>
    <p>Your order has been placed successfully.</p>
    <p><strong>Transaction ID:</strong> <?= htmlspecialchars($transaction_uuid) ?></p>
    <a href="customer_dashboard.php">Go to Dashboard</a>
</div>
</body>
</html>
