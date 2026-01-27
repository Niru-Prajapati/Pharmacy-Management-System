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

foreach ($_SESSION['cart'] as $med_id => $qty) {

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

    $stmt2 = $conn->prepare("
        INSERT INTO orders (customer_id, medicine_id, quantity, total_price, order_date)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt2->bind_param("iiid", $customer_id, $med_id, $qty, $total_price);
    $stmt2->execute();

    $stmt3 = $conn->prepare("
        UPDATE meds SET MED_QTY = MED_QTY - ? WHERE MED_ID = ?
    ");
    $stmt3->bind_param("ii", $qty, $med_id);
    $stmt3->execute();
}

unset($_SESSION['cart']);

$_SESSION['cart_success'] = "🎉 Order placed successfully!";
header("Location: customer_dashboard.php#orders");
exit();