<?php
session_start();
include 'connection.php';

$error = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if(empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Check if user exists
        $query = "SELECT * FROM customersignup WHERE email='$email'";
        $result = mysqli_query($conn, $query);

        if(mysqli_num_rows($result) == 1){
            $row = mysqli_fetch_assoc($result);

            // Verify hashed password
            if(password_verify($password, $row['password'])){
                // Store session variables
                $_SESSION['customer_id'] = $row['id'];
                $_SESSION['customer_name'] = $row['name'];

                // Redirect to dashboard
                header("Location: customer_dashboard.php");
                exit();
            } else {
                $error = "Incorrect password!";
            }

        } else {
            $error = "Email not found!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Login</title>
    <link rel="stylesheet" href="customerlogin.css" />
  </head>
  <body>
    <div class="login-box">
      <h2>Customer Login</h2>

      <!-- Display error message if any -->
      <?php if($error != "") { ?>
      <p style="color: red; text-align: center"><?php echo $error; ?></p>
      <?php } ?>

      <form action="" method="POST">
        <input
          type="email"
          id="email"
          name="email"
          placeholder="Email Address"
          required
        />
        <input
          type="password"
          id="password"
          name="password"
          placeholder="Password"
          required
        />
        <button type="submit">Login</button>
      </form>

      <div class="signup-link">
        Don't have an account? <a href="customer_signup.php">Signup</a>
      </div>
    </div>
  </body>
</html>
