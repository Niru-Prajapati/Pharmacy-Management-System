<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    $_SESSION['cart_error'] = "Your cart is empty!";
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
?>

<h2>Payment Page</h2>
<p>Total Amount: Rs. <?= $total ?></p>

<form method="post" action="placeorder.php">
    <label>Payment Method:</label>
    <select name="payment_method" id="payment_method" required>
        <option value="Cash on Delivery">Cash on Delivery</option>
        <option value="Online">Online (eSewa)</option>
    </select><br><br>

    <button type="submit">Pay & Place Order</button>
</form>

<!-- Optional: Auto redirect to eSewa if Online selected -->
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    var method = document.getElementById('payment_method').value;
    if(method === 'Online') {
        e.preventDefault();
        // Redirect to eSewa payment page
        var total = <?= $total ?>;
        var params = {
            amt: total,
            psc: 0,
            pdc: 0,
            tAmt: total,
            pid: "ORDER_<?= time() ?>",
            scd: "YOUR_MERCHANT_CODE",
            su: "http://localhost/Project-D/esewa_success.php",
            fu: "http://localhost/Project-D/esewa_fail.php"
        };

        // Build query string
        var query = Object.keys(params).map(k => k + '=' + encodeURIComponent(params[k])).join('&');
        window.location.href = "https://uat.esewa.com.np/epay/main?" + query;
    }
});
</script>
