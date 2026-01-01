
<?php
session_start();

include 'connection.php';
include 'validate_prescription.php';

// Check login FIRST
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Cart count
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart count
$cart_count = array_sum($_SESSION['cart']);


$customer_id = $_SESSION['customer_id'];

// Fetch customer info
$stmt = $conn->prepare("SELECT * FROM customersignup WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch initial medicines
$medicines_result = mysqli_query($conn, "SELECT * FROM meds");

// Fetch orders
$stmt2 = $conn->prepare("
    SELECT o.id AS order_id, m.med_name, o.quantity, o.total_price, o.order_date
    FROM orders o
    JOIN meds m ON o.medicine_id = m.med_id
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$stmt2->bind_param("i", $customer_id);
$stmt2->execute();
$orders_result = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Customer Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="customerdashboard.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
table { width:100%; border-collapse: collapse; }
th, td { padding:8px; border-bottom:1px solid #ccc; }
input, select { padding:7px; margin-right:5px; }
.card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; }
.sidebar a { display:block; padding:10px; text-decoration:none; }
small { font-size:0.8em; }
.disabled { color:gray; pointer-events:none; text-decoration:none; }
</style>
</head>

<body>
    <?php
if(isset($_SESSION['cart_success'])){
    $msg = $_SESSION['cart_success'];
    echo "<script>alert('$msg');</script>";
    unset($_SESSION['cart_success']);
}
if(isset($_SESSION['cart_error'])){
    $msg = $_SESSION['cart_error'];
    echo "<script>alert('Error: $msg');</script>";
    unset($_SESSION['cart_error']);
}
?>


<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Customer Panel</h2>
    <a href="#profile">👤 Profile</a>
    <a href="#medicines">💊 Medicines</a>
    <a href="#orders">🛒 Orders</a>
   <a href="cart.php">
    🛒 View Cart 
    <?php if($cart_count > 0): ?>
        <span style="color:red;">(<?= $cart_count ?>)</span>
    <?php endif; ?>
</a>
    <a href="profile_update.php">✏️ Update Profile</a>
    <a href="cus_logout.php">🚪 Logout</a>
</div>

<!-- CONTENT -->
<div class="content">

<!-- PROFILE -->
<div class="card" id="profile">
    <h3>👤 Customer Profile</h3>
    <p><strong>Name:</strong> <?= htmlspecialchars($customer['name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']); ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone']); ?></p>
</div>

<!-- MEDICINES (RECOMMENDATION ENABLED) -->
<div class="card" id="medicines">
    <h3>💊 Recommended Medicines</h3>

    <input type="text" id="searchMedicine" placeholder="Search medicine...">
    
    <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="Pain Relief">Pain Relief</option>
        <option value="Cold & Flu">Cold & Flu</option>
        <option value="Vitamins">Vitamins</option>
        <option value="AntiBiotics">AntiBiotics</option>
        <option value="Allergy">Allergy</option>
    </select>

    <table id="medicinesTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="medicinesBody">
<?php 
$current_cart = []; // no cart table, but validation still works

while($med = mysqli_fetch_assoc($medicines_result)) { 
    // Use correct column names
    $med_id = $med['MED_ID'];
    $med_name = $med['MED_NAME'];
    $med_qty = $med['MED_QTY'];
    $med_category = $med['CATEGORY'];
    $med_price = $med['MED_PRICE'];

    // Run prescription validation
    $errors = validatePrescription($med_id, 1, $current_cart);
?>
<tr>
    <td><?= htmlspecialchars($med_name); ?></td>
    <td><?= htmlspecialchars($med_category); ?></td>
    <td>Rs. <?= htmlspecialchars($med_price); ?></td>
    <td><?= htmlspecialchars($med_qty); ?></td>
    <td>
        <?php 
        if(!empty($errors)) {
            echo "<span class='disabled' title='" . implode(", ", $errors) . "'>⚠️ Cannot Add</span>";
        } else if(!empty($med_qty) && $med_qty > 0) {
            echo "<a href='add_to_cart.php?med_id=$med_id'>✅ Add to Cart</a>";
        } else {
            echo "<span style='color:red;'>❌ Out of stock</span>";
        }
        ?>
    </td>
</tr>
<?php } ?>
</tbody>

    </table>
</div>

<!-- ORDERS -->
<div class="card" id="orders">
    <h3>🛒 My Orders</h3>

    <?php if($orders_result->num_rows > 0){ ?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Medicine</th>
            <th>Qty</th>
            <th>Total</th>
            <th>Date</th>
        </tr>

        <?php while($o = $orders_result->fetch_assoc()){ ?>
        <tr>
            <td><?= $o['order_id']; ?></td>
            <td><?= htmlspecialchars($o['med_name'] ?? 'N/A'); ?></td>
            <td><?= $o['quantity'] ?? 0; ?></td>
            <td>Rs. <?= $o['total_price'] ?? 0; ?></td>
            <td><?= $o['order_date'] ?? 'N/A'; ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
        <p>No orders found.</p>
    <?php } ?>
</div>

</div>

<!-- AJAX SCRIPT (RECOMMENDATION) -->
<script>
$(document).ready(function(){
    function loadMedicines(){
        let search = $("#searchMedicine").val();
        let category = $("#categoryFilter").val();

        $.ajax({
            url: "recommend_medicine.php",
            method: "POST",
            data: { search: search, category: category },
            success: function(data){
                $("#medicinesBody").html(data); // update only tbody
            }
        });
    }

    loadMedicines(); // initial load
    $("#searchMedicine").on("keyup", loadMedicines);
    $("#categoryFilter").on("change", loadMedicines);
});
</script>

</body>
</html>
