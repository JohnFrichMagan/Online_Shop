<?php
session_start();
include 'db.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// 🔹 Fetch product info for this order (JOIN with order_items + products)
$products = [];

if ($order_id > 0) {
    $stmtProd = $conn->prepare("
        SELECT p.id AS product_id, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.id = ? AND o.customer_id = ?
    ");
    if ($stmtProd) {
        $stmtProd->bind_param("ii", $order_id, $user_id);
        $stmtProd->execute();
        $resultProd = $stmtProd->get_result();
        while ($row = $resultProd->fetch_assoc()) {
            $products[] = $row; // store multiple products
        }
        $stmtProd->close();
    }
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = intval($_POST['order_id']);
    $message = trim($_POST['message']);
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;

    if (!empty($message)) {
        // Loop insert per product in this order
        foreach ($products as $prod) {
            $product_id = $prod['product_id'];

            $stmt = $conn->prepare("
                INSERT INTO feedback (user_id, product_id, message, rating, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            if ($stmt === false) {
                die("❌ SQL Prepare failed: " . $conn->error);
            }
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
<title>Message Admin</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body { font-family: Poppins, sans-serif; background:#f9fafb; margin:0; padding:0; }
.container { max-width:600px; margin:40px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 15px rgba(0,0,0,0.1); }
h2 { color:#2563eb; margin-bottom:10px; }
h3 { color:#374151; margin-bottom:20px; }
textarea { width:100%; height:120px; padding:10px; border:1px solid #ddd; border-radius:8px; resize:none; font-family:inherit; }
select { width:100%; margin-top:10px; padding:10px; border:1px solid #ddd; border-radius:8px; }
.btn { display:inline-block; margin-top:15px; padding:10px 18px; background:#2563eb; color:#fff; border-radius:8px; border:none; cursor:pointer; }
.btn:hover { background:#1d4ed8; }
.alert { margin-top:10px; padding:10px; border-radius:8px; }
.alert-success { background:#d1fae5; color:#065f46; }
.alert-error { background:#fee2e2; color:#991b1b; }
.back-btn { display:inline-block; margin-top:20px; padding:10px 18px; background:#6b7280; color:#fff; border-radius:8px; text-decoration:none; }
.back-btn:hover { background:#4b5563; }
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; }
.modal-content { background:#fff; padding:20px; border-radius:12px; text-align:center; max-width:400px; width:90%; box-shadow:0 4px 15px rgba(0,0,0,0.2); }
.modal .btn { margin:10px; }
.btn-cancel { background:#6b7280; }
.btn-cancel:hover { background:#4b5563; }
</style>
</head>
<body>
<div class="container">
    <h2>💬 Feedback to Admin about Order #<?php echo $order_id; ?></h2>

    <?php if (!empty($products)): ?>
        <h3>🛒 Products in this order:</h3>
        <ul>
            <?php foreach ($products as $prod): ?>
                <li><?php echo htmlspecialchars($prod['product_name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php elseif(isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="feedbackForm">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <textarea name="message" placeholder="Write your message to the admin..."></textarea>
        <br>
        <label for="rating"><strong>Rate this order (1-5 stars):</strong></label>
        <select name="rating" id="rating">
            <option value="">-- Select Rating --</option>
            <option value="1">⭐</option>
            <option value="2">⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
        </select>
        <br>
        <div style="text-align:center; margin-top:15px;">
            <button type="button" class="btn" onclick="openModal()">Send Feedback</button>
        </div>
    </form>

    <a href="track.php?id=<?php echo $order_id; ?>" class="back-btn">⬅ Back to Order</a>
</div>

<div class="modal" id="confirmModal">
    <div class="modal-content">
        <h3>Confirm Feedback</h3>
        <p>Are you sure you want to send this feedback?</p>
        <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn" onclick="submitForm()">Send</button>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('confirmModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
function submitForm() {
    document.getElementById('feedbackForm').submit();
}
</script>
</body>
</html>
