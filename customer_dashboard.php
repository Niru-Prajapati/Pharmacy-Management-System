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
$cart_count = array_sum($_SESSION['cart']);

$customer_id = $_SESSION['customer_id'];

// Fetch customer info
$stmt = $conn->prepare("SELECT * FROM customersignup WHERE id = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

// Fetch medicines
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
table { width:100%; border-collapse: collapse; margin-top:10px;}
th, td { padding:8px; border-bottom:1px solid #ccc; }
input, select { padding:7px; margin-right:5px; }
.card { background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; }
.sidebar a { display:block; padding:10px; text-decoration:none; }
small { font-size:0.8em; }
 #suggestions {
    border: 1px solid #ccc;
    max-width: 300px;
    background: white;
    position: absolute;
    z-index: 1000;
}

.suggestion {
    padding: 8px;
    cursor: pointer;
}

.suggestion:hover {
    background: #f0f0f0;
}

.disabled {
    color: gray;
    cursor: pointer;
}


</style>
</head>

<body>

<?php
if(isset($_SESSION['cart_success'])){
    echo "<script>alert('".$_SESSION['cart_success']."');</script>";
    unset($_SESSION['cart_success']);
}
if(isset($_SESSION['cart_error'])){
    echo "<script>alert('Error: ".$_SESSION['cart_error']."');</script>";
    unset($_SESSION['cart_error']);
}
?>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Customer Panel</h2>
    <a href="#profile">👤 Profile</a>
    <a href="#medicines">💊 Medicines</a>
    <a href="#orders">🛒 Orders</a>
    <a href="cart.php">🛒 View Cart
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

<!-- MEDICINES -->
<div class="card" id="medicines">
<h3>💊 Recommended Medicines</h3>

<input type="text" id="searchBox" placeholder="Search medicine..." autocomplete="off">
<div id="suggestions"></div>

<select id="categoryFilter">
    <option value="">All Categories</option>
    <option value="Pain Relief">Pain Relief</option>
    <option value="Cold & Flu">Cold & Flu</option>
    <option value="Vitamins">Vitamins</option>
    <option value="AntiBiotics">AntiBiotics</option>
    <option value="Allergy">Allergy</option>
</select>

<table>
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
$current_cart = [];

while($med = mysqli_fetch_assoc($medicines_result)){
    $errors = validatePrescription($med['MED_ID'], 1, $current_cart);
?>
<tr>
    <td><?= htmlspecialchars($med['MED_NAME']); ?></td>
    <td><?= htmlspecialchars($med['CATEGORY']); ?></td>
    <td>Rs. <?= htmlspecialchars($med['MED_PRICE']); ?></td>
    <td><?= htmlspecialchars($med['MED_QTY']); ?></td>
    <td>
        <?php
        if(!empty($errors)){
            echo "<span class='disabled' title='".implode(", ", $errors)."'>⚠️ Cannot Add</span>";
        } elseif($med['MED_QTY'] > 0){
            echo "<a href='add_to_cart.php?med_id=".$med['MED_ID']."'>✅ Add to Cart</a>";
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
    <td><?= htmlspecialchars($o['med_name']); ?></td>
    <td><?= $o['quantity']; ?></td>
    <td>Rs. <?= $o['total_price']; ?></td>
    <td><?= $o['order_date']; ?></td>
</tr>
<?php } ?>
</table>
<?php } else { ?>
<p>No orders found.</p>
<?php } ?>
</div>

</div>

<!-- AJAX TABLE UPDATE -->
<script>
function loadMedicines(){
    let search = $("#searchBox").val();
    let category = $("#categoryFilter").val();

    $.ajax({
        url: "recommend_medicine.php",
        method: "POST",
        data: { search: search, category: category },
        success: function(data){
            $("#medicinesBody").html(data);
        }
    });
}

$(document).ready(function(){
    loadMedicines();
    $("#searchBox").on("keyup", loadMedicines);
    $("#categoryFilter").on("change", loadMedicines);
});
</script>

<!-- LIVE SUGGESTIONS -->
<script>
let selectedIndex = -1;

document.getElementById("searchBox").addEventListener("keyup", function (e) {
    let query = this.value;
    let suggestionsBox = document.getElementById("suggestions");

    // Keyboard navigation
    let items = document.querySelectorAll(".suggestion");

    if (e.key === "ArrowDown") {
        selectedIndex = (selectedIndex + 1) % items.length;
        highlight(items);
        return;
    }

    if (e.key === "ArrowUp") {
        selectedIndex = (selectedIndex - 1 + items.length) % items.length;
        highlight(items);
        return;
    }

    if (e.key === "Enter" && selectedIndex >= 0) {
        items[selectedIndex].click();
        selectedIndex = -1;
        return;
    }

    // Reset index on typing
    selectedIndex = -1;

    if (query.length < 2) {
        suggestionsBox.innerHTML = "";
        return;
    }

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "live_search.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    xhr.onload = function () {
        suggestionsBox.innerHTML = this.responseText;
    };

    xhr.send("query=" + query);
});

function highlight(items){
    items.forEach((item, index) => {
        item.style.background = index === selectedIndex ? "#72c6c9ff" : "#f3f4f5ff";
    });
}

function selectMedicine(name){
    document.getElementById("searchBox").value = name;
    document.getElementById("suggestions").innerHTML = "";
    selectedIndex = -1;
    loadMedicines(); // update table
}
</script>

<script>
function selectMedicine(medName){
    document.getElementById("searchBox").value = medName;
    document.getElementById("suggestions").innerHTML = "";
    loadMedicines(); // refresh medicine table
}
</script>


</body>
</html>
