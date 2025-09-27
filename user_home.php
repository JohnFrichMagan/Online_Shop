<?php
session_start();
include 'db.php'; // include your DB connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Get cart count safely
$cart_count = count($_SESSION['cart']);

// User info
$user_email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];

// Fetch top-selling products
$topProductsResult = $conn->query("
    SELECT p.name, SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY total_sold DESC
    LIMIT 5
");

// Fetch recent orders
$recentOrders = $conn->query("
    SELECT id, total, status, created_at 
    FROM orders 
    WHERE customer_id = $user_id
    ORDER BY created_at DESC
    LIMIT 5
");

// Fetch recommended products (example: top 5 cheapest not yet bought by user)
$recommended = $conn->query("
    SELECT id, name, price 
    FROM products 
    ORDER BY price ASC 
    LIMIT 5
");

// Account overview
$accountOverviewQuery = $conn->query("
    SELECT 
        IFNULL(SUM(total),0) AS total_spent,
        COUNT(id) AS total_orders,
        (SELECT created_at FROM users WHERE id = $user_id) AS date_joined
    FROM orders WHERE customer_id = $user_id
");
$accountOverview = $accountOverviewQuery->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: #1f293a;
    color: #333;
    overflow-y: hidden; /* 🚀 hides the right vertical scrollbar */
}

header { background:#3b82f6; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.15);}
header h2 { margin:0;color:#fff;font-weight:600;text-align:center;}
nav { flex:1; display:flex; justify-content:center; gap:20px;}
nav a { text-decoration:none; color:#fff; font-weight:500; padding:10px 22px; border-radius:12px; background:linear-gradient(135deg,#2563eb,#3b82f6); box-shadow:0 4px 12px rgba(59,130,246,0.4); transition:all 0.3s ease;}
nav a:hover { background:linear-gradient(135deg,#3b82f6,#2563eb); transform:translateY(-3px); box-shadow:0 6px 16px rgba(37,99,235,0.5);}
nav a.active { background:#1e40af; box-shadow:0 4px 12px rgba(30,64,175,0.6);}
.logout { text-decoration:none; background:#ef4444; color:#fff; padding:10px 20px; border-radius:12px; font-weight:500; transition:all 0.3s ease;}
.logout:hover { background:#dc2626; transform:scale(1.05);}
.container { max-width: 1200px; margin: 40px auto; padding:20px;}
.welcome-box { background:#fff; padding:30px; border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1); text-align:center; margin-bottom:30px;}
.welcome-box h1 { color:#2575fc; margin-bottom:15px;}
.welcome-box p { font-size:1.1rem; color:#555; margin-bottom:20px;}
.stats { display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;}
.stat-box { flex:1; min-width:200px; background:#fff; padding:20px; border-radius:15px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center;}
.stat-box h3 { margin:0; color:#1e40af; font-size:1.6rem;}
.stat-box p { margin-top:8px; font-size:0.95rem; color:#666;}
.row { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px;}
.section-box { background:#fff; padding:20px; border-radius:15px; box-shadow:0 8px 25px rgba(0,0,0,0.1); flex:1; min-width:300px; display:flex; flex-direction:column;}
.section-box h2 { color:#1d4ed8; margin-bottom:20px; text-align:center;}
.table-container { flex:1; } /* removed overflow-y and max-height */
table { width:100%; border-collapse:collapse;}
table th, table td { padding:10px; border-bottom:1px solid #ddd; text-align:center;}
table th { background:#3b82f6; color:#fff; position:sticky; top:0;}
table tr:hover { background:#f1f5f9;}
</style>
</head>
<body>
<header>
    <h2>My System</h2>
    <nav>
        <a href="user_home.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_home.php'?'active':'' ?>">🏠 Home</a>
        <a href="user_products.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_products.php'?'active':'' ?>">📦 Products</a>
        <a href="cart.php" class="cart-btn <?= basename($_SERVER['PHP_SELF'])=='cart.php'?'active':'' ?>">🛒 Cart (<?php echo $cart_count; ?>)</a>
        <a href="user_order.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_order.php'?'active':'' ?>">📄 My Order</a>
        <a href="user_settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="user_logout.php" class="logout">🚪 Logout</a>
</header>

<div class="container">
    <!-- Welcome -->
    <

    <!-- Account Overview -->
    <div class="stats">
        <div class="stat-box">
            <h3>₱<?php echo number_format($accountOverview['total_spent'],2); ?></h3>
            <p>Total Spent</p>
        </div>
        <div class="stat-box">
            <h3><?php echo $accountOverview['total_orders']; ?></h3>
            <p>Total Orders</p>
        </div>
        <div class="stat-box">
            <h3><?php echo date("M d, Y", strtotime($accountOverview['date_joined'])); ?></h3>
            <p>Joined</p>
        </div>
    </div>

    <!-- Row 1 -->
    <div class="row">
        <!-- Top-Selling Products -->
        <div class="section-box">
            <h2>🔥 Top-Selling Products</h2>
            <div class="table-container">
                <table>
                    <tr><th>Product Name</th><th>Sold</th></tr>
                    <?php if ($topProductsResult && $topProductsResult->num_rows > 0): ?>
                        <?php while($row = $topProductsResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['total_sold']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="2">No data</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="section-box">
            <h2>📄 Recent Orders</h2>
            <div class="table-container">
                <table>
                    <tr><th>ID</th><th>Total</th><th>Status</th><th>Date</th></tr>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <?php while($row = $recentOrders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>₱<?php echo number_format($row['total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo date("M d", strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4">No recent orders</td></tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

   

    </div>
</div>
</body>
</html>
