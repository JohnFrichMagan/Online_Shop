<?php
session_start();
include 'db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php"); // admin login page
    exit();
}

$admin_id = $_SESSION['admin_id'];
$current_email = $_SESSION['admin_email'];

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email)) {
        if (!empty($password)) {
            // Update email + password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET email=?, password=? WHERE id=?");
            $stmt->bind_param("ssi", $email, $hashedPassword, $admin_id);
        } else {
            // Update only email
            $stmt = $conn->prepare("UPDATE admins SET email=? WHERE id=?");
            $stmt->bind_param("si", $email, $admin_id);
        }

        if ($stmt->execute()) {
            $success = "✅ Settings updated successfully!";
            $_SESSION['admin_email'] = $email; // Update session email
            $current_email = $email;
        } else {
            $error = "❌ Failed to update settings. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "❌ Email cannot be empty.";
    }
}

// Fetch current admin data again safely
$currentAdmin = [];
$stmt = $conn->prepare("SELECT email FROM admins WHERE id=?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $currentAdmin = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Settings | Dashboard</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#1f293a;
    color:#333;
}
header {
    background:#3b82f6;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 8px rgba(0,0,0,0.15);
}
header h2 {margin:0;color:#fff;font-weight:600;}
nav {
    flex:1;
    display:flex;
    justify-content:center;
    gap:20px;
}
nav a {
    display:inline-block;
    text-decoration:none;
    color:#fff;
    font-weight:500;
    padding:10px 22px;
    border-radius:12px;
    background:linear-gradient(135deg,#2563eb,#3b82f6);
    box-shadow:0 4px 12px rgba(59,130,246,0.4);
    transition:all 0.3s ease;
}
nav a:hover {background:linear-gradient(135deg,#3b82f6,#2563eb); transform:translateY(-3px);}
nav a.active {background:#1e40af; box-shadow:0 4px 12px rgba(30,64,175,0.6);}
.logout {
    text-decoration:none;
    background:#ef4444;
    color:#fff;
    padding:10px 20px;
    border-radius:12px;
    font-weight:500;
    transition:all 0.3s ease;
}
.logout:hover {background:#dc2626; transform:scale(1.05);}

.page-title {text-align:center; color:#fff; margin:30px 0; font-size:2rem;}

.settings-container {
    max-width:500px;
    margin:0 auto 50px auto;
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.15);
}
.settings-container h2 {
    color:#2563eb;
    margin-bottom:20px;
    text-align:center;
}
.settings-container input {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:1rem;
}
.settings-container button {
    width:100%;
    padding:12px;
    margin-top:15px;
    border:none;
    border-radius:10px;
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff;
    font-weight:500;
    cursor:pointer;
    transition:all 0.3s ease;
}
.settings-container button:hover {
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    transform:translateY(-2px);
}
.success {color:green; text-align:center; margin-bottom:10px;}
.error {color:red; text-align:center; margin-bottom:10px;}
</style>
</head>
<body>
<header>
    <h2>Admin Dashboard</h2>
    <nav>
        <a href="home.php" class="<?= basename($_SERVER['PHP_SELF'])=='home.php'?'active':'' ?>">🏠 Dashboard</a>
        <a href="products.php" class="<?= basename($_SERVER['PHP_SELF'])=='products.php'?'active':'' ?>">📦 Products</a>
        <a href="order.php" class="<?= basename($_SERVER['PHP_SELF'])=='order.php'?'active':'' ?>">🛒 Orders</a>
        <a href="report.php" class="<?= basename($_SERVER['PHP_SELF'])=='report.php'?'active':'' ?>">📊 Reports</a>
        <a href="manage.php" class="<?= basename($_SERVER['PHP_SELF'])=='report.php'?'active':'' ?>">📋 Manage</a>
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
</header>

<h1 class="page-title">Settings</h1>

<div class="settings-container">
    <h2>Update Admin Account</h2>
    <?php if($success) echo "<div class='success'>$success</div>"; ?>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" 
               value="<?php echo isset($currentAdmin['email']) ? htmlspecialchars($currentAdmin['email']) : ''; ?>" required>
        <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
        <button type="submit" name="update_settings">Update Settings</button>
    </form>
</div>
</body>
</html>
