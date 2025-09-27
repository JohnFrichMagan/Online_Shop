<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    header("Location: login.php");
    exit();
}

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_email = $_SESSION['admin_email'];

// Handle product submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    
    // Handle image upload
    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $image = $targetDir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $insert = "INSERT INTO products (name, description, image, price, quantity) VALUES ('$name', '$description', '$image', '$price', '$quantity')";
    mysqli_query($conn, $insert);
}

// Fetch only the latest 2 products
$latest_product = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 2");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products</title>
  <link rel="icon" href="login.png" type="image/x-icon">
  <style>
  
     html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  height: 100%;
  overflow: hidden; /* 🚀 removes both vertical and horizontal scrollbars */
  font-family: "Poppins", sans-serif;
  background: #1f293a;
  color: #333;
  box-sizing: border-box;
}


    /* Header */
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
        text-align: center;
    }

    nav {
        flex: 1;
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    nav a {
        display: inline-block;
        text-decoration: none;
        color: #fff;
        font-weight: 500;
        padding: 10px 22px;
        border-radius: 12px;
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        box-shadow: 0 4px 12px rgba(59,130,246,0.4);
        transition: all 0.3s ease;
    }
    nav a:hover {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(37,99,235,0.5);
    }
    nav a.active {
        background: #1e40af;
        box-shadow: 0 4px 12px rgba(30,64,175,0.6);
    }
    .logout {
        text-decoration: none;
        background: #ef4444;
        color: #fff;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .logout:hover {
        background: #dc2626;
        transform: scale(1.05);
    }

    .page-title { 
      text-align: center; 
      color: #fff; 
      margin: 25px 0; 
      font-size: 2rem; 
    }

    /* Main container */
    .main-container {
      display: grid;
      grid-template-columns: 1fr 2fr;
      gap: 30px;
      max-width: 1300px;
      margin: auto;
      padding: 20px;
    }

    /* Form section */
    .form-container {
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 8px 18px rgba(0,0,0,0.15);
    }
    .form-container h2 { color: #2563eb; text-align: center; margin-bottom: 20px; }
    .form-container input, .form-container textarea {
      width: 95%; 
      padding: 14px; 
      margin: 10px auto; 
      display: block;
      border: 1px solid #ccc; 
      border-radius: 8px; 
      font-size: 1rem;
    }
    .form-container button {
      width: 98%; 
      padding: 14px; 
      border: none; 
      border-radius: 10px; 
      background: linear-gradient(135deg,#3b82f6,#2563eb);
      color: #fff; 
      font-weight: 500; 
      cursor: pointer;
    }
    .form-container button:hover { background: linear-gradient(135deg,#2563eb,#1d4ed8); }

    /* Latest products container */
    .products-wrapper {
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      box-shadow: 0 8px 18px rgba(0,0,0,0.15);
    }
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-bottom: 20px;
    }
    .card {
      background: #f9fafb;
      border-radius: 12px;
      padding: 15px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card img {
      width: 100%;
      height: 200px;
      border-radius: 10px;
      margin-bottom: 10px;
      object-fit: cover;
    }
    .card h3 {
      color: #2563eb;
      margin-bottom: 6px;
      font-size: 1.1rem;
    }
    .card p {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 8px;
    }
    .card span {
      display: block;
      font-weight: bold;
      font-size: 1rem;
      color: #111;
      margin-bottom: 4px;
    }
    .card small {
      color: #444;
      font-size: 0.85rem;
    }

    .view-all-btn {
      display: inline-block;
      text-align: center;
      padding: 12px 20px;
      background: linear-gradient(135deg, #2563eb, #3b82f6);
      color: #fff;
      border-radius: 10px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .view-all-btn:hover { background: linear-gradient(135deg,#3b82f6,#2563eb); transform: translateY(-2px); }
  </style>
</head>
<body>
  <header>
    <h2>Admin Dashboard</h2>
    <nav>
        <a href="home.php" class="<?= basename($_SERVER['PHP_SELF'])=='home.php'?'active':'' ?>">🏠 Dashboard</a>
        <a href="products.php" class="<?= basename($_SERVER['PHP_SELF'])=='products.php'?'active':'' ?>">📦 Products</a>
        <a href="order.php" class="<?= basename($_SERVER['PHP_SELF'])=='order.php'?'active':'' ?>">🛒 Orders</a>
        <a href="report.php" class="<?= basename($_SERVER['PHP_SELF'])=='report.php'?'active':'' ?>">📊 Reports</a>
        <a href="manage.php" class="<?= basename($_SERVER['PHP_SELF'])=='manage.php'?'active':'' ?>">📋 Manage</a>
        <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF'])=='settings.php'?'active':'' ?>">⚙️ Settings</a>
    </nav>
    <a href="logout.php" class="logout">🚪 Logout</a>
  </header>

  <h1 class="page-title">📦 Products</h1>

  <div class="main-container">
    <!-- Left: Add Product Form -->
    <div class="form-container">
      <h2>Add Product</h2>
      <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Product Name" required>
        <textarea name="description" placeholder="Product Description"></textarea>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <input type="number" name="quantity" placeholder="Quantity" required>
        <input type="file" name="image" accept="image/*">
        <button type="submit" name="add_product">Add Product</button>
      </form>
    </div>

    <!-- Right: Latest Products + View All -->
    <div class="products-wrapper">
      <div class="products-grid">
        <?php if (mysqli_num_rows($latest_product) > 0) { 
            while ($row = mysqli_fetch_assoc($latest_product)) { ?>
              <div class="card">
                <?php if($row['image']) { ?>
                  <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <?php } else { ?>
                  <img src="images/default.png" alt="No image">
                <?php } ?>
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p><?php echo htmlspecialchars($row['description']); ?></p>
                <span>$<?php echo number_format($row['price'], 2); ?></span>
                <small>Quantity: <?php echo htmlspecialchars($row['quantity']); ?></small>
              </div>
        <?php } } else { ?>
            <p>No products yet.</p>
        <?php } ?>
      </div>

      <div style="text-align:center;">
        <a href="view_all_products.php" class="view-all-btn">View All Products</a>
      </div>
    </div>
  </div>
</body>
</html>
