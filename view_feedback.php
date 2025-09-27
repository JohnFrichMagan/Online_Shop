<?php
session_start();
include 'db.php';

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Validate user_id
if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    die("❌ Invalid user ID.");
}
$user_id = (int)$_GET['user_id'];

// ✅ Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $stmtDel = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
    if ($stmtDel) {
        $stmtDel->bind_param("ii", $feedback_id, $user_id);
        if ($stmtDel->execute()) {
            $msg = "✅ Feedback removed successfully.";
        } else {
            $msg = "❌ Failed to remove feedback: " . $stmtDel->error;
        }
        $stmtDel->close();
    }
}

// ✅ Fetch user info
$sqlUser = "SELECT id, name, email FROM users WHERE id = ?";
$stmtUser = $conn->prepare($sqlUser);
if (!$stmtUser) {
    die("❌ SQL Prepare failed (User): " . $conn->error);
}
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$user = $userResult->fetch_assoc();
$stmtUser->close();

if (!$user) {
    die("❌ User not found.");
}

// ✅ Fetch feedback with product name
$sqlFeedback = "
    SELECT 
        f.id,
        f.message,
        f.rating,
        f.created_at,
        p.name AS product_name
    FROM feedback f
    LEFT JOIN products p ON p.id = f.product_id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
";
$stmtFb = $conn->prepare($sqlFeedback);
if (!$stmtFb) {
    die("❌ SQL Prepare failed (Feedback): " . $conn->error);
}
$stmtFb->bind_param("i", $user_id);
$stmtFb->execute();
$fbResult = $stmtFb->get_result();
$feedbacks = $fbResult->fetch_all(MYSQLI_ASSOC);
$stmtFb->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Feedback from <?php echo htmlspecialchars($user['name']); ?></title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body { 
    font-family: Poppins, sans-serif; 
    background:#f9fafb; 
    margin:0; 
    padding:20px; 
    color:#1f2937;
}
.container { 
    max-width:1200px; 
    margin:0 auto; 
    background:#fff; 
    padding:20px; 
    border-radius:12px; 
    box-shadow:0 4px 12px rgba(0,0,0,0.08); 
}
h2 { 
    color:#2563eb; 
    margin-bottom:20px; 
    font-weight:600; 
}
.message-box { 
    border:1px solid #e5e7eb; 
    padding:18px; 
    border-radius:12px; 
    background:#ffffff; 
    box-shadow:0 2px 6px rgba(0,0,0,0.05); 
    transition: all 0.2s ease-in-out;
    position:relative; 
}
.message-box:hover {
    transform: translateY(-3px);
    box-shadow:0 6px 14px rgba(0,0,0,0.08);
}
.message-box p { margin:6px 0; font-size:14px; line-height:1.5; }
.rating { color:#fbbf24; font-size:1.1rem; }
.back-btn { 
    display:inline-block; 
    margin-top:20px; 
    padding:10px 20px; 
    background:#2563eb; 
    color:#fff; 
    border-radius:8px; 
    text-decoration:none; 
    font-weight:500;
}
.back-btn:hover { background:#1d4ed8; }
.remove-btn { 
    position:absolute; 
    top:12px; 
    right:12px; 
    background:#ef4444; 
    color:#fff; 
    padding:6px 10px; 
    border:none; 
    border-radius:6px; 
    cursor:pointer; 
    font-size:13px;
}
.remove-btn:hover { background:#dc2626; }

/* Alerts */
.alert { margin-bottom:15px; padding:10px; border-radius:8px; font-size:14px; }
.alert-success { background:#d1fae5; color:#065f46; }
.alert-error { background:#fee2e2; color:#991b1b; }

/* Grid layout for feedbacks */
.feedback-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

/* Modal */
.modal { 
    display:none; 
    position:fixed; 
    top:0; left:0; 
    width:100%; height:100%; 
    background:rgba(0,0,0,0.5); 
    justify-content:center; 
    align-items:center; 
}
.modal-content { 
    background:#fff; 
    padding:25px; 
    border-radius:12px; 
    text-align:center; 
    max-width:380px; 
    width:90%; 
    box-shadow:0 4px 12px rgba(0,0,0,0.2); 
}
.modal-content h3 { margin-bottom:12px; color:#111827; font-weight:600; }
.modal .btn { 
    margin:10px; 
    padding:8px 16px; 
    border:none; 
    border-radius:8px; 
    cursor:pointer; 
    font-weight:500;
}
.btn-cancel { background:#6b7280; color:#fff; }
.btn-cancel:hover { background:#4b5563; }
.btn-confirm { background:#ef4444; color:#fff; }
.btn-confirm:hover { background:#dc2626; }
</style>
</head>
<body>
<div class="container">
    <h2>Feedback from <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</h2>

    <?php if (isset($msg)): ?>
        <div class="alert <?php echo strpos($msg,'✅')!==false ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- Feedback containers -->
    <?php if (!empty($feedbacks)): ?>
        <div class="feedback-grid">
            <?php foreach ($feedbacks as $fb): ?>
                <div class="message-box">
                    <form method="POST" style="display:inline;" id="deleteForm<?php echo $fb['id']; ?>">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <input type="hidden" name="delete_feedback" value="1">
                        <button type="button" class="remove-btn" onclick="openModal(<?php echo $fb['id']; ?>)">🗑 Remove</button>
                    </form>
                    <p><strong>📦 Product:</strong> <?php echo htmlspecialchars($fb['product_name'] ?? 'N/A'); ?></p>
                    <p><strong>💬 Message:</strong><br><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                    <p><strong>⭐ Rating:</strong> 
                        <span class="rating">
                            <?php 
                            $stars = (int)$fb['rating'];
                            echo str_repeat("⭐", $stars) . str_repeat("☆", 5 - $stars);
                            ?>
                        </span>
                    </p>
                    <p><em>📅 <?php echo date("M d, Y h:i A", strtotime($fb['created_at'])); ?></em></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No feedback messages from this user.</p>
    <?php endif; ?>

    <a href="manage.php" class="back-btn">⬅ Back to Manage</a>
</div>

<!-- Modal -->
<div class="modal" id="confirmModal">
    <div class="modal-content">
        <h3>Confirm Remove</h3>
        <p>Are you sure you want to remove this feedback?</p>
        <button type="button" class="btn btn-cancel" onclick="closeModal()">Cancel</button>
        <button type="button" class="btn btn-confirm" id="confirmDeleteBtn">Remove</button>
    </div>
</div>

<script>
let currentFeedbackId = null;

function openModal(feedbackId) {
    currentFeedbackId = feedbackId;
    document.getElementById('confirmModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
    currentFeedbackId = null;
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (currentFeedbackId) {
        document.getElementById('deleteForm' + currentFeedbackId).submit();
    }
});
</script>
</body>
</html>
