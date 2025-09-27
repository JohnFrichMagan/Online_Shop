<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Optional: Admin name field
        $name = isset($_POST['name']) ? trim($_POST['name']) : null;

        // Check if email already exists
        if ($stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? LIMIT 1")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $_SESSION['error'] = "Email already registered!";
                header("Location: signup.php");
                exit();
            }
            $stmt->close();
        }

        // Hash password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin
        if ($stmt = $conn->prepare("INSERT INTO admin (name, email, password) VALUES (?, ?, ?)")) {
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Admin signup successful! Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error'] = "Signup failed! Please try again.";
                header("Location: signup.php");
                exit();
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: signup.php");
        exit();
    }
} else {
    header("Location: signup.php");
    exit();
}
?>
