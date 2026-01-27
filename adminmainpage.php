<?php
	
	session_start();
	$username=$_SESSION['user'];
?>
<!DOCTYPE html>
<html>

<head>
<link rel="stylesheet" type="text/css" href="nav2.css">
<title>
 Dashboard
</title>
</head>

<body>
	<div class="container">
	<div class="sidenav">
			<h2 style="font-family:Arial; color:white; text-align:center;"> PHARMACY  </h2>
			<a href="adminmainpage.php">Dashboard</a>
			<button class="dropdown-btn">Inventory
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="inventory-add.php">Add New Medicine</a>
				<a href="inventory-view.php">Manage Inventory</a>
			</div>
			 <button class="dropdown-btn">Suppliers
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="supplier-add.php">Add New Supplier</a>
				<a href="supplier-view.php">Manage Suppliers</a>
			</div> 
			<button class="dropdown-btn">Stock Purchase
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="purchase-add.php">Add New Purchase</a>
				<a href="purchase-view.php">Manage Purchases</a>
			</div>
			<button class="dropdown-btn">Employees
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="employee-add.php">Add New Employee</a>
				<a href="employee-view.php">Manage Employee</a>
			</div>
			<button class="dropdown-btn">Customers
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="customer-add.php">Add New Customer</a>
				<a href="customer-view.php">Manage Customer</a>
			</div>
			
			<a href="salesitems-view.php">View Sold Products Details</a>
			
			<button class="dropdown-btn">Reports
			<i class="down"></i>
			</button>
			<div class="dropdown-container">
				<a href="stockreport.php">Medicines - Low Stock</a>
				<a href="expiryreport.php">Medicines - Soon to Expire</a>
			</div>
	</div>

	<div class="topnav">
		<a onclick="return confirm('Are you sure to logout?');" href="logout.php">Logout(Logged in as Admin)</a>
	</div>
	
	<center>
	<div class="head">
	<h2> ADMIN DASHBOARD </h2>
	</div>
	</center>
	<div class="icon-grid">
    <a href="supplier-view.php" title="View Suppliers">
        <img src="https://cdn2.iconfinder.com/data/icons/shopping-e-commerce-1-1/32/Add-Cart-Product-Buy-Plus-512.png" alt="Suppliers">
    </a>

    <a href="inventory-view.php" title="View Inventory">
        <img src="https://cdn-icons-png.flaticon.com/512/9984/9984209.png" alt="Inventory">
    </a>

    <a href="pharmacist-view.php" title="View employee">
        <img src="https://cdn-icons-png.flaticon.com/512/2621/2621849.png" alt="Employees List">
    </a>

    <a href="salesitems-view.php" title="View Sold Products">
        <img src="https://cdn-icons-png.flaticon.com/256/11000/11000628.png" alt="Sold Products">
    </a>

    <a href="expiryreport.php" title="Soon to Expire">
        <img src="https://thumbs.dreamstime.com/b/raster-medical-help-warning-triangle-sign-icon-flat-uses-black-yellow-colors-symbol-style-attention-white-background-182313707.jpg" alt="Soon to Expire">
    </a>
</div>

	
</div>
</body>

<script>
	var dropdown = document.getElementsByClassName("dropdown-btn");
	var i;

	for (i = 0; i < dropdown.length; i++) {
	  dropdown[i].addEventListener("click", function() {
	  this.classList.toggle("active");
	  var dropdownContent = this.nextElementSibling;
	  if (dropdownContent.style.display === "block") {
	  dropdownContent.style.display = "none";
	  } else {
	  dropdownContent.style.display = "block";
	  }
	  });
	}
</script>

</html>