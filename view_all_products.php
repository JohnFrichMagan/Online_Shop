<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle search + sorting
$search = "";
$sort = "created_at DESC"; // default

if (isset($_GET['sort'])) {
    if ($_GET['sort'] === "name") $sort = "name ASC";
    if ($_GET['sort'] === "price") $sort = "price ASC";
    if ($_GET['sort'] === "date") $sort = "created_at DESC";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where = "WHERE name LIKE '%$search%'";
} else {
    $where = "";
}

// Pagination setup
$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Count total products
$countQuery = "SELECT COUNT(*) as total FROM products $where";
$countResult = mysqli_query($conn, $countQuery);
$totalProducts = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalProducts / $limit);

// Fetch products with pagination
$query = "SELECT * FROM products $where ORDER BY $sort LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Products</title>
  <style>
    body {
  font-family: "Poppins", sans-serif;
  background: #1f293a;
  margin: 0;
  padding: 0;
  color: #333;
  overflow-x: hidden; /* Prevent horizontal scroll */
  overflow-y: auto;   /* Only show vertical scroll if really needed */
}

h2 {
  text-align: center;
  color: #fff;
  margin: 20px 0;
  font-size: 1.6rem;
}

    .top-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      max-width: 1200px;
      margin: auto;
      margin-bottom: 20px;
      padding: 0 10px;
    }
    .back-btn {
      background: #2563eb;
      color: #fff;
      padding: 8px 14px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .back-btn:hover { background: #2563eb; }

    .search-box { flex-grow: 1; text-align: center; }
    .search-box input, .search-box select {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 0.9rem;
      margin-right: 5px;
    }
    .search-box button {
      padding: 8px 14px;
      background: #2563eb;
      color: #fff;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
    }
    .search-box button:hover { background: #1d4ed8; }

    .alert {
      max-width: 600px;
      margin: 20px auto;
      padding: 12px 18px;
      border-radius: 8px;
      font-weight: 500;
      text-align: center;
      opacity: 1;
      transition: opacity 1s ease-out;
    }
    .alert.success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
    .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }

    .product-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  max-width: 1200px;
  margin: auto;
  margin-bottom: 20px; /* ⬅️ changed from 60px */
  padding: 0 10px;
}
    .card {
      background: #fff;
      border-radius: 12px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transition: 0.3s;
      display: flex;
      flex-direction: column;
      height: 100%;
    }
    .card:hover { transform: translateY(-5px); }
    .card img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 10px;
    }
    .card h3 {
      color: #2563eb;
      margin: 10px 0;
      font-size: 1rem;
    }
    .card p { color: #555; font-size: 0.85rem; flex-grow: 1; }
    .price {
      font-weight: bold;
      color: #16a34a;
      margin: 8px 0;
      font-size: 0.95rem;
    }

    /* ✅ Quantity style with colors */
    .quantity {
      font-size: 0.9rem;
      margin: 5px 0;
      font-weight: 600;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 5px;
    }
    .quantity.red { color: #dc2626; }   /* 🔴 */
    .quantity.yellow { color: #ca8a04; } /* 🟡 */
    .quantity.green { color: #16a34a; }  /* 🟢 */

    .card-actions {
      display: flex;
      justify-content: space-between;
      margin-top: auto;
    }
    .btn {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 8px;
      font-size: 0.8rem;
      text-decoration: none;
      flex: 1;
      text-align: center;
    }
    .btn.edit {
      background: #3b82f6;
      color: #fff;
      margin-right: 5px;
    }
    .btn.edit:hover { background: #1d4ed8; }
    .btn.delete {
      background: #ef4444;
      color: #fff;
      margin-left: 5px;
    }
    .btn.delete:hover { background: #b91c1c; }

    .pagination {
      text-align: center;
      margin: 70px 0 30px 0;
    }
    .pagination a {
      display: inline-block;
      padding: 6px 12px;
      margin: 0 3px;
      border-radius: 6px;
      background: #3b82f6;
      text-decoration: none;
      color: #fff;
      font-size: 0.85rem;
    }
    .pagination a.active { background: #1e40af; }
    .pagination a:hover { background: #2563eb; }
  </style>
  <script>
    function confirmDelete(id) {
      if (confirm("⚠️ Are you sure you want to delete this product?")) {
        // ✅ Add timestamp to always refresh and avoid cache issue
        window.location.href = "delete_product.php?id=" + id + "&t=" + new Date().getTime();
      }
    }

    window.onload = function() {
      setTimeout(() => {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(alert => alert.style.opacity = "0");
      }, 10000);
    };
  </script>
</head>
<body>
  <h2>All Products</h2>
  <div class="top-bar">
    <a href="products.php" class="back-btn">⬅ Back</a>
    <div class="search-box">
      <form method="GET" action="">
        <input type="text" name="search" placeholder="🔍 Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="sort">
          <option value="date" <?php if(isset($_GET['sort']) && $_GET['sort']=="date") echo "selected"; ?>>Newest</option>
          <option value="name" <?php if(isset($_GET['sort']) && $_GET['sort']=="name") echo "selected"; ?>>Name</option>
          <option value="price" <?php if(isset($_GET['sort']) && $_GET['sort']=="price") echo "selected"; ?>>Price</option>
        </select>
        <button type="submit">Search</button>
      </form>
    </div>
  </div>

  <!-- Messages -->
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated') { ?>
    <div class="alert success">✅ Product updated successfully!</div>
  <?php } ?>
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') { ?>
    <div class="alert success">🗑️ Product deleted successfully!</div>
  <?php } ?>
  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'error') { ?>
    <div class="alert error">❌ Something went wrong!</div>
  <?php } ?>

  <!-- Product Grid -->
  <div class="product-grid">
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <div class="card">
        <?php if (!empty($row['image'])) { ?>
          <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        <?php } else { ?>
          <img src="no-image.png" alt="No Image">
        <?php } ?>
        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
        <p><?php echo htmlspecialchars($row['description']); ?></p>
        <div class="price">$<?php echo number_format($row['price'], 2); ?></div>

        <!-- ✅ Color-coded Quantity -->
        <?php
          $qty = (int)$row['quantity'];
          if ($qty <= 0) {
              $qtyClass = "red";
              $qtyText = "🔴 Out of Stock";
          } elseif ($qty <= 5) {
              $qtyClass = "yellow";
              $qtyText = "🟡 Low Stock ($qty)";
          } else {
              $qtyClass = "green";
              $qtyText = "🟢 In Stock ($qty)";
          }
        ?>
        <div class="quantity <?php echo $qtyClass; ?>">
          <?php echo $qtyText; ?>
        </div>

        <div class="card-actions">
          <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn edit">✏️ Edit</a>
          <a href="javascript:void(0);" onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn delete">🗑️ Delete</a>
        </div>
      </div>
    <?php } ?>
  </div>

  <!-- Pagination -->
  <div class="pagination">
    <?php if ($page > 1) { ?>
      <a href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $_GET['sort'] ?? 'date'; ?>">⬅ Prev</a>
    <?php } ?>

    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
      <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $_GET['sort'] ?? 'date'; ?>" class="<?php if ($i == $page) echo 'active'; ?>"><?php echo $i; ?></a>
    <?php } ?>

    <?php if ($page < $totalPages) { ?>
      <a href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo $_GET['sort'] ?? 'date'; ?>">Next ➡</a>
    <?php } ?>
  </div>
</body>
</html>
