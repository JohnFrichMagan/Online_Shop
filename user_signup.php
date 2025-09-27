<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Signup</title>
  <link rel="icon" href="login.png" type="image/x-icon">
  <link href="login.css" rel="stylesheet" />
  <style>
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
    // Optional animated background
    for ($i = 0; $i < 50; $i++) {
      echo '<span style="--i:'.$i.'"></span>';
    } 
    ?>
    <div class="login-box">
      <form method="POST" action="process_user_signup.php" onsubmit="return validateSignup()">
        <h2>Signup</h2>

        <!-- Error / Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
          <p class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
          <p class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Email -->
        <div class="input-box">
          <input type="email" name="email" id="email" required />
          <label for="email">Email</label>
        </div>

        <!-- Password -->
        <div class="input-box">
          <input type="password" name="password" id="password" minlength="6" required />
          <label for="password">Password (min 6 chars)</label>
        </div>

        <!-- Confirm Password -->
        <div class="input-box">
          <input type="password" name="confirm_password" id="confirm_password" minlength="6" required />
          <label for="confirm_password">Confirm Password</label>
        </div>

        <!-- Submit -->
        <div class="input-box">
          <button type="submit" class="btn">Signup</button>
        </div>

        <div class="signup-link">
          <a href="user_login.php">Already have an account? Login</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function validateSignup() {
      const password = document.getElementById("password").value;
      const confirm = document.getElementById("confirm_password").value;

      if (password !== confirm) {
        alert("Passwords do not match!");
        return false; // prevent submit
      }
      return true;
    }
  </script>
</body>
</html>
