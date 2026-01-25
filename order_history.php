<?php
session_start();
include 'connection.php';

if(!isset($_SESSION['customer_id'])){
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch orders for this customer
$stmt = $conn->prepare(" SELECT o.id AS order_id, m.MED_NAME, o.quantity,
o.total_price, o.order_date FROM orders o JOIN meds m ON o.medicine_id =
m.MED_ID WHERE o.customer_id = ? ORDER BY o.order_date DESC ");
$stmt->bind_param("i", $customer_id); $stmt->execute(); $result =
$stmt->get_result(); ?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Order History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
   <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap');

* {
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: #f5f6fa;
  margin: 0;
  padding: 0;
}

/* Container card */
.container {
  max-width: 1200px;
  margin: 30px auto;
  padding: 25px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* Title */
h2 {
  text-align: center;
  margin-bottom: 25px;
  color: #222;
}

/* Table */
table {
  width: 100%;
  border-collapse: collapse;
  overflow: hidden;
  border-radius: 10px;
}

/* Header */
th {
  background: #2c3e50;
  color: #fff;
  padding: 14px;
  font-weight: 500;
  text-align: center;
}

/* Cells */
td {
  padding: 14px;
  text-align: center;
  border-bottom: 1px solid #eee;
  color: #333;
  font-size: 15px;
}

/* Row hover */
tbody tr {
  transition: background 0.25s ease, transform 0.2s ease;
}

tbody tr:hover {
  background: #fff7f0;
  transform: scale(1.01);
}

/* Price highlight */
td:nth-child(4) {
  font-weight: 600;
  color: #2c3e50;
}

/* No orders message */
.no-orders {
  text-align: center;
  color: #888;
  font-size: 16px;
  padding: 40px 0;
}

/* Back button */
.back-link {
  display: inline-block;
  margin: 20px auto 40px;
  padding: 12px 22px;
  border-radius: 25px;
  background: #fff;
  color: #2c3e50;
  border: 2px solid #2c3e50;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.3s ease;
}

.back-link:hover {
  background: #2c3e50;
  color: #fff;
  box-shadow: 0 6px 14px rgba(255,111,0,0.35);
}

/* Mobile responsiveness */
@media (max-width: 768px) {
  table, thead, tbody, th, td, tr {
    display: block;
  }

  thead {
    display: none;
  }

  tbody tr {
    background: #fff;
    margin-bottom: 15px;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
  }

  td {
    text-align: right;
    padding: 10px 0;
    border: none;
    position: relative;
  }

  td::before {
    content: attr(data-label);
    position: absolute;
    left: 0;
    font-weight: 500;
    color: #555;
  }
}
</style>

  </head>
  <body>
    <div class="container">
      <h2>🛒 My Orders</h2>

      <?php if($result->num_rows > 0){ ?>
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Medicine</th>
            <th>Quantity</th>
            <th>Total Price</th>
            <th>Order Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = $result->fetch_assoc()){ ?>
          <tr>
            <td><?= htmlspecialchars($row['order_id']); ?></td>
            <td><?= htmlspecialchars($row['MED_NAME']); ?></td>
            <td><?= htmlspecialchars($row['quantity']); ?></td>
            <td>
              Rs.
              <?= htmlspecialchars($row['total_price']); ?>
            </td>
            <td><?= htmlspecialchars($row['order_date']); ?></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
      <p class="no-orders">You have not placed any orders yet.</p>
      <?php } ?>
      </div>
      <a href="customer_dashboard.php" class="back-link">← Back to Dashboard</a>
    
  </body>
</html>
