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

// Top-selling products
$topProductsQuery = "
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 10
";
$topProductsResult = $conn->query($topProductsQuery);

// Show only 3 latest users (instead of all)
$usersQuery = "SELECT id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 3";
$usersResult = $conn->query($usersQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    overflow-y: hidden;
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #1f293a;
    color: #333;
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
    margin: 0;
    color: #fff;
    font-weight: 600;
    text-align: center;
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
    box-shadow: 0 4px 12px rgba(30,64,175,0.6);
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

.welcome-box {
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    text-align: center;
    margin-bottom: 30px;
}

.welcome-box h1 {
    color: #2575fc;
    margin-bottom: 15px;
}

.welcome-box p {
    font-size: 1.1rem;
    color: #555;
    margin-bottom: 20px;
}

.btn {
    display: inline-block;
    padding: 12px 25px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    border-radius: 10px;
    font-weight: 500;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(59,130,246,0.4);
    transition: all 0.3s ease;
}

.btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(37,99,235,0.5);
}

.row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.section-box {
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    flex: 1;
    min-width: 300px;
    max-height: 400px;
    display: flex;
    flex-direction: column;
}

.section-box h2 {
    color: #1d4ed8;
    margin-bottom: 20px;
    text-align: center;
}

.table-container {
    overflow-y: auto;
    flex: 1;
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

/* Button under user list */
.view-all-btn {
    text-align: center;
    margin-top: 15px;
}
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
        <a href="manage.php" class="<?= basename($_SERVER['PHP_SELF'])=='manage.php'?'active':'' ?>">📋 Manage</a>
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
</header>

<div class="container">

  
    <div class="row">
        <!-- Top-selling products -->
        <div class="section-box">
            <h2>Top-Selling Products</h2>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                    </tr>
                    <?php if ($topProductsResult && $topProductsResult->num_rows > 0): ?>
                        <?php while($row = $topProductsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['total_sold']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2">No data available</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- List of Users -->
        <div class="section-box">
            <h2>Latest Users</h2>
            <div class="table-container">
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Date Registered</th>
                    </tr>
                    <?php if ($usersResult && $usersResult->num_rows > 0): ?>
                        <?php while($row = $usersResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No users found</td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- ✅ View All Users Button -->
            <div class="view-all-btn">
                <a href="all_users.php" class="btn">👥 View All Users</a>
            </div>
        </div>
    </div>

</div>
</body>
</html>
