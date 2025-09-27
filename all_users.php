<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// ✅ Handle Block/Unblock
if (isset($_GET['block'])) {
    $userId = intval($_GET['block']);
    $conn->query("UPDATE users SET status = 'blocked' WHERE id = $userId");
    header("Location: all_users.php");
    exit();
}
if (isset($_GET['unblock'])) {
    $userId = intval($_GET['unblock']);
    $conn->query("UPDATE users SET status = 'active' WHERE id = $userId");
    header("Location: all_users.php");
    exit();
}

// ✅ Handle Delete
if (isset($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $userId");
    header("Location: all_users.php");
    exit();
}

// Pagination setup
$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search setup
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchSQL = $search ? "WHERE name LIKE ? OR email LIKE ?" : "";

// Count total users
$countQuery = "SELECT COUNT(*) as total FROM users $searchSQL";
$stmt = $conn->prepare($countQuery);
if ($search) {
    $searchParam = "%$search%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
}
$stmt->execute();
$countResult = $stmt->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $limit);

// Fetch paginated users
$usersQuery = "SELECT id, name, email, created_at, status 
               FROM users 
               $searchSQL
               ORDER BY created_at DESC 
               LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($usersQuery);
if ($search) {
    $stmt->bind_param("ss", $searchParam, $searchParam);
}
$stmt->execute();
$usersResult = $stmt->get_result();

// ✅ Smart Back Button
$allowed_pages = ['home.php', 'manage.php'];
$back_page = 'manage.php'; // default
if (isset($_SERVER['HTTP_REFERER'])) {
    foreach ($allowed_pages as $page) {
        if (strpos($_SERVER['HTTP_REFERER'], $page) !== false) {
            $back_page = $page;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>All Users</title>
<link rel="icon" href="login.png" type="image/x-icon">
<style>
body {
    font-family: "Poppins", sans-serif;
    background: #f9fafb;
    margin: 0;
    padding: 0;
}
.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
h2 {
    text-align: center;
    color: #1e40af;
    margin-bottom: 20px;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}
table th, table td {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
table th {
    background: #2563eb;
    color: #fff;
}
.pagination {
    text-align: center;
    margin-top: 20px;
}
.pagination a {
    display: inline-block;
    padding: 8px 15px;
    margin: 0 5px;
    border-radius: 8px;
    background: #2563eb;
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    transition: 0.3s;
}
.pagination a:hover {
    background: #1e40af;
}
.pagination a.active {
    background: #1e3a8a;
}
.back-btn {
    display: inline-block;
    margin-bottom: 15px;
    padding: 10px 18px;
    background: #6b7280;
    color: #fff;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
}
.back-btn:hover {
    background: #4b5563;
}
.search-box {
    text-align: center;
    margin-bottom: 15px;
}
.search-box input {
    padding: 10px;
    width: 60%;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 1rem;
}
.search-box button {
    padding: 10px 15px;
    border: none;
    background: #2563eb;
    color: #fff;
    border-radius: 8px;
    font-size: 1rem;
    cursor: pointer;
    margin-left: 8px;
}
.search-box button:hover {
    background: #1e40af;
}
.action-btn {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.9rem;
    text-decoration: none;
    margin: 0 3px;
    display: inline-block;
}
.edit-btn { background: #3b82f6; color: #fff; }
.edit-btn:hover { background: #2563eb; }
.delete-btn { background: #ef4444; color: #fff; }
.delete-btn:hover { background: #dc2626; }
.block-btn { background: #f59e0b; color: #fff; }
.block-btn:hover { background: #d97706; }
.unblock-btn { background: #10b981; color: #fff; }
.unblock-btn:hover { background: #059669; }
</style>
</head>
<body>
<div class="container">
    <a href="<?php echo htmlspecialchars($back_page); ?>" class="back-btn">⬅ Back</a>
    <h2>All Registered Users</h2>

    <!-- Search form -->
    <div class="search-box">
        <form method="get" action="all_users.php">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <!-- Users table -->
    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date Registered</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if ($usersResult && $usersResult->num_rows > 0): ?>
            <?php while($row = $usersResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo date("M d, Y", strtotime($row['created_at'])); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">✏️ Edit</a>
                        <a href="all_users.php?delete=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">🗑️ Delete</a>
                        <?php if ($row['status'] == 'active'): ?>
                            <a href="all_users.php?block=<?php echo $row['id']; ?>" class="action-btn block-btn" onclick="return confirm('Block this user?')">🚫 Block</a>
                        <?php else: ?>
                            <a href="all_users.php?unblock=<?php echo $row['id']; ?>" class="action-btn unblock-btn" onclick="return confirm('Unblock this user?')">✅ Unblock</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No users found</td></tr>
        <?php endif; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>">⬅ Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>">Next ➡</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
