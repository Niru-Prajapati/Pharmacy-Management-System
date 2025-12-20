<?php
session_start();
include 'connection.php';

// Check if user is logged in
if(!isset($_SESSION['customer_id'])){
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch current user data to prefill the form
$sql = "SELECT * FROM customersignup WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $phone    = $_POST['phone'];
    $password = $_POST['password'];

    if(empty($name) || empty($email) || empty($phone)){
        echo "<script>alert('All fields except password are required!');</script>";
    } else {
        if(!empty($password)){
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update = "UPDATE customersignup SET name=?, email=?, phone=?, password=? WHERE id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssssi", $name, $email, $phone, $hashed_password, $customer_id);
        } else {
            $sql_update = "UPDATE customersignup SET name=?, email=?, phone=? WHERE id=?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $name, $email, $phone, $customer_id);
        }

        if($stmt_update->execute()){
            echo "<script>alert('Profile updated successfully!'); window.location='customer_dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error updating profile!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Update Profile</title>
  <link rel="stylesheet" href="dashboard.css" />
</head>
<body>
  <nav class="navbar">
    <h2>Update Profile</h2>
    <ul>
      <li><a href="customer_dashboard.php">Back</a></li>
    </ul>
  </nav>

  <section class="form-box">
    <h3>Edit Your Information</h3>

    <form action="" method="POST">
      <input type="text" name="name" placeholder="Full Name" value="<?php echo htmlspecialchars($user['name']); ?>" required />
      <input type="email" name="email" placeholder="Email Address" value="<?php echo htmlspecialchars($user['email']); ?>" required />
      <input type="text" name="phone" placeholder="Phone Number" value="<?php echo htmlspecialchars($user['phone']); ?>" required />
      <input type="password" name="password" placeholder="New Password (optional)" />

      <button type="submit">Update</button>
    </form>
  </section>
</body>
</html>
