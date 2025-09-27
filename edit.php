<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

$admin_email = $_SESSION['admin_email'];

// Validate user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage.php");
    exit();
}

$userId = intval($_GET['id']);

// Fetch user data
$userQuery = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
$userQuery->bind_param("i", $userId);
$userQuery->execute();
$userResult = $userQuery->get_result();
$user = $userResult->fetch_assoc();

if (!$user) {
    header("Location: manage.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (!empty($name) && !empty($email)) {
        $updateQuery = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $updateQuery->bind_param("ssi", $name, $email, $userId);
        $updateQuery->execute();

        header("Location: manage.php");
        exit();
    } else {
        $error = "All fields are required!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit User</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #1f293a;
    color: #333;
}

header {
    background: #3b82f6;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

header h2 {
    margin: 0;
    color: #fff;
    font-weight: 600;
}

nav {
    flex: 1;
    display: flex;
    justify-content: center;
    gap: 20px;
}

nav a {
    display: inline-block;
    text-decoration: none;
    color: #fff;
    font-weight: 500;
    padding: 10px 22px;
    border-radius: 12px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    box-shadow: 0 4px 12px rgba(59,130,246,0.4);
    transition: all 0.3s ease;
}

nav a:hover {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(37,99,235,0.5);
}

nav a.active {
    background: #1e40af;
    box-shadow: 0 4px 12px rgba(30,64,175,0.6);
}

.logout {
    text-decoration: none;
    background: #ef4444;
    color: #fff;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.logout:hover {
    background: #dc2626;
    transform: scale(1.05);
}

.container {
    max-width: 600px;
    margin: 40px auto;
    padding: 20px;
}

.section-box {
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    text-align: center;
}

.section-box h2 {
    color: #1d4ed8;
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: left;
}

label {
    font-weight: 500;
    color: #444;
}

input[type="text"], input[type="email"] {
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    width: 100%;
    font-size: 1rem;
}

button {
    padding: 12px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-2px);
}

.error {
    color: red;
    margin-bottom: 10px;
}
</style>
</head>
<body>


<div class="container">
    <div class="section-box">
        <h2>Edit User</h2>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <button type="submit">💾 Save Changes</button>
        </form>
    </div>
</div>
</body>
</html>
