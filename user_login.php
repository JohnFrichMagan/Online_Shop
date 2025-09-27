<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Login</title>
  <link rel="icon" href="login.png" type="image/x-icon">
  <link href="login.css" rel="stylesheet" />
  <style>
    .input-box {
      position: relative;
      display: flex;
      flex-direction: column;
      margin-bottom: 20px;
    }

    .input-box input {
      width: 100%;
      padding: 12px;
      border-radius: 25px;
      border: 1px solid #444;
      background: transparent;
      color: #fff;
      font-size: 16px;
    }

    .input-box label {
      position: absolute;
      top: 50%;
      left: 15px;
      transform: translateY(-50%);
      color: #aaa;
      font-size: 14px;
      pointer-events: none;
      transition: 0.3s ease;
    }

    .input-box input:focus ~ label,
    .input-box input:valid ~ label {
      top: -8px;
      font-size: 12px;
      color: #28e3ff;
    }

   .show-pass {
  display: flex;
  justify-content: center;  /* ✅ centers horizontally */
  align-items: center;
  margin-top: 5px;          /* ✅ small spacing below password box */
  margin-bottom: 15px;
  font-size: 14px;
  color: #ccc;
}

.show-pass input {
  margin-right: 8px;
}


    .message {
      text-align: center;
      font-weight: bold;
      margin-bottom: 10px;
    }

    .message.error { color: red; }
    .message.success { color: green; }
  </style>
</head>
<body>
<div class="container">
   <?php
    for ($i = 0; $i < 50; $i++) {
      echo '<span style="--i:'.$i.'"></span>';
    }
  ?>
  <div class="login-box">
    <form method="POST" action="process_user_login.php">
      <h2>Login</h2>

      <?php if (isset($_SESSION['error'])): ?>
        <p class="message error">
          <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']); 
          ?>
        </p>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <p class="message success">
          <?php 
            echo $_SESSION['success']; 
            unset($_SESSION['success']); 
          ?>
        </p>
      <?php endif; ?>

      <div class="input-box">
        <input type="email" name="email" id="email" required />
        <label for="email">Email</label>
      </div>

      <div class="input-box">
        <input type="password" name="password" id="password" required />
        <label for="password">Password</label>
      </div>

      <!-- Show Password checkbox -->
      <div class="show-pass">
        <input type="checkbox" id="showPassword" onclick="togglePassword()">
        <label for="showPassword">Show Password</label>
      </div>

      <div class="forgot-pass">
        <a href="#">Forgot your password?</a>
      </div>

      <div class="input-box">
        <button type="submit" class="btn">Login</button>
      </div>

      <div class="signup-link">
        <a href="user_signup.php">Signup</a>
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
