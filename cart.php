<?php
session_start();
$showToast = false;
$toastMessage = "";

if (isset($_SESSION['cart_success'])) {
    $showToast = true;
    $toastMessage = $_SESSION['cart_success'];
    unset($_SESSION['cart_success']);
}

include 'connection.php';

// Check login
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Show success message (from add_to_cart.php)
if (isset($_SESSION['cart_success'])) {
    echo "<script>
        alert('" . addslashes($_SESSION['cart_success']) . "');
    </script>";
    unset($_SESSION['cart_success']);
}

// Show error message
if (isset($_SESSION['cart_error'])) {
    echo "<script>
        alert('" . addslashes($_SESSION['cart_error']) . "');
    </script>";
    unset($_SESSION['cart_error']);
}

// If cart is empty
if (empty($_SESSION['cart'])) {
    $_SESSION['toast_error'] = "Your cart is empty";
    header("Location: customer_dashboard.php#medicines");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <link rel="stylesheet" href="cart.css">
   
</head>

<body>
    <!-- Toast Notification -->
<div id="toast" class="toast">
    🛒 <?= htmlspecialchars($toastMessage); ?>
</div>


<h2>🛒 My Cart</h2>

<table>
    <tr>
        <th>Medicine</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
        <th>Action</th>
    </tr>

<?php
$grand_total = 0;

foreach ($_SESSION['cart'] as $med_id => $qty) {

    // Fetch medicine details
    $stmt = $conn->prepare("SELECT MED_NAME, MED_PRICE FROM meds WHERE MED_ID = ?");
    $stmt->bind_param("i", $med_id);
    $stmt->execute();
    $med = $stmt->get_result()->fetch_assoc();

    if (!$med) continue;

    $subtotal = $med['MED_PRICE'] * $qty;
    $grand_total += $subtotal;
?>

    <tr>
        <td><?= htmlspecialchars($med['MED_NAME']); ?></td>
        <td>Rs. <?= $med['MED_PRICE']; ?></td>

        <!-- Quantity Increase / Decrease -->
        <td>
            <a href="update_cart.php?med_id=<?= $med_id ?>&action=decrease">➖</a>
            <?= $qty ?>
            <a href="update_cart.php?med_id=<?= $med_id ?>&action=increase">➕</a>
        </td>

        <td>Rs. <?= $subtotal; ?></td>

        <td>
            <a href="remove_from_cart.php?med_id=<?= $med_id ?>" 
               onclick="return confirm('Remove this item from cart?');">
               ❌ Remove
            </a>
        </td>
    </tr>

<?php } ?>

    <tr>
        <td colspan="3" class="total">Grand Total</td>
        <td colspan="2" class="total">Rs. <?= $grand_total; ?></td>
    </tr>

</table>

<br>

<!-- Place Order Button -->
<form action="payment.php" method="post">
    <button type="submit">🧾 Place Order</button>
</form>

<br><br>
<a href="customer_dashboard.php#medicines" class="shop">⬅ Continue Shopping</a>
<script>
<?php if ($showToast): ?>
    const toast = document.getElementById("toast");
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 2500);
<?php endif; ?>
</script>



</body>
</html>
