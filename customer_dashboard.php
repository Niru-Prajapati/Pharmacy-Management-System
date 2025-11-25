<?php
session_start();
include 'connection.php';

// Check if customer is logged in
if(!isset($_SESSION['customer_id'])){
    header("Location: customer_login.php");
    exit();
}

// Store session customer ID
$customer_id = $_SESSION['customer_id'];

// Fetch customer info safely
$customer_query = "SELECT * FROM customersignup WHERE id='$customer_id'";
$customer_result = mysqli_query($conn, $customer_query);

if(!$customer_result || mysqli_num_rows($customer_result) == 0){
    // Customer not found
    echo "Customer record not found. Please login again.";
    exit();
}

$customer = mysqli_fetch_assoc($customer_result);

// Fetch available medicines
$medicines_query = "SELECT * FROM medicines";
$medicines_result = mysqli_query($conn, $medicines_query);

// Fetch customer orders
$orders_query = "SELECT o.id as order_id, m.med_name as medicine_name, o.quantity, o.total_price, o.order_date
                 FROM orders o
                 JOIN medicines m ON o.medicine_id = m.med_id
                 WHERE o.customer_id='$customer_id'
                 ORDER BY o.order_date DESC";
$orders_result = mysqli_query($conn, $orders_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customer Dashboard</title>
<link rel="stylesheet" href="customerdashboard.css">
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
    <h2>Customer Panel</h2>
    <a href="#profile">👤 Profile</a>
    <a href="#medicines">💊 Medicines</a>
    <a href="#orders">🛒 Orders</a>
    <a href="profile_update.php">✏️ Update Profile</a>
    <a href="cus_logout.php">🚪 Logout</a>
</div>

<div class="content">

<!-- PROFILE SECTION -->
<div class="card" id="profile">
    <h3>👤 Customer Profile</h3>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone']); ?></p>
</div>

<!-- MEDICINES SECTION -->
<div class="card" id="medicines">
    <h3>💊 Available Medicines</h3>
    <table>
        <tr>
            <th>Medicine Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php if($medicines_result && mysqli_num_rows($medicines_result) > 0){ ?>
            <?php while($med = mysqli_fetch_assoc($medicines_result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($med['med_name']); ?></td>
                <td><?php echo htmlspecialchars($med['category']); ?></td>
                <td>Rs. <?php echo htmlspecialchars($med['med_price']); ?></td>
                <td><a href="add_to_cart.php?med_id=<?php echo $med['med_id']; ?>">Add to Cart</a></td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="4">No medicines available.</td></tr>
        <?php } ?>
    </table>
</div>

<!-- ORDER HISTORY SECTION -->
<div class="card" id="orders">
    <h3>🛒 My Orders</h3>
    <?php if($orders_result && mysqli_num_rows($orders_result) > 0){ ?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Medicine</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Date</th>
        </tr>
        <?php while($order = mysqli_fetch_assoc($orders_result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
            <td><?php echo htmlspecialchars($order['medicine_name']); ?></td>
            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
            <td>Rs. <?php echo htmlspecialchars($order['total_price']); ?></td>
            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
    <p>No orders found yet.</p>
    <?php } ?>
</div>

</div>
</body>
</html>
