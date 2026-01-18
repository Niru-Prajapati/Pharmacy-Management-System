<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$med_id = intval($_GET['med_id'] ?? 0);
if ($med_id <= 0) {
    $_SESSION['cart_error'] = "Invalid medicine!";
    header("Location: cart.php");
    exit();
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add or increase quantity
if (isset($_SESSION['cart'][$med_id])) {
    $_SESSION['cart'][$med_id]++;
} else {
    $_SESSION['cart'][$med_id] = 1;
}

// Success message (Daraz style)
$_SESSION['cart_success'] = "✅ Medicine added to cart successfully!";

// Redirect to cart page
header("Location: cart.php");
exit();
