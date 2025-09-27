<?php
session_start();
include 'db.php';

// ✅ Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ✅ Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_all_products.php?msg=error");
    exit();
}

$id = intval($_GET['id']);

// ✅ Fetch product
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if ($product) {
    // ✅ Delete product image (handle relative paths properly)
    if (!empty($product['image'])) {
        $imagePath = __DIR__ . '/' . $product['image']; // absolute path
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // ✅ Delete product from DB
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->bind_param("i", $id);
    $deleteStmt->execute();

    header("Location: view_all_products.php?msg=deleted&t=" . time()); // ✅ force reload, no cache
    exit();
} else {
    header("Location: view_all_products.php?msg=error");
    exit();
}
