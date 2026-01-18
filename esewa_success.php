<?php
session_start();
include 'connection.php';

// eSewa returns these parameters
$amt = $_GET['amt'] ?? 0;
$pid = $_GET['oid'] ?? $_GET['pid'] ?? '';
$refId = $_GET['refId'] ?? '';

// Security check
if (empty($pid) || empty($refId)) {
    $_SESSION['toast_error'] = "Invalid payment response";
    header("Location: cart.php");
    exit();
}

// User must be logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Cart must exist
if (empty($_SESSION['cart'])) {
    $_SESSION['toast_error'] = "Cart is empty";
    header("Location: customer_dashboard.php");
    exit();
}

// Place order for each cart item
foreach ($_SESSION['cart'] as $med_id => $qty) {

    // Get medicine info
    $stmt = $conn->prepare("SELECT MED_PRICE, MED_QTY FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();

    if (!$med || $med['MED_QTY'] < $qty) {
        $_SESSION['toast_error'] = "Stock issue while placing order";
        header("Location: cart.php");
        exit();
    }

    $total_price = $med['MED_PRICE'] * $qty;

    // Insert order
    $stmt2 = $conn->prepare("
        INSERT INTO orders (customer_id, medicine_id, quantity, total_price, payment_method, payment_status, order_date)
        VALUES (?, ?, ?, ?, 'Online (eSewa)', 'Paid', NOW())
    ");
    $stmt2->bind_param("iiid", $customer_id, $med_id, $qty, $total_price);
    $stmt2->execute();

    // Reduce stock
    $stmt3 = $conn->prepare("
        UPDATE meds SET MED_QTY = MED_QTY - ? WHERE MED_ID = ?
    ");
    $stmt3->bind_param("ii", $qty, $med_id);
    $stmt3->execute();
}

// Clear cart
unset($_SESSION['cart']);

// Success toast
$_SESSION['toast_success'] = "🎉 Payment successful! Order placed.";

// Redirect to orders section
header("Location: customer_dashboard.php#orders");
exit();
