<?php
session_start();
include 'connection.php';



/* APPROVE = Confirm order (Pending → Confirmed) */
if (isset($_POST['approve'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("
        UPDATE orders
        SET order_status = 'Confirmed'
        WHERE id = ? AND order_status = 'Pending'
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

/* RECEIVE PAYMENT = COD only */
if (isset($_POST['receive_payment'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("
        UPDATE orders
        SET payment_status = 'Paid'
        WHERE id = ? AND payment_method = 'COD' AND payment_status = 'Pending'
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

/* DELIVER = Confirmed → Delivered */
if (isset($_POST['deliver'])) {
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("
        UPDATE orders
        SET order_status = 'Delivered',
            payment_status = CASE 
                WHEN payment_method='COD' THEN 'Paid'
                ELSE payment_status
            END
        WHERE id = ? AND order_status = 'Confirmed'
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

header("Location: pharm_orders.php");
exit();
