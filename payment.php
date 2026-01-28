<?php
session_start();
include 'connection.php';

// Login check
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

// Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $med_id => $qty) {
    $stmt = $conn->prepare("SELECT MED_PRICE FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();
    $total += $med['MED_PRICE'] * $qty;
}

// Order ID
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
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .add{
            width:90%;
            padding:5px;
            

        }
        .num,.pay{
            width:90%;
            padding:5px;
            

        }
        select, button, textarea, input {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
        }
        textarea {
            resize: none;
        }
        button {
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="payment-box">
    <h2>💳 Payment</h2>

    <div class="total">
        Total Amount: Rs. <?= $total ?>
    </div>

    <!-- NORMAL ORDER FORM -->
    <form id="orderForm" method="post" action="place_order.php">
        <input type="hidden" name="order_id" value="<?= $order_id ?>">
        <input type="hidden" name="total_amount" value="<?= $total ?>">
        <div class="add">
        <!-- ✅ ADDED: DELIVERY ADDRESS -->
        <label>Delivery Address</label>
        <textarea name="address" required placeholder="Enter full delivery address"></textarea>
        </div>
        <div class="num">
        <!-- ✅ ADDED: CONTACT NUMBER -->
        <label>Contact Number</label>
        <input type="text" name="phone" required placeholder="98XXXXXXXX">
</div>
<div class="pay">
        <label>Payment Method</label>
        <!-- ✅ FIXED: added name attribute -->
        <select id="payment_method" name="payment_method" required>
            <option value="cod">Cash on Delivery</option>
            <option value="esewa">Online (eSewa)</option>
        </select></div>

        <button type="submit">Pay & Place Order</button>
    </form>
</div>

<!-- eSewa V2 FORM -->
<form id="esewaForm" action="https://rc-epay.esewa.com.np/api/epay/main/v2/form" method="POST">
    <input type="hidden" name="amount" value="<?= $total ?>">
    <input type="hidden" name="tax_amount" value="0">
    <input type="hidden" name="total_amount" value="<?= $total ?>">
    <input type="hidden" name="transaction_uuid" id="transaction_uuid">
    <input type="hidden" name="product_code" value="EPAYTEST">
    <input type="hidden" name="product_service_charge" value="0">
    <input type="hidden" name="product_delivery_charge" value="0">
    <input type="hidden" name="success_url" value="http://localhost/Project-D/esewa_success.php">
    <input type="hidden" name="failure_url" value="http://localhost/Project-D/esewa_fail.php">
    <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
    <input type="hidden" name="signature" id="signature">
</form>

<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>

<script>
document.getElementById("orderForm").addEventListener("submit", function (e) {

    const method = document.getElementById("payment_method").value;

    if (method === "esewa") {
        e.preventDefault();

        const totalAmount = "<?= $total ?>";
        const productCode = "EPAYTEST";
        const transactionUuid = "<?= $order_id ?>_" + Date.now();
        const secret = "8gBm/:&EnhH.1/q"; // UAT Secret Key

        document.getElementById("transaction_uuid").value = transactionUuid;

        const message = `total_amount=${totalAmount},transaction_uuid=${transactionUuid},product_code=${productCode}`;
        const hash = CryptoJS.HmacSHA256(message, secret);
        const signature = CryptoJS.enc.Base64.stringify(hash);

        document.getElementById("signature").value = signature;

        document.getElementById("esewaForm").submit();
    }
});
</script>

</body>
</html>
