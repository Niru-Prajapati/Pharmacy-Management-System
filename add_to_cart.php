<?php
session_start();
include 'connection.php';

// Check login
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$med_id = intval($_GET['med_id'] ?? 0);
if ($med_id <= 0) {
    $_SESSION['cart_error'] = "Invalid medicine!";
    header("Location: customer_dashboard.php#medicines");
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add medicine to cart
if (isset($_SESSION['cart'][$med_id])) {
    $_SESSION['cart'][$med_id] += 1;
} else {
    $_SESSION['cart'][$med_id] = 1;
}

// Success message
$_SESSION['cart_success'] = "Medicine added to cart!";

// Redirect back to dashboard
header("Location: customer_dashboard.php#medicines");
exit();
