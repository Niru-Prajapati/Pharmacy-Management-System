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
      body {
        font-family: Arial, sans-serif;
        background: #f5f5f5;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 900px;
        margin: 40px auto;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
      }
      h2 {
        text-align: center;
        margin-bottom: 20px;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th,
      td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ccc;
      }
      th {
        background-color: #f0f0f0;
      }
      tr:hover {
        background-color: #f9f9f9;
      }
      .no-orders {
        text-align: center;
        color: #888;
        margin-top: 20px;
      }
      .back-link {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #007bff;
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

      <a href="customer_dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
  </body>
</html>
