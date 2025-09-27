<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Signup</title>
  <link rel="icon" href="login.png" type="image/x-icon">
  <link href="login.css" rel="stylesheet" />
</head>
<body>
<div class="container">
   <?php
    for ($i = 0; $i < 50; $i++) {
      echo '<span style="--i:'.$i.'"></span>';
    }
  ?>
  <div class="login-box">
    <form method="POST" action="process_signup.php">
      <h2>Admin Signup</h2>

      <div class="input-box">
        <input type="email" name="email" id="email" placeholder=" " required />
        <label for="email">Email</label>
      </div>

      <div class="input-box">
        <input type="password" name="password" id="password" placeholder=" " required />
        <label for="password">Password</label>
      </div>

      <div class="input-box">
        <input type="password" name="confirm_password" id="confirm_password" placeholder=" " required />
        <label for="confirm_password">Confirm Password</label>
      </div>

      <div class="input-box">
        <button type="submit" class="btn">Signup</button>
      </div>

      <div class="signup-link">
        <a href="ogin.php" class="signup-link">Login</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
