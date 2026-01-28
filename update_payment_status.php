<?php
session_start();
include 'connection.php';



if(isset($_POST['receive_payment'])){
    $order_id = $_POST['order_id'];

    $stmt = $conn->prepare("
        UPDATE orders
        SET payment_status = 'Paid'
        WHERE id = ? AND payment_method = 'COD' AND payment_status = 'Pending'
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
}

header("Location: admin_orders.php");
exit();
