<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link rel="icon" href="login.png" type="image/x-icon">
  <link href="login.css" rel="stylesheet" />
  <style>
    .show-pass {
      display: flex;
      justify-content: center; /* ✅ centers horizontally */
      align-items: center;
      margin-top: 5px;
      margin-bottom: 15px;
      font-size: 14px;
      color: #ccc;
    }

    .show-pass input {
      margin-right: 8px;
    }
  </style>
</head>
<body>
<div class="container">
   <?php
    // Animated background spans
    for ($i = 0; $i < 50; $i++) {
      echo '<span style="--i:'.$i.'"></span>';
    }
  ?>
  <div class="login-box">
    <form method="POST" action="process_login.php">
      <h2>Admin Login</h2>

      <!-- Error message -->
      <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red; text-align: center; font-weight: bold;">
          <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']); 
          ?>
        </p>
      <?php endif; ?>

      <!-- Success message -->
      <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green; text-align: center; font-weight: bold;">
          <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
          ?>
        </p>
      <?php endif; ?>

      <div class="input-box">
        <input type="email" name="email" id="email" placeholder=" " required />
        <label for="email">Email</label>
      </div>

      <div class="input-box">
        <input type="password" name="password" id="password" placeholder=" " required />
        <label for="password">Password</label>
      </div>

      <!-- ✅ Show Password Checkbox -->
      <div class="show-pass">
        <input type="checkbox" onclick="togglePassword()"> Show Password
      </div>

      <div class="forgot-pass">
        <a href="#">Forgot your password?</a>
      </div>

      <div class="input-box">
        <button type="submit" class="btn">Login</button>
      </div>

      <div class="signup-link">
        <a href="signup.php">Admin Signup</a>
      </div>
    </form>
  </div>
</div>

<script>
  function togglePassword() {
    const field = document.getElementById("password");
    field.type = field.type === "password" ? "text" : "password";
  }
</script>
</body>
</html>
