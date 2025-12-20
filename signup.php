<?php
include 'connection.php';

$name     = $_POST['name'];
$email    = $_POST['email'];
$phone    = $_POST['phone'];
$password = $_POST['password'];
$cpass    = $_POST['confirmPassword'];

if($password !== $cpass){
    echo "<script>alert('Passwords do not match'); window.history.back();</script>";
    exit();
}

// Secure password encryption
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert into your REAL TABLE
$query = "INSERT INTO customersignup (name, email, phone, password) 
          VALUES ('$name', '$email', '$phone', '$hashedPassword')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Signup successful! Please login now.'); window.location='customer_login.php';</script>";
} else {
    echo "<script>alert('Error: Email might already exist'); window.history.back();</script>";
}
?>
