<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
    <style>
        body {
            font-family: Arial;
            background: #fdecea;
            text-align: center;
            padding-top: 80px;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            display: inline-block;
        }
        h1 {
            color: #e74c3c;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: white;
            background: #e74c3c;
            padding: 10px 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>❌ Payment Failed</h1>
    <p>Your payment was not completed.</p>
    <p>Please try again.</p>

    <a href="cart.php">Back to Cart</a>
</div>

</body>
</html>
