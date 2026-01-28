<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

if (!isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit();
}

$order_id = $_GET['order_id'];

$stmt = $conn->prepare("
    SELECT o.*, m.MED_NAME
    FROM orders o
    JOIN meds m ON o.medicine_id = m.MED_ID
    WHERE o.id = ? AND o.customer_id = ?
");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Order Details</title>
<style>
body {
    font-family: Poppins, sans-serif;
    background:#f5f6fa;
}
.card {
    max-width:600px;
    margin:40px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 8px 18px rgba(0,0,0,0.1);
}
h2 {
    text-align:center;
}
.row {
    margin:10px 0;
}
.label {
    font-weight:600;
    color:#555;
}
.status {
    padding:6px 14px;
    border-radius:20px;
    font-size:14px;
}
.pending { background:#fff3cd; }
.delivered { background:#d4edda; }
</style>
</head>

<body>
<div class="card">
<h2>📦 Order Details</h2>

<div class="row"><span class="label">Order ID:</span> <?= $order['id']; ?></div>
<div class="row"><span class="label">Medicine:</span> <?= htmlspecialchars($order['MED_NAME']); ?></div>
<div class="row"><span class="label">Quantity:</span> <?= $order['quantity']; ?></div>
<div class="row"><span class="label">Total Price:</span> Rs. <?= $order['total_price']; ?></div>

<div class="row">
  <span class="label">Payment Method:</span>
  <?= htmlspecialchars($order['payment_method'] ?? 'Not Available'); ?>
</div>

<div class="row">
  <span class="label">Payment Status:</span>
    <span class="status <?= strtolower($order['order_status'] ?? 'pending'); ?>">

  <?= htmlspecialchars($order['payment_status'] ?? 'Not Available'); ?>
</div>

<div class="row">
  <span class="label">Order Status:</span>
  <span class="status <?= strtolower($order['order_status'] ?? 'pending'); ?>">
    <?= htmlspecialchars($order['order_status'] ?? 'Pending'); ?>
  </span>
</div>

<div class="row">
  <span class="label">Delivery Address:</span><br>
  <?= htmlspecialchars($order['delivery_address'] ?? 'Not Provided'); ?>
</div>


<div class="row">
  <span class="label">Order Date:</span>
  <?= $order['order_date']; ?>
</div>

<a href="order_history.php">← Back to Orders</a>
</div>
</body>
</html>
