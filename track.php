<?php
session_start();
include 'db.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Validate order_id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid order ID.");
}
$order_id = (int)$_GET['id'];

// ✅ Fetch order details
$sql = "
    SELECT o.id, o.total, o.payment_method, o.status, o.created_at, o.mobile, u.email
    FROM orders o
    JOIN users u ON o.customer_id = u.id
    WHERE o.id = ? AND o.customer_id = ?
";
$stmt = $conn->prepare($sql);
if (!$stmt) die("❌ SQL Prepare failed: " . $conn->error);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) die("❌ Order not found or you don’t have access.");

// ✅ Fetch order items
$sql_items = "
    SELECT p.id AS product_id, p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
";
$stmt_items = $conn->prepare($sql_items);
if (!$stmt_items) die("❌ SQL Prepare failed (items): " . $conn->error);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$res_items = $stmt_items->get_result();
$order_items = $res_items->fetch_all(MYSQLI_ASSOC);
$stmt_items->close();

// ✅ Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;

    if (!empty($message)) {
        foreach ($order_items as $prod) {
            $product_id = $prod['product_id'];
            $stmt = $conn->prepare("
                INSERT INTO feedback (user_id, product_id, message, rating, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            if ($stmt === false) die("❌ SQL Prepare failed: " . $conn->error);
            $stmt->bind_param("iisi", $user_id, $product_id, $message, $rating);
            $stmt->execute();
            $stmt->close();
        }
        $success = "✅ Your feedback has been sent to the admin.";
    } else {
        $error = "⚠️ Message cannot be empty.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Track Order #<?php echo $order['id']; ?></title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    margin:0;
    font-family:"Poppins",sans-serif;
    background:#1f293a; /* dark background like orders page */
    color:#333;
}
.container {
    max-width:1000px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:15px;
    box-shadow:0 8px 25px rgba(0,0,0,0.15);
}
h2 { color:#1d4ed8; margin-bottom:20px; }
h3 { color:#374151; margin-top:25px; margin-bottom:10px; }
.order-info { margin-bottom:20px; font-size:1rem; }
.order-info p { margin:5px 0; }
.badge { padding:6px 12px; border-radius:8px; font-weight:500; color:#fff; }
.badge-pending { background:#facc15; color:#000; }
.badge-processing { background:#3b82f6; }
.badge-shipped { background:#06b6d4; }
.badge-delivered { background:#22c55e; }
.badge-cancelled { background:#ef4444; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
table th, table td { border:1px solid #ddd; padding:10px; text-align:center; }
table th { background:#2563eb; color:#fff; }
.back-btn, .msg-btn {
    display:inline-block;
    padding:10px 18px;
    color:#fff;
    border-radius:10px;
    text-decoration:none;
    cursor:pointer;
    border:none;
    font-weight:500;
}
.back-btn { background:#2563eb; }
.back-btn:hover { background:#1e40af; }
.msg-btn { background:#10b981; }
.msg-btn:hover { background:#059669; }

/* Modal */
.modal {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center; align-items:center;
    z-index:1000;
}
.modal-content {
    background:#fff;
    padding:25px;
    border-radius:15px;
    width:90%; max-width:500px;
    box-shadow:0 8px 25px rgba(0,0,0,0.2);
    position:relative;
}
.close {
    position:absolute; top:10px; right:15px;
    font-size:22px; cursor:pointer; color:#555;
}

/* Inputs */
textarea, select {
    width:100%; padding:12px;
    margin-top:10px;
    border:1px solid #ddd;
    border-radius:10px;
    font-family:inherit;
    font-size:14px;
    box-sizing:border-box;
}
textarea { resize:none; height:120px; }

/* Buttons */
.btn-submit { background:#2563eb; margin-top:15px; }
.btn-submit:hover { background:#1d4ed8; }
.btn-cancel { background:#6b7280; }
.btn-cancel:hover { background:#4b5563; }

.alert { margin-top:10px; padding:10px; border-radius:8px; }
.alert-success { background:#d1fae5; color:#065f46; }
.alert-error { background:#fee2e2; color:#991b1b; }
</style>
</head>
<body>
<div class="container">
    <h2>Track Order #<?php echo $order['id']; ?></h2>
    <div class="order-info">
        <p><strong>Total:</strong> $<?php echo number_format($order['total'],2); ?></p>
        <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Status:</strong> 
            <?php 
            $status = strtolower($order['status']);
            echo "<span class='badge badge-$status'>" . htmlspecialchars($order['status']) . "</span>";
            ?>
        </p>
        <p><strong>Placed On:</strong> <?php echo date("M d, Y h:i A", strtotime($order['created_at'])); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
        <p><strong>Mobile:</strong> <?php echo htmlspecialchars($order['mobile']); ?></p>
    </div>

    <h3>Order Items</h3>
    <table>
        <tr><th>Product</th><th>Quantity</th><th>Price</th></tr>
        <?php if (!empty($order_items)): ?>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'],2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="3">No items found</td></tr>
        <?php endif; ?>
    </table>

    <div style="display:flex; justify-content:space-between; margin-top:20px;">
        <a href="user_order.php" class="back-btn">⬅ Back to Orders</a>
        <button class="msg-btn" onclick="openModal()">💬 Message Admin</button>
    </div>
</div>

<!-- ✅ Message Admin Modal -->
<div class="modal" id="msgModal">
  <div class="modal-content">
    <span class="close" onclick="closeModal()">&times;</span>
    <h3>💬 Feedback about Order #<?php echo $order_id; ?></h3>
    <?php if (!empty($order_items)): ?>
      <p><strong>Products:</strong></p>
      <ul>
        <?php foreach ($order_items as $prod): ?>
          <li><?php echo htmlspecialchars($prod['name']); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="feedbackForm">
        <textarea name="message" placeholder="Write your feedback..." required></textarea>
        <label><strong>Rate this order:</strong></label>
        <select name="rating">
            <option value="">-- Select Rating --</option>
            <option value="1">⭐</option>
            <option value="2">⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
        </select>
        <div style="text-align:center; margin-top:15px;">
            <button type="button" class="back-btn btn-submit" onclick="openConfirm()">Send Feedback</button>
        </div>
    </form>
  </div>
</div>

<!-- ✅ Confirmation Modal -->
<div class="modal" id="confirmModal">
  <div class="modal-content" style="max-width:400px; text-align:center;">
    <h3>Confirm Feedback</h3>
    <p>Are you sure you want to send this feedback?</p>
    <button type="button" class="btn-cancel back-btn" onclick="closeConfirm()">Cancel</button>
    <button type="button" class="back-btn btn-submit" onclick="submitForm()">Send</button>
  </div>
</div>

<script>
function openModal(){ document.getElementById('msgModal').style.display='flex'; }
function closeModal(){ document.getElementById('msgModal').style.display='none'; }
function openConfirm(){ document.getElementById('confirmModal').style.display='flex'; }
function closeConfirm(){ document.getElementById('confirmModal').style.display='none'; }
function submitForm(){ document.getElementById('feedbackForm').submit(); }
</script>
</body>
</html>
