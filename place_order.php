<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

if (empty($_SESSION['cart'])) {
    $_SESSION['cart_error'] = "Your cart is empty!";
    header("Location: customer_dashboard.php#medicines");
    exit();
}

/* ✅ REQUIRED FIELDS FROM payment.php */
$payment_method = $_POST['payment_method'] ?? 'cod';
$delivery_address = $_POST['address'] ?? '';
$phone = $_POST['phone'] ?? '';

/* Status setup */
$payment_status = ($payment_method === 'esewa') ? 'Paid' : 'Pending';
$order_status = 'Pending';

/* Safety check */
if (empty($delivery_address) || empty($phone)) {
    $_SESSION['cart_error'] = "Delivery details missing!";
    header("Location: payment.php");
    exit();
}

foreach ($_SESSION['cart'] as $med_id => $qty) {

    // Fetch medicine
    $stmt = $conn->prepare("SELECT MED_PRICE, MED_QTY FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();

    if (!$med || $med['MED_QTY'] < $qty) {
        $_SESSION['cart_error'] = "Insufficient stock for one of the medicines.";
        header("Location: customer_dashboard.php#medicines");
        exit();
    }

    $total_price = $med['MED_PRICE'] * $qty;

    /* ✅ INSERT FULL ORDER DATA */
    $stmt2 = $conn->prepare("
        INSERT INTO orders 
        (customer_id, medicine_id, quantity, total_price, order_date,
         payment_method, payment_status, order_status, delivery_address, phone)
        VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?)
    ");

    $stmt2->bind_param(
        "iiidsssss",
        $customer_id,
        $med_id,
        $qty,
        $total_price,
        $payment_method,
        $payment_status,
        $order_status,
        $delivery_address,
        $phone
    );

    $stmt2->execute();

    /* Reduce stock */
    $stmt3 = $conn->prepare("UPDATE meds SET MED_QTY = MED_QTY - ? WHERE MED_ID = ?");
    $stmt3->bind_param("ii", $qty, $med_id);
    $stmt3->execute();
}

/* Clear cart */
unset($_SESSION['cart']);

$_SESSION['cart_success'] = "🎉 Order placed successfully!";
header("Location: customer_dashboard.php#orders");
exit();
