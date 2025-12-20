<?php
session_start();
include 'connection.php';
include 'validate_prescription.php';

if(!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$med_id = $_GET['med_id'] ?? 0;
$quantity = $_POST['quantity'] ?? 1;

// Validate med_id
if(!$med_id || $med_id <= 0){
    $_SESSION['cart_error'] = "Invalid medicine ID.";
    header("Location: customer_dashboard.php");
    exit();
}

// Fetch current cart items for validation
$cart_result = mysqli_query($conn, "
    SELECT m.MED_NAME, c.quantity 
    FROM cart c 
    JOIN meds m ON c.med_id = m.MED_ID 
    WHERE c.customer_id = $customer_id
");
$current_cart = mysqli_fetch_all($cart_result, MYSQLI_ASSOC);

// Prescription validation
$errors = validatePrescription($med_id, $quantity, $current_cart);

if(!empty($errors)){
    $_SESSION['cart_error'] = implode(", ", $errors);
    header("Location: customer_dashboard.php");
    exit();
}

// Add to cart with duplicate handling
$stmt = $conn->prepare("
    INSERT INTO cart (customer_id, med_id, quantity) 
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");
$stmt->bind_param("iii", $customer_id, $med_id, $quantity);
$stmt->execute();

// Set success message
$_SESSION['cart_success'] = "✅ Medicine added to cart!";
header("Location: customer_dashboard.php");
exit();
?>
