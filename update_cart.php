<?php
session_start();

$med_id = intval($_GET['med_id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!isset($_SESSION['cart'][$med_id])) {
    header("Location: cart.php");
    exit();
}

if ($action === 'increase') {
    $_SESSION['cart'][$med_id] += 1;
}

if ($action === 'decrease') {
    $_SESSION['cart'][$med_id] -= 1;

    // If qty becomes 0, remove item
    if ($_SESSION['cart'][$med_id] <= 0) {
        unset($_SESSION['cart'][$med_id]);
    }
}

header("Location: cart.php");
exit();
