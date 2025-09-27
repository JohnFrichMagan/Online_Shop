<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// --- Filters ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : "All";
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : "newest";

// Pagination setup
$limit = 3; // ✅ 3 orders per page (1 row of 3 containers)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// WHERE condition
$where = "WHERE customer_id=?";
$params = [$user_id];
$types = "i";

if ($filter_status !== "All") {
    $where .= " AND status=?";
    $params[] = $filter_status;
    $types .= "s";
}

// Sorting
$orderBy = ($sort_order === "oldest") ? "ASC" : "DESC";

// Count total orders
$sqlCount = "SELECT COUNT(*) AS total FROM orders $where";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalOrders = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);
$stmtCount->close();

// Fetch orders
$sql = "SELECT * FROM orders $where ORDER BY created_at $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();

// Fetch items
$orderItems = [];
$stmt_items = $conn->prepare("SELECT oi.order_id, oi.quantity, oi.price, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id=p.id 
    WHERE oi.order_id IN (SELECT id FROM orders WHERE customer_id=?)");
$stmt_items->bind_param("i", $user_id);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
while ($row = $res_items->fetch_assoc()) {
    $orderItems[$row['order_id']][] = $row;
}
$stmt_items->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Orders</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#1f293a;
    color:#333;
    overflow-x:hidden;
}
header {
    background:#3b82f6;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 8px rgba(0,0,0,0.15);
}
header h2 { margin:0;color:#fff;font-weight:600;text-align:center; }
nav { flex:1; display:flex; justify-content:center; gap:15px; }
nav a {
    text-decoration:none;
    color:#fff;
    font-weight:500;
    padding:10px 20px;
    border-radius:10px;
    transition:all 0.3s ease;
    background:rgba(255,255,255,0.1);
}
nav a:hover {
    background:#2563eb;
    transform:translateY(-2px);
}
nav a.active { background:#1e40af; font-weight:600; }
.logout {
    text-decoration:none;
    background:#ef4444;
    color:#fff;
    padding:10px 18px;
    border-radius:10px;
    font-weight:500;
}
.logout:hover { background:#dc2626; transform:translateY(-2px); }

/* Title */
.page-title { text-align:center; margin:20px 0; font-size:1.6rem; color:#fff; }

/* Filter Bar */
.filter-bar {
    max-width:1200px;
    margin:0 auto 20px;
    display:flex;
    justify-content:center;
    gap:10px;
}
.filter-bar select, .filter-bar button {
    padding:8px 12px;
    border-radius:8px;
    border:1px solid #ccc;
    font-size:0.9rem;
}
.filter-bar button {
    background:#2563eb;
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:500;
}
.filter-bar button:hover { background:#1e40af; }

/* Orders grid */
.container {
    max-width:1100px;
    margin:0 auto 100px; /* extra bottom space for fixed pagination */
    display:grid;
    grid-template-columns:repeat(3,1fr); /* 3 per row */
    gap:16px;
}
.order-card {
    background:#fff;
    padding:15px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
    display:flex;
    flex-direction:column;
    font-size:0.85rem;
}
.order-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.order-header h3 { margin:0; color:#1e40af; font-size:0.95rem; }
.badge { padding:4px 8px; border-radius:8px; font-size:0.75rem; font-weight:600; }
.badge.pending { background:#fbbf24; color:#000; }
.badge.processing { background:#3b82f6; color:#fff; }
.badge.shipped { background:#06b6d4; color:#fff; }
.badge.delivered { background:#10b981; color:#fff; }
.badge.cancelled { background:#ef4444; color:#fff; }

.order-info { margin-bottom:8px; line-height:1.4; }
.items-table { width:100%; border-collapse:collapse; margin:8px 0; font-size:0.8rem; }
.items-table th, .items-table td { border:1px solid #ddd; padding:5px; text-align:center; }
.items-table th { background:#f3f4f6; }

/* Track button */
.track-btn {
    background:#2563eb;
    color:#fff;
    padding:6px 12px;
    border-radius:8px;
    text-decoration:none;
    font-size:0.85rem;
    text-align:center;
    margin-top:auto;
}
.track-btn:hover { background:#1e40af; }

/* Pagination */
.pagination {
    position:fixed;
    bottom:0;
    left:0;
    width:100%;
    text-align:center;
    background:#1f293a;
    padding:10px 0;
    z-index:999;
}
.pagination a {
    display:inline-block;
    margin:0 4px;
    padding:6px 12px;
    background:#3b82f6;
    color:#fff;
    text-decoration:none;
    border-radius:6px;
    font-size:0.85rem;
}
.pagination a:hover { background:#2563eb; }
.pagination a.active { background:#1e40af; }
</style>
</head>
<body>
<header>
    <h2>My System</h2>
    <nav>
        <a href="user_home.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_home.php'?'active':'' ?>">🏠 Home</a>
        <a href="user_products.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_products.php'?'active':'' ?>">📦 Products</a>
        <a href="cart.php" class="cart-btn <?= basename($_SERVER['PHP_SELF'])=='cart.php'?'active':'' ?>">🛒 Cart (<?= $cart_count ?>)</a>
        <a href="user_order.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_order.php'?'active':'' ?>">📄 My Order</a>
        <a href="user_settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='user_settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="user_logout.php" class="logout">🚪 Logout</a>
</header>

<h2 class="page-title">My Orders</h2>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="get">
        <select name="status">
            <option value="All" <?= $filter_status=="All"?"selected":"" ?>>All</option>
            <option value="Pending" <?= $filter_status=="Pending"?"selected":"" ?>>Pending</option>
            <option value="Processing" <?= $filter_status=="Processing"?"selected":"" ?>>Processing</option>
            <option value="Shipped" <?= $filter_status=="Shipped"?"selected":"" ?>>Shipped</option>
            <option value="Delivered" <?= $filter_status=="Delivered"?"selected":"" ?>>Delivered</option>
            <option value="Cancelled" <?= $filter_status=="Cancelled"?"selected":"" ?>>Cancelled</option>
        </select>
        <select name="sort">
            <option value="newest" <?= $sort_order=="newest"?"selected":"" ?>>Newest First</option>
            <option value="oldest" <?= $sort_order=="oldest"?"selected":"" ?>>Oldest First</option>
        </select>
        <button type="submit">Apply</button>
    </form>
</div>

<div class="container">
<?php if ($orders && $orders->num_rows > 0): ?>
    <?php while($order = $orders->fetch_assoc()): ?>
        <div class="order-card">
            <div class="order-header">
                <h3>Order #<?= $order['id'] ?></h3>
                <span class="badge <?= strtolower($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span>
            </div>
            <div class="order-info">
                <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
                <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
                <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile']) ?></p>
                <p><strong>Date:</strong> <?= date("M d, Y h:i A", strtotime($order['created_at'])) ?></p>
            </div>
            <table class="items-table">
                <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
                <?php if (!empty($orderItems[$order['id']])): ?>
                    <?php foreach ($orderItems[$order['id']] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td>$<?= number_format($item['price'],2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No items found</td></tr>
                <?php endif; ?>
            </table>
            <a href="track.php?id=<?= $order['id'] ?>" class="track-btn">Track Order</a>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="color:white;text-align:center;">No orders found</p>
<?php endif; ?>
</div>

<!-- Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>&sort=<?= $sort_order ?>">⬅ Prev</a>
    <?php endif; ?>
    <?php for ($i=1;$i<=$totalPages;$i++): ?>
        <a href="?page=<?= $i ?>&status=<?= $filter_status ?>&sort=<?= $sort_order ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>&sort=<?= $sort_order ?>">Next ➡</a>
    <?php endif; ?>
</div>
</body>
</html>
