<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// --- UPDATE ORDER STATUS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $message = "✅ Order #$order_id status updated to $status.";
        } else {
            $message = "❌ Failed to update order.";
        }
        $stmt->close();
    }
}

// --- Filters ---
$filter_status = isset($_GET['status']) ? $_GET['status'] : "All";
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : "newest";

$limit = 3; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// WHERE condition
$where = "WHERE 1=1";
$params = [];
$types = "";

if ($filter_status !== "All") {
    $where .= " AND status=?";
    $params[] = $filter_status;
    $types .= "s";
}

// Sorting
$orderBy = ($sort_order === "oldest") ? "ASC" : "DESC";

// Count orders
$sqlCount = "SELECT COUNT(*) AS total FROM orders $where";
$stmtCount = $conn->prepare($sqlCount);
if (!empty($params)) $stmtCount->bind_param($types, ...$params);
$stmtCount->execute();
$totalOrders = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $limit);
$stmtCount->close();

// Fetch orders
$sql = "SELECT * FROM orders $where ORDER BY created_at $orderBy LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types .= "ii";
    $params[] = $limit;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$orders = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Orders | Admin</title>
<style>
body {
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#1f293a;
    color:#333;
    overflow-x:hidden;
    overflow-y:hidden; /* remove scrollbar */
}
header {
    background:#3b82f6;
    padding:15px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 8px rgba(0,0,0,0.15);
}
header h2 { margin:0; color:#fff; font-weight:600; }
nav { flex:1; display:flex; justify-content:center; gap:20px; }
nav a {
    text-decoration:none; color:#fff; font-weight:500;
    padding:10px 22px; border-radius:12px;
    background:linear-gradient(135deg,#2563eb,#3b82f6);
    transition:all .3s ease;
}
nav a:hover { background:linear-gradient(135deg,#3b82f6,#2563eb); transform:translateY(-2px); }
nav a.active { background:#1e40af; }
.logout {
    text-decoration:none; background:#ef4444; color:#fff;
    padding:10px 20px; border-radius:12px; font-weight:500;
}
.logout:hover { background:#dc2626; }

.page-title { text-align:center; margin:20px 0; font-size:1.8rem; color:#fff; }

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
    background:linear-gradient(135deg,#2563eb,#3b82f6);
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:500;
}
.filter-bar button:hover { background:#1e40af; }

/* Container */
.container {
    max-width:1100px; /* a bit smaller */
    margin:0 auto 80px; 
    display:grid;
    grid-template-columns:repeat(3, 1fr);
    gap:16px;
}
.message { text-align:center; margin-bottom:15px; color:#fff; font-weight:500; }
.order-card {
    background:#fff;
    padding:12px;
    border-radius:10px;
    box-shadow:0 4px 8px rgba(0,0,0,0.1);
    display:flex;
    flex-direction:column;
    font-size:0.85rem;
    min-height:260px;
}
.order-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
.order-header h3 { margin:0; color:#1f2937; font-size:0.95rem; }
.badge { padding:4px 8px; border-radius:8px; font-size:0.7rem; font-weight:600; }
.badge.Pending { background:#fbbf24; color:#000; }
.badge.Processing { background:#3b82f6; color:#fff; }
.badge.Shipped { background:#6366f1; color:#fff; }
.badge.Delivered { background:#10b981; color:#fff; }
.badge.Cancelled { background:#ef4444; color:#fff; }

.order-details { margin-bottom:8px; line-height:1.3; }
.items-table { width:100%; border-collapse:collapse; margin:8px 0; font-size:0.8rem; }
.items-table th, .items-table td { border:1px solid #ddd; padding:5px; text-align:center; }
.items-table th { background:#f3f4f6; }

/* Update Form */
.update-form { text-align:center; margin-top:auto; }
.update-form select, .update-form button {
    padding:5px 8px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:0.8rem;
}
.update-form button {
    background:#2563eb;
    color:#fff;
    border:none;
    cursor:pointer;
}
.update-form button:hover { background:#1e40af; }

/* Pagination Bottom */
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
    transition:.3s;
}
.pagination a:hover { background:#2563eb; }
.pagination a.active { background:#1e40af; }
</style>
</head>
<body>

<header>
    <h2>Admin Dashboard</h2>
    <nav>
        <a href="home.php">🏠 Dashboard</a>
        <a href="products.php">📦 Products</a>
        <a href="order.php" class="active">🛒 Orders</a>
        <a href="report.php">📊 Reports</a>
        <a href="manage.php">📋 Manage</a>
        <a href="settings.php">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
</header>

<h2 class="page-title">Manage Orders</h2>

<!-- Filter + Sort -->
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

<?php if (isset($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="container">
<?php while ($order = $orders->fetch_assoc()): ?>
    <div class="order-card">
        <div class="order-header">
            <h3>Order #<?= $order['id'] ?></h3>
            <span class="badge <?= $order['status'] ?>"><?= htmlspecialchars($order['status']) ?></span>
        </div>
        <div class="order-details">
            <p><strong>Name:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
            <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
            <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            <p><strong>Total:</strong> $<?= number_format($order['total'],2) ?></p>
            <p><strong>Date:</strong> <?= $order['created_at'] ?></p>
        </div>

        <?php
        $stmt_items = $conn->prepare("SELECT oi.*, p.name FROM order_items oi 
                                      JOIN products p ON oi.product_id=p.id 
                                      WHERE oi.order_id=?");
        $stmt_items->bind_param("i", $order['id']);
        $stmt_items->execute();
        $items = $stmt_items->get_result();
        ?>
        <table class="items-table">
            <tr><th>Product</th><th>Qty</th><th>Price</th></tr>
            <?php while($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['price'],2) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <?php $stmt_items->close(); ?>

        <form method="post" class="update-form">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <select name="status">
                <option <?= $order['status']=="Pending"?"selected":"" ?>>Pending</option>
                <option <?= $order['status']=="Processing"?"selected":"" ?>>Processing</option>
                <option <?= $order['status']=="Shipped"?"selected":"" ?>>Shipped</option>
                <option <?= $order['status']=="Delivered"?"selected":"" ?>>Delivered</option>
                <option <?= $order['status']=="Cancelled"?"selected":"" ?>>Cancelled</option>
            </select>
            <button type="submit">Update</button>
        </form>
    </div>
<?php endwhile; ?>
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
