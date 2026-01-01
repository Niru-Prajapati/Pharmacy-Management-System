<?php
session_start();

$med_id = intval($_GET['med_id'] ?? 0);

if (isset($_SESSION['cart'][$med_id])) {
    unset($_SESSION['cart'][$med_id]);
}

header("Location: cart.php");
exit();
