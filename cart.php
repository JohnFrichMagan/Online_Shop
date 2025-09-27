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

// Handle update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        $qty = max(1, intval($qty));
        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $qty;
        }
    }
}

// Handle remove item
if (isset($_POST['remove'])) {
    $remove_index = intval($_POST['remove']);
    if (isset($_SESSION['cart'][$remove_index])) {
        unset($_SESSION['cart'][$remove_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    }
}

$cart_items = $_SESSION['cart'];
$cart_count = count($cart_items);
$total_price = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart | My System</title>
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

.cart-container {
    max-width: 900px;
    margin: auto;
    padding: 20px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
.cart-container table {
    width: 100%;
    border-collapse: collapse;
}
.cart-container th, .cart-container td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}
.cart-container th {
    background: #f3f4f6;
}
.cart-container img {
    width: 70px;
    height: 70px;
    border-radius: 10px;
    object-fit: cover;
}
.cart-container input[type="number"] {
    width: 60px;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ddd;
}
.cart-container button {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}
.update-btn {
    background: #3b82f6;
    color: #fff;
}
.update-btn:hover {
    background: #2563eb;
}
.remove-btn {
    background: #ef4444;
    color: #fff;
}
.remove-btn:hover {
    background: #dc2626;
}
.checkout-btn {
    background: #10b981;
    color: #fff;
    padding: 12px 20px;
    margin-top: 20px;
    border-radius: 10px;
    display: inline-block;
    font-weight: 600;
    text-decoration: none;
}
.checkout-btn:hover {
    background: #059669;
}
.total {
    text-align: right;
    font-size: 1.2rem;
    font-weight: bold;
    margin-top: 15px;
}
.empty {
    text-align: center;
    padding: 30px;
    font-size: 1.2rem;
    color: #555;
}
</style>
</head>
<body>
<header>
    <h2>My System</h2>
    <nav>
        <a href="user_home.php">🏠 Home</a>
        <a href="user_products.php">📦 Products</a>
        <a href="cart.php" class="active">🛒 Cart (<?php echo $cart_count; ?>)</a>
        <a href="user_order.php">📄 My Order</a>
        <a href="user_settings.php">⚙️ Settings</a>
    </nav>
    <a href="user_logout.php" class="logout">🚪 Logout</a>
</header>

<h1 class="page-title">My Cart</h1>

<div class="cart-container">
    <?php if (empty($cart_items)) { ?>
        <div class="empty">🛒 Your cart is empty.</div>
    <?php } else { ?>
        <form method="POST">
            <table>
                <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($cart_items as $index => $item) {
                    $product_id = is_array($item) ? $item['id'] : $item;
                    $quantity = is_array($item) ? $item['quantity'] : 1;

                    $product_query = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
                    $product = mysqli_fetch_assoc($product_query);

                    if (!$product) continue;

                    $subtotal = $product['price'] * $quantity;
                    $total_price += $subtotal;
                ?>
                <tr>
                    <td>
                        <img src="<?= $product['image'] ? htmlspecialchars($product['image']) : 'images/default.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td>$<?= number_format($product['price'], 2) ?></td>
                    <td>
                        <input type="number" name="quantity[<?= $index ?>]" value="<?= $quantity ?>" min="1" max="<?= $product['quantity'] ?>">
                    </td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                    <td>
                        <button type="submit" name="remove" value="<?= $index ?>" class="remove-btn">Remove</button>
                    </td>
                </tr>
                <?php } ?>
            </table>
            <div class="total">Total: $<?= number_format($total_price, 2) ?></div>
            <div style="text-align:right; margin-top:15px;">
                <button type="submit" name="update_cart" class="update-btn">Update Cart</button>
            </div>
        </form>
        <div style="text-align:right;">
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
        </div>
    <?php } ?>
</div>
</body>
</html>
