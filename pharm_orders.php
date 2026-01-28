<?php
session_start();
include 'connection.php';



/* Fetch orders: Pending and Confirmed so admin can take action */
$result = $conn->query("
SELECT o.id, o.customer_id, m.MED_NAME, o.quantity,
       o.total_price, o.payment_method, o.payment_status,
       o.order_status
FROM orders o
JOIN meds m ON o.medicine_id = m.MED_ID
WHERE o.order_status IN ('Pending','Confirmed')
ORDER BY o.order_date ASC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Orders</title>
<link href="pharm_orders.css" rel="stylesheet">
</head>
<body>

<h2 style="text-align:center; margin-bottom:20px;">📦 Admin Orders</h2>

<table>
<tr>
  <th>Order ID</th>
  <th>Customer</th>
  <th>Medicine</th>
  <th>Qty</th>
  <th>Total</th>
  <th>Payment</th>
  <th>Payment Status</th>
  <th>Order Status</th>
  <th>Action</th>
  <th>Payment Action</th>
</tr>

<?php while ($row = $result->fetch_assoc()): ?>
<tr>
  <td><?= $row['id']; ?></td>
  <td><?= $row['customer_id']; ?></td>
  <td><?= htmlspecialchars($row['MED_NAME']); ?></td>
  <td><?= $row['quantity']; ?></td>
  <td>Rs. <?= $row['total_price']; ?></td>
  <td><?= strtoupper($row['payment_method']); ?></td>
  <td>
      <?php if(strtolower($row['payment_status'])=='pending'): ?>
          <span class="status-badge pending">Pending</span>
      <?php else: ?>
          <span class="status-badge paid">Paid</span>
      <?php endif; ?>
  </td>
  <td>
      <?php 
          if(strtolower($row['order_status'])=='pending') echo '<span class="status-badge pending">Pending</span>';
          elseif(strtolower($row['order_status'])=='confirmed') echo '<span class="status-badge confirmed">Confirmed</span>';
      ?>
  </td>

  <!-- Action: Approve / Deliver -->
  <td>
    <?php if(strtolower($row['order_status'])=='pending'): ?>
        <form method="post" action="update_order_status.php" style="display:inline;">
            <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
            <button name="approve">Confirm Order</button>
        </form>
    <?php elseif(strtolower($row['order_status'])=='confirmed'): ?>
        <form method="post" action="update_order_status.php" style="display:inline;">
            <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
            <button name="deliver">Mark Delivered</button>
        </form>
    <?php endif; ?>
  </td>

  <!-- Payment Action: Only for COD pending -->
  <td>
    <?php if(strtolower($row['payment_method']) == 'cod' && strtolower($row['payment_status']) == 'pending'): ?>
        <form method="post" action="update_order_status.php" style="display:inline;">
            <input type="hidden" name="order_id" value="<?= $row['id']; ?>">
            <button type="submit" name="receive_payment">Payment Received</button>
        </form>
    <?php elseif(strtolower($row['payment_status'])=='paid'): ?>
        <span class="status-badge paid">Paid</span>
    <?php endif; ?>
  </td>

</tr>
<?php endwhile; ?>
</table>

</body>
</html>
