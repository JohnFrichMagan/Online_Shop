<?php
session_start();
include 'db.php';

// Check login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullName = '';
$address = '';
$mobile = '';
$success = '';
$error = '';

// Determine items to checkout
$buy_now_product = $_GET['product_id'] ?? null;
$buy_now_qty = isset($_GET['qty']) ? max(1, intval($_GET['qty'])) : 1;
$cart_items = $_SESSION['cart'] ?? [];

$items = [];

// Fetch Buy Now product
if ($buy_now_product) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $buy_now_product);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        $product = $res->fetch_assoc();
        if ($product && $product['quantity'] > 0) {
            $product['selected_qty'] = min($buy_now_qty, $product['quantity']);
            $items[] = $product;
        } else {
            $error = "❌ The product '{$product['name']}' is out of stock.";
        }
    }
    $stmt->close();
} elseif (!empty($cart_items)) {
    foreach ($cart_items as $citem) {
        $pid = is_array($citem) ? $citem['id'] : $citem;
        $qty = is_array($citem) ? $citem['quantity'] : 1;

        $stmt = $conn->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($product = $res->fetch_assoc()) {
            if ($product['quantity'] > 0) {
                $product['selected_qty'] = min($qty, $product['quantity']);
                $items[] = $product;
            } else {
                $error .= "❌ '{$product['name']}' is out of stock.<br>";
            }
        }
        $stmt->close();
    }
} else {
    header("Location: user_products.php");
    exit();
}

// Handle place order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    $fullName = trim($_POST['fullName']);
    $address = trim($_POST['address']);
    $mobile = trim($_POST['mobile']);
    $payment_method = $_POST['payment_method'] ?? '';
    $quantities = $_POST['quantities'] ?? [];

    if (!$fullName || !$address || !$mobile || !$payment_method) {
        $error = "Please fill in all fields.";
    } elseif ($error) {
        $error .= " Please remove out-of-stock items.";
    } else {
        $total_price = 0;
        $order_items = [];

        foreach ($items as $item) {
            $qty = max(1, intval($quantities[$item['id']] ?? $item['selected_qty'] ?? 1));
            if ($qty > $item['quantity']) $qty = $item['quantity'];

            $subtotal = $item['price'] * $qty;
            $total_price += $subtotal;

            $order_items[] = [
                'product_id' => $item['id'],
                'quantity' => $qty,
                'price' => $item['price']
            ];
        }

        $stmt = $conn->prepare("
            INSERT INTO orders (customer_id, full_name, address, mobile, total, payment_method, status) 
            VALUES (?,?,?,?,?,?,?)
        ");
        if ($stmt) {
            $default_status = "Pending";
            $stmt->bind_param("isssdss", $user_id, $fullName, $address, $mobile, $total_price, $payment_method, $default_status);

            if ($stmt->execute()) {
                $order_id = $stmt->insert_id;
                $stmt->close();

                $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
                $stmt_update_stock = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id=? AND quantity >= ?");

                foreach ($order_items as $item) {
                    $stmt_item->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                    $stmt_item->execute();

                    $stmt_update_stock->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
                    $stmt_update_stock->execute();
                }

                $stmt_item->close();
                $stmt_update_stock->close();

                if (!$buy_now_product) unset($_SESSION['cart']);

                header("Location: user_order.php?success=1");
                exit();
            } else {
                $error = "❌ Failed to place order. Error: " . $stmt->error;
            }
        } else {
            $error = "❌ Database error. Prepare failed: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Checkout | My System</title>
<style>
body { margin:0; font-family:"Poppins",sans-serif; background:#f3f4f6; color:#333; }
header { background:#3b82f6; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 4px 8px rgba(0,0,0,0.15);}
header h2 { margin:0;color:#fff;font-weight:600;}
.back-btn { display:inline-block; margin-bottom:20px; padding:10px 25px; background:#2563eb; color:#fff; border-radius:8px; text-decoration:none; font-weight:500; transition:0.3s; }
.back-btn:hover { background:#1d4ed8; }
.checkout-container {max-width:900px; margin:40px auto; background:#fff; padding:30px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.1);}
.checkout-container h2 {text-align:center; color:#2563eb; margin-bottom:20px;}
form .form-group { display:flex; flex-direction:column; margin-bottom:20px; }
form label { font-weight:500; margin-bottom:5px; color:#2563eb; }
form input[type=text], form input[type=number], form select { padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1rem; width:95%; }
.checkout-table { width:100%; border-collapse:collapse; margin-top:10px; }
.checkout-table th, .checkout-table td { padding:12px; text-align:center; border-bottom:1px solid #ddd; }
.checkout-table th { background:#3b82f6; color:#fff; position:sticky; top:0; }
.checkout-table td input[type=number] { width:60px; text-align:center; border-radius:6px; border:1px solid #ccc; }
.total-section { display:flex; justify-content:flex-end; align-items:center; margin-top:20px; font-weight:bold; font-size:1.3rem; }
button.place-order { display: block; margin: 20px auto 0; padding: 15px; border: none; border-radius: 10px; background: #10b981; color: #fff; font-weight: 600; cursor: pointer; transition: 0.3s; font-size:1.1rem; }
button.place-order:hover { background:#059669; }
/* Confirmation Modal */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:#fff; padding:30px; border-radius:12px; max-width:400px; text-align:center; }
.modal-content button { margin:10px; padding:12px 20px; border:none; border-radius:8px; cursor:pointer; font-weight:500; font-size:1rem; }
.modal-content .proceed { background:#10b981; color:#fff; }
.modal-content .cancel { background:#ef4444; color:#fff; }
.success {color:green; text-align:center; margin-bottom:15px;}
.error {color:red; text-align:center; margin-bottom:15px;}
</style>
<script>
function updateTotal() {
    let total = 0;
    document.querySelectorAll('input.quantity-input').forEach(input => {
        const price = parseFloat(input.dataset.price);
        const qty = parseInt(input.value);
        total += price * qty;
    });
    document.getElementById('total').innerText = total.toFixed(2);
}
function confirmOrder(event) {
    event.preventDefault();
    document.getElementById('confirmationModal').style.display = 'flex';
}
function cancelOrder() {
    document.getElementById('confirmationModal').style.display = 'none';
}
function proceedOrder() {
    document.getElementById('confirm_order').value = 1;
    document.getElementById('checkoutForm').submit();
}
</script>
</head>
<body>

<header>
    <h2>Checkout</h2>
    <a href="user_products.php" class="back-btn">← Back </a>
</header>

<div class="checkout-container">

    <?php if($success) echo "<div class='success'>$success</div>"; ?>
    <?php if($error) echo "<div class='error'>$error</div>"; ?>

    <form id="checkoutForm" method="POST" onsubmit="confirmOrder(event)">
        <input type="hidden" name="confirm_order" id="confirm_order" value="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullName" placeholder="Enter your full name" value="<?php echo htmlspecialchars($fullName); ?>" required>
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" placeholder="Enter your delivery address" value="<?php echo htmlspecialchars($address); ?>" required>
        </div>
        <div class="form-group">
            <label>Mobile Number</label>
            <input type="text" name="mobile" placeholder="Enter your mobile number" value="<?php echo htmlspecialchars($mobile); ?>" required>
        </div>
        <div class="form-group" style="display:flex; flex-direction:column; align-items:center;">
            <label style="width:95%; text-align:center;">Payment Method</label>
            <select name="payment_method" required style="width:95%; padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1rem; text-align:center;">
                <option value="">Select Payment Method</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="Credit/Debit Card">Credit/Debit Card</option>
                <option value="Gcash">Gcash</option>
                <option value="PayPal">PayPal</option>
            </select>
        </div>

        <table class="checkout-table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
            </tr>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td>$<?php echo number_format($item['price'],2); ?></td>
                <td>
                    <input type="number" name="quantities[<?php echo $item['id']; ?>]" 
                           value="<?php echo $item['selected_qty'] ?? 1; ?>" 
                           min="1" max="<?php echo $item['quantity']; ?>"
                           class="quantity-input" data-price="<?php echo $item['price']; ?>" oninput="updateTotal()">
                    <small style="color:#d97706;">Stock: <?php echo $item['quantity']; ?></small>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="total-section">
            Total: $<span id="total">
                <?php 
                $total = 0;
                foreach($items as $item) {
                    $qty = $item['selected_qty'] ?? 1;
                    $total += $item['price'] * $qty;
                }
                echo number_format($total,2);
                ?>
            </span>
        </div>

        <button type="submit" class="place-order" <?= $error ? "disabled style='background:#9ca3af;cursor:not-allowed;'" : "" ?>>Place Order</button>
    </form>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmationModal">
    <div class="modal-content">
        <h3>Confirm Order?</h3>
        <p>Do you want to proceed with placing your order?</p>
        <button class="proceed" onclick="proceedOrder()">Proceed</button>
        <button class="cancel" onclick="cancelOrder()">Cancel</button>
    </div>
</div>

<script>
updateTotal(); // initialize total
</script>
</body>
</html>
