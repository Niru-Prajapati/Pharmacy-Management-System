<?php
session_start();
include 'connection.php';

// 🔐 Check login
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// 🧾 Validate medicine ID
$med_id = filter_input(INPUT_GET, 'med_id', FILTER_VALIDATE_INT);

if (!$med_id || $med_id <= 0) {
    $_SESSION['cart_error'] = "Invalid medicine!";
    header("Location: customer_dashboard.php#medicines");
    exit();
}

// 🛒 Initialize cart (PERSISTENT)
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 🧮 Increase quantity safely
if (array_key_exists($med_id, $_SESSION['cart'])) {
    $_SESSION['cart'][$med_id]++;
} else {
    $_SESSION['cart'][$med_id] = 1;
}

// ✅ Store last added medicine (useful for UI highlight)
$_SESSION['last_added_med'] = $med_id;

// 🎉 Success message
$_SESSION['cart_success'] = "Medicine added to cart successfully!";

// 🔁 Redirect back
header("Location: customer_dashboard.php#medicines");
exit();
