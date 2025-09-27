<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$cart_count = count($_SESSION['cart']); 

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if (is_array($item) && $item['id'] == $product_id) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    unset($item);

    if (!$found) {
        $_SESSION['cart'][] = ['id' => $product_id, 'quantity' => $quantity];
    }

    // ✅ Redirect so refresh doesn’t resubmit
    header("Location: cart.php?added=1");
    exit();
}


// Handle Buy Now
if (isset($_POST['buy_now'])) {
    $buy_product_id = intval($_POST['product_id']);
    header("Location: checkout.php?product_id=$buy_product_id");
    exit();
}

// Search and Sorting
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination setup
$limit = 4; // 4 products per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Count total products
$count_query = "SELECT COUNT(*) as total FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $limit);

// Build query
$query = "SELECT * FROM products WHERE name LIKE '%$search%' OR description LIKE '%$search%'";

// Sorting logic
switch ($sort) {
    case "price_asc":
        $query .= " ORDER BY price ASC";
        break;
    case "price_desc":
        $query .= " ORDER BY price DESC";
        break;
    case "name":
        $query .= " ORDER BY name ASC";
        break;
    default:
        $query .= " ORDER BY created_at DESC"; 
        break;
}

$query .= " LIMIT $limit OFFSET $offset";
$products_result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products | My System</title>
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
nav {
    flex: 1;
    display: flex;
    justify-content: center;
    gap: 15px;
}
nav a {
    text-decoration: none;
    color: #fff;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 10px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.1);
}
nav a:hover {
    background: #2563eb;
    transform: translateY(-2px);
}
nav a.active {
    background: #1e40af;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.logout {
    text-decoration: none;
    background: #ef4444;
    color: #fff;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
}
.logout:hover {
    background: #dc2626;
    transform: translateY(-2px);
}
.page-title {
    text-align: center;
    color: #fff;
    margin: 30px 0 10px;
    font-size: 2rem;
}
.filters {
    max-width: 100%;
    margin: 20px auto;
    display: flex;
    justify-content: center;
}
.filters form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: center;
}
.filters input, .filters select, .filters button {
    padding: 10px 14px;
    border-radius: 8px;
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 0.95rem;
}
.filters input {
    min-width: 220px;
}
.filters button {
    background: #3b82f6;
    color: white;
    cursor: pointer;
    font-weight: 500;
}
.filters button:hover {
    background: #2563eb;
}
.products-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* ✅ Always 4 per row */
    gap: 15px;
    max-width: 950px; /* ✅ smaller container */
    margin: auto;
    padding: 15px;
    align-items: start;
}

.card {
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 210px;   /* ✅ smaller card width */
    height: 300px;  /* ✅ more compact height */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card img {
    width: 100%;
    border-radius: 10px;
    margin-bottom: 8px;
    height: 100px;  /* ✅ smaller image */
    object-fit: cover;
}

.card h3 {
    color: #2563eb;
    margin-bottom: 5px;
    font-size: 0.95rem;
    line-height: 1.2rem;
}

.card p {
    font-size: 0.8rem;
    color: #555;
    margin-bottom: 5px;
    max-height: 35px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.card span {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

.card form {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
}

.card button {
    padding: 8px 15px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: 0.3s;
}
.card .buy-now {
    background: #10b981;
    color: #fff;
}
.card .buy-now:hover {
    background: #059669;
}
.card .add-cart {
    background: #3b82f6;
    color: #fff;
}
.card .add-cart:hover {
    background: #2563eb;
}
.stock {
    font-size: 0.85rem;
    margin-bottom: 8px;
    color: #d97706;
}
.out-stock {
    color: #dc2626;
    font-weight: bold;
}
.pagination {
    text-align: center;
    margin: 20px 0;
}
.pagination a {
    display: inline-block;
    margin: 0 5px;
    padding: 8px 14px;
    background: #3b82f6;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
}
.pagination a.active {
    background: #1e40af;
}
.pagination a:hover {
    background: #2563eb;
}

/* ✅ Modal */
#confirmModal {
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    display:flex; align-items:center; justify-content:center;
    z-index:1000;
}
#confirmModal .box {
    background:#fff;
    padding:20px;
    border-radius:12px;
    text-align:center;
    width:300px;
}
#confirmModal h3 { margin-bottom:15px; color:#2563eb; }
#confirmModal p { margin-bottom:20px; color:#333; }
#confirmModal button {
    padding:8px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
#cancelBtn { background:#ef4444; color:#fff; }
#confirmBtn { background:#10b981; color:#fff; }
</style>
</head>
<body>
<header>
    <h2>My System</h2>
    <nav>
        <a href="user_home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_home.php' ? 'active' : '' ?>">🏠 Home</a>
        <a href="user_products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_products.php' ? 'active' : '' ?>">📦 Products</a>
        <a href="cart.php" class="cart-btn <?= basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : '' ?>">🛒 Cart (<?php echo $cart_count; ?>)</a>
        <a href="user_order.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_order.php' ? 'active' : '' ?>">📄 My Order</a>
        <a href="user_settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_settings.php' ? 'active' : '' ?>">⚙️ Settings</a>
    </nav>
    <a href="user_logout.php" class="logout">🚪 Logout</a>
</header>

<h1 class="page-title">Products</h1>

<div class="filters">
    <form method="GET">
        <input type="text" name="search" placeholder="🔍 Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="sort">
            <option value="newest" <?= $sort=='newest'?'selected':'' ?>>Newest</option>
            <option value="price_asc" <?= $sort=='price_asc'?'selected':'' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort=='price_desc'?'selected':'' ?>>Price: High to Low</option>
            <option value="name" <?= $sort=='name'?'selected':'' ?>>Name</option>
        </select>
        <button type="submit">Apply</button>
    </form>
</div>

<div class="products-container">
    <?php while ($product = mysqli_fetch_assoc($products_result)) { ?>
        <div class="card">
            <?php if ($product['image']) { ?>
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php } else { ?>
                <img src="images/default.png" alt="No image">
            <?php } ?>
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <span>$<?php echo number_format($product['price'], 2); ?></span>

            <?php if (isset($product['quantity'])): ?>
                <?php if ($product['quantity'] > 0): ?>
                    <div class="stock"><?= $product['quantity'] <= 5 ? "⚠️ Only {$product['quantity']} left!" : "In Stock: {$product['quantity']}" ?></div>
                <?php else: ?>
                    <div class="stock out-stock">❌ Out of Stock</div>
                <?php endif; ?>
            <?php endif; ?>

            <form method="POST" class="cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <button type="button" class="buy-now" <?= isset($product['quantity']) && $product['quantity']==0 ? "disabled" : "" ?>>Buy Now</button>
                <button type="button" class="add-cart" <?= isset($product['quantity']) && $product['quantity']==0 ? "disabled" : "" ?>>Add to Cart</button>
            </form>
        </div>
    <?php } ?>
</div>

<!-- ✅ Pagination -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $page-1 ?>">⬅ Prev</a>
    <?php endif; ?>

    <?php for ($i=1; $i<=$total_pages; $i++): ?>
        <a href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $i ?>" class="<?= $i==$page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?search=<?= urlencode($search) ?>&sort=<?= $sort ?>&page=<?= $page+1 ?>">Next ➡</a>
    <?php endif; ?>
</div>

<!-- ✅ Confirmation Modal -->
<div id="confirmModal">
    <div class="box">
        <h3 id="modalTitle">Confirm Action</h3>
        <p id="modalMessage"></p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button id="cancelBtn">Cancel</button>
            <button id="confirmBtn">Confirm</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("confirmModal");
    const modalTitle = document.getElementById("modalTitle");
    const modalMessage = document.getElementById("modalMessage");
    const cancelBtn = document.getElementById("cancelBtn");
    const confirmBtn = document.getElementById("confirmBtn");

    let currentForm = null;
    let actionType = "";

    // ✅ Always reset modal on page load / back / refresh
    modal.style.display = "none";
    currentForm = null;
    actionType = "";
    // Clear any POST/back state
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // ✅ Intercept Add to Cart
    document.querySelectorAll(".add-cart").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            currentForm = this.closest("form");
            actionType = "add_to_cart";
            modalTitle.textContent = "Add to Cart";
            modalMessage.textContent = "Do you want to add this item to your cart?";
            modal.style.display = "flex";
        });
    });

    // ✅ Intercept Buy Now
    document.querySelectorAll(".buy-now").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            currentForm = this.closest("form");
            actionType = "buy_now";
            modalTitle.textContent = "Buy Now";
            modalMessage.textContent = "Do you want to proceed to checkout?";
            modal.style.display = "flex";
        });
    });

    // Cancel
    cancelBtn.addEventListener("click", () => {
        modal.style.display = "none";
        currentForm = null;
        actionType = "";
    });

    // Confirm
    confirmBtn.addEventListener("click", () => {
        if (currentForm && actionType) {
            if (actionType === "buy_now") {
                const productId = currentForm.querySelector("input[name='product_id']").value;
                window.location.href = "checkout.php?product_id=" + productId;
            } else if (actionType === "add_to_cart") {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = "add_to_cart";
                hiddenInput.value = "1";
                currentForm.appendChild(hiddenInput);
                currentForm.submit();
            }
        }
        modal.style.display = "none";
        currentForm = null;
        actionType = "";
    });
});
</script>


</body>
</html>
