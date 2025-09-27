<?php
session_start();
include 'db.php';

// 🔒 Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 🔍 Check if product ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_all_products.php?msg=error");
    exit();
}

$id = intval($_GET['id']);

// 📦 Get product details
$result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: view_all_products.php?msg=error");
    exit();
}

$successMsg = "";
$errorMsg = "";

// ✏️ Handle update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']); 

    $imagePath = $product['image'];

    // 📷 Handle new image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $targetFile = $targetDir . time() . "_" . basename($_FILES["image"]["name"]);

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            // Delete old image
            if (!empty($product['image']) && file_exists($product['image'])) {
                unlink($product['image']);
            }
            $imagePath = $targetFile;
        }
    }

    // ✅ Update query
    $query = "UPDATE products 
              SET name='$name', description='$description', price='$price', quantity='$quantity', image='$imagePath' 
              WHERE id=$id";

    if (mysqli_query($conn, $query)) {
        // refresh product info
        $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id"));
        $successMsg = "✅ Product updated successfully!";
    } else {
        $errorMsg = "❌ Error updating: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product</title>
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background: #f9fafb;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .form-container {
      width: 100%;
      max-width: 800px;
      background: #fff;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
      position: relative;
    }
    h2 {
      text-align: center;
      color: #1e40af;
      margin-bottom: 20px;
      font-size: 1.5rem;
    }
    .message {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      font-weight: 500;
      text-align: center;
    }
    .success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #16a34a;
    }
    .error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #dc2626;
    }
    label {
      font-weight: 600;
      margin-top: 10px;
      display: block;
      color: #374151;
      font-size: 0.95rem;
    }
    input, textarea {
      width: 94%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 15px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 0.95rem;
    }
    textarea { resize: vertical; }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }
    .image-preview {
      margin-top: 10px;
      text-align: center;
    }
    .image-preview img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #ddd;
    }
    .btn {
      margin-top: 20px;
      width: 100%;
      padding: 12px;
      border: none;
      border-radius: 8px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }
    .btn:hover {
      background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    }
    .back-link {
      position: absolute;
      top: 15px;
      left: 15px;
      padding: 8px 12px;
      border-radius: 6px;
      background: linear-gradient(135deg, #2563eb, #1d4ed8);
      color: #fff;
      font-weight: 500;
      text-decoration: none;
      font-size: 0.9rem;
    }
    .back-link:hover {
      background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
    }
    /* 📱 Mobile-friendly */
    @media (max-width: 640px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
      .image-preview img {
        width: 100px;
        height: 100px;
      }
      input, textarea {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="form-container">
    <a href="view_all_products.php" class="back-link">⬅ Back</a>
    <h2>✏️ Edit Product</h2>

    <?php if ($successMsg): ?>
      <div class="message success"><?php echo $successMsg; ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div class="message error"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" action="edit_product.php?id=<?php echo $id; ?>">
      <div class="form-grid">
        <!-- LEFT SIDE -->
        <div>
          <label for="name">Product Name</label>
          <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

          <label for="description">Description</label>
          <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>

          <label for="price">Price ($)</label>
          <input type="number" step="0.01" name="price" id="price" value="<?php echo $product['price']; ?>" required>
        </div>

        <!-- RIGHT SIDE -->
        <div>
          <label for="quantity">Quantity</label>
          <input type="number" name="quantity" id="quantity" value="<?php echo $product['quantity']; ?>" min="0" required>

          <label for="image">Image</label>
          <input type="file" name="image" id="image" accept="image/*" onchange="previewImage(event)">
          <div class="image-preview">
            <img id="preview" src="<?php echo !empty($product['image']) ? $product['image'] : 'https://via.placeholder.com/120'; ?>" alt="Preview">
          </div>
        </div>
      </div>
      <button type="submit" class="btn">💾 Save Changes</button>
    </form>
  </div>

  <script>
    // ✅ Live image preview
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        document.getElementById('preview').src = reader.result;
      }
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</body>
</html>
