<?php
session_start();
include 'connection.php';

// Check login
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Cart empty check
if (empty($_SESSION['cart'])) {
    $_SESSION['toast_error'] = "Your cart is empty";
    header("Location: customer_dashboard.php#medicines");
    exit();
}

// Calculate total amount
$total = 0;
foreach ($_SESSION['cart'] as $med_id => $qty) {
    $stmt = $conn->prepare("SELECT MED_PRICE FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();
    $total += $med['MED_PRICE'] * $qty;
}

// Unique order id
$order_id = "ORDER_" . time();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .payment-box {
            max-width: 420px;
            margin: 60px auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50; /* Daraz orange */
            text-align: center;
            margin-bottom: 25px;
        }
        label {
            font-weight: bold;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #2c3e50;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0a2036;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #555;
        }
    </style>
</head>

<body>

<div class="payment-box">
    <h2>💳 Payment</h2>

    <div class="total">
        Total Amount: Rs. <?= $total ?>
    </div>

    <form id="paymentForm" method="post" action="place_order.php">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="total_amount" value="<?= $total ?>">

        <label>Payment Method</label>
        <select name="payment_method" id="payment_method" required>
            <option value="Cash on Delivery">Cash on Delivery</option>
            <option value="Online">Online (eSewa)</option>
        </select>

        <button type="submit">Pay & Place Order</button>
    </form>

    <a class="back" href="cart.php">⬅ Back to Cart</a>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var method = document.getElementById('payment_method').value;

    if (method === 'Online') {
        e.preventDefault();

        var total = <?= $total ?>;

        var params = {
            amt: total,
            psc: 0,
            pdc: 0,
            txAmt: 0,
            tAmt: total,
            pid: "ORDER_<?= time() ?>",
            scd: "EPAYTEST",
            su: "http://localhost/Project-D/esewa_success.php",
            fu: "http://localhost/Project-D/esewa_fail.php"
        };

        // Create a POST form dynamically
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "https://esewa.com.np/epay/main";

        // Add all parameters as hidden inputs
        for (var key in params) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
});
</script>



</body>
</html>
