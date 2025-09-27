<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$admin_email = $_SESSION['admin_email'];

// Handle delete user
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $userId");
    header("Location: manage.php");
    exit();
}

// Fetch only the 3 newest users
$usersQuery = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 3";
$usersResult = $conn->query($usersQuery);


// Fetch users who sent feedback
$feedbackQuery = "
    SELECT DISTINCT u.id, u.name, u.email 
    FROM feedback f
    JOIN users u ON f.user_id = u.id
    ORDER BY f.created_at DESC
";
$feedbackResult = $conn->query($feedbackQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management</title>
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

/* Header */
header {
    background: #3b82f6;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

header h2 {
    color: #fff;
    font-weight: 600;
}

/* Navigation */
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
    box-shadow: 0 6px 16px rgba(30,64,175,0.5);
}

/* Logout Button */
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
    max-width: 1200px;
    margin: 40px auto;
    padding: 20px;
}

/* Toggle buttons */
.toggle-buttons {
    text-align: center;
    margin-bottom: 25px;
}
.toggle-buttons button {
    padding: 12px 25px;
    margin: 0 10px;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    background: #3b82f6;
    color: #fff;
    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
    transition: all 0.3s ease;
}
.toggle-buttons button:hover {
    background: #2563eb;
    transform: translateY(-2px);
}
.toggle-buttons button.active {
    background: #1e40af;
    box-shadow: 0 4px 14px rgba(30,64,175,0.5);
}

/* Section boxes */
.section-box {
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    display: none; /* hidden by default */
}

.section-box.active {
    display: block; /* show only when active */
}

.section-box h2 {
    color: #1d4ed8;
    margin-bottom: 20px;
    text-align: center;
}

.table-container {
    overflow-y: auto;
    max-height: 500px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

table th {
    background: #3b82f6;
    color: #fff;
    position: sticky;
    top: 0;
}

table tr:hover {
    background: #f1f5f9;
}

.action-btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.9rem;
    text-decoration: none;
    margin: 0 3px;
    display: inline-block;
}

.edit-btn {
    background: #3b82f6;
    color: #fff;
}
.edit-btn:hover { background: #2563eb; }

.delete-btn {
    background: #ef4444;
    color: #fff;
}
.delete-btn:hover { background: #dc2626; }

.view-btn {
    background: #10b981;
    color: #fff;
}
.view-btn:hover { background: #059669; }
</style>
</head>
<body>
<header>
    <h2>Admin Dashboard</h2>
    <nav>
        <a href="home.php">🏠 Dashboard</a>
        <a href="products.php">📦 Products</a>
        <a href="order.php">🛒 Orders</a>
        <a href="report.php">📊 Reports</a>
        <a href="manage.php" class="active">📋 Manage</a>
        <a href="settings.php">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
</header>

<div class="container">
    <!-- Toggle buttons -->
    <div class="toggle-buttons">
        <button onclick="showSection('users')" id="btn-users" class="active">👤 Manage Users</button>
        <button onclick="showSection('feedback')" id="btn-feedback">💬 User Feedbacks</button>
    </div>

        <!-- Manage Users -->
    <div class="section-box active" id="users-section">
        <h2>Manage Users</h2>
        <div class="table-container">
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Date Registered</th>
                    <th>Actions</th>
                </tr>
                <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                    <?php while($row = $usersResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                                <a href="manage.php?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No users found</td></tr>
                <?php endif; ?>
            </table>
        </div>



        <!-- ✅ View All Users Button -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="all_users.php" 
               style="display:inline-block; padding:12px 25px; background:#2563eb; color:#fff; border-radius:10px; font-weight:500; text-decoration:none; transition:0.3s;">
               👥 View All Users
            </a>
        </div>
    </div>


    <!-- Feedback Section -->
    <div class="section-box" id="feedback-section">
        <h2>User Feedbacks</h2>
        <div class="table-container">
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                <?php if ($feedbackResult && $feedbackResult->num_rows > 0): ?>
                    <?php while($row = $feedbackResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <a href="view_feedback.php?user_id=<?php echo $row['id']; ?>" class="action-btn view-btn">👀 View Messages</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No feedbacks found</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
<script>
function showSection(section) {
    // Hide all sections
    document.getElementById('users-section').classList.remove('active');
    document.getElementById('feedback-section').classList.remove('active');
    document.getElementById('btn-users').classList.remove('active');
    document.getElementById('btn-feedback').classList.remove('active');

    // Show selected
    if (section === 'users') {
        document.getElementById('users-section').classList.add('active');
        document.getElementById('btn-users').classList.add('active');
    } else {
        document.getElementById('feedback-section').classList.add('active');
        document.getElementById('btn-feedback').classList.add('active');
    }
}
</script>
</body>
</html>


