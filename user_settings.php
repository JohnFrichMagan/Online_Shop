<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- Handle Profile Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    $old_password = trim($_POST['old_password']);

    // Profile Picture Upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $profile_pic = "uploads/profile_" . $user_id . "." . $ext;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
        } else {
            $msg = "❌ Invalid image format. Use JPG, PNG, or GIF.";
        }
    }

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_password);
    $stmt->fetch();
    $stmt->close();

    // If changing password, require old password check
    if ($new_password) {
        if (!$old_password) {
            $msg = "❌ Please enter your old password.";
        } elseif (!password_verify($old_password, $db_password)) {
            $msg = "❌ Old password is incorrect.";
        }
    }

    // Proceed if no error
    if (!$msg) {
        $sql = "UPDATE users SET name=?, email=? " . ($new_password ? ", password=? " : "") . ($profile_pic ? ", profile_pic=? " : "") . "WHERE id=?";
        $stmt = $conn->prepare($sql);

        if ($new_password && $profile_pic) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt->bind_param("ssssi", $new_name, $new_email, $hashed, $profile_pic, $user_id);
        } elseif ($new_password) {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt->bind_param("sssi", $new_name, $new_email, $hashed, $user_id);
        } elseif ($profile_pic) {
            $stmt->bind_param("sssi", $new_name, $new_email, $profile_pic, $user_id);
        } else {
            $stmt->bind_param("ssi", $new_name, $new_email, $user_id);
        }

        if ($stmt->execute()) {
            $msg = "✅ Profile updated successfully!";
            $_SESSION['email'] = $new_email;
        } else {
            $msg = "❌ Error updating profile: " . $stmt->error;
        }
    }
}

// --- Fetch User Info ---
$stmt = $conn->prepare("SELECT name, email, profile_pic, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Settings</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#1f293a;
    color:#333;
    overflow:hidden; /* ✅ removes both horizontal & vertical scrollbars */
}

header {
    background:#3b82f6;
    padding:15px 40px; /* ✅ restored old design */
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 8px rgba(0,0,0,0.15);
}
header h2 { margin:0;color:#fff;font-weight:600;}
nav { flex:1; display:flex; justify-content:center; gap:20px;}
nav a {
    text-decoration:none;
    color:#fff;
    font-weight:500;
    padding:10px 22px;
    border-radius:12px;
    background:linear-gradient(135deg,#2563eb,#3b82f6);
    box-shadow:0 4px 12px rgba(59,130,246,0.4);
    transition:all 0.3s ease;
}
nav a:hover { background:linear-gradient(135deg,#3b82f6,#2563eb); transform:translateY(-3px);}
nav a.active { background:#1e40af;}
.logout {
    text-decoration:none;
    background:#ef4444;
    color:#fff;
    padding:10px 20px;
    border-radius:12px;
    font-weight:500;
}
.logout:hover { background:#dc2626;}
.container {
    max-width:450px;
    margin:30px auto;
    background:#fff;
    padding:20px;
    border-radius:12px;
    box-shadow:0 8px 25px rgba(0,0,0,0.1);
}
h1 { color:#2563eb; text-align:center; margin-bottom:15px;}
.msg { text-align:center; font-weight:bold; margin-bottom:15px; color:#2563eb; }
.profile-pic { text-align:center; margin-bottom:15px;}
.profile-pic img { width:100px; height:100px; border-radius:50%; object-fit:cover; box-shadow:0 4px 12px rgba(0,0,0,0.2);}
input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-family:inherit;
    width:93%;
}
label {
    font-size:0.9rem;
    margin-bottom:3px;
    color:#444;
}
button {
    padding:10px;
    background:#3b82f6;
    color:#fff;
    font-weight:600;
    border:none;
    border-radius:8px;
    cursor:pointer;
    width:100%;
}
button:hover { background:#2563eb;}
</style>
</head>
<body>
<header>
    <h2>My System</h2>
    <nav>
        <a href="user_home.php">🏠 Home</a>
        <a href="user_products.php">📦 Products</a>
        <a href="cart.php">🛒 Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
        <a href="user_order.php">📄 My Orders</a>
        <a href="user_settings.php" class="active">⚙️ Settings</a>
    </nav>
    <a href="user_logout.php" class="logout">🚪 Logout</a>
</header>

<div class="container">
    <h1>⚙️ User Settings</h1>
    <?php if ($msg) echo "<p class='msg'>$msg</p>"; ?>

    <div class="profile-pic">
        <?php if (!empty($user['profile_pic']) && file_exists($user['profile_pic'])): ?>
            <img src="<?php echo $user['profile_pic']; ?>" alt="Profile Picture">
        <?php else: ?>
            <img src="images/default.png" alt="Default Profile">
        <?php endif; ?>
    </div>

    <form method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:12px;">
        <!-- Two-column row -->
        <div style="display:flex; gap:15px; flex-wrap:wrap;">
            <!-- Left column -->
            <div style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
                <label>Full Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <!-- Right column -->
            <div style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
                <label>Old Password:</label>
                <input type="password" name="old_password">

                <label>New Password:</label>
                <input type="password" name="password">
            </div>
        </div>

       <!-- Profile Picture -->
<div style="text-align:center;">
    <label>Profile Picture:</label><br>
    <input type="file" name="profile_pic" accept="image/*" style="margin-top:5px;">
</div>


        <!-- Save Button -->
        <button type="submit">💾 Save Changes</button>
    </form>
</div>
</body>
</html>
