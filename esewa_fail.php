<?php
session_start();

// Payment failed or cancelled
$_SESSION['toast_error'] = "❌ Payment failed or cancelled";

// Redirect back to cart
header("Location: cart.php");
exit();
