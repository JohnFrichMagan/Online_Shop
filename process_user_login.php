<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Prepare statement
        $stmt = $conn->prepare("SELECT id, email, password, status FROM users WHERE email = ? LIMIT 1");

        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: user_login.php");
            exit();
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Check if user is blocked
            if (isset($user['status']) && $user['status'] === 'blocked') {
                $_SESSION['error'] = "🚫 Your account has been blocked. Please contact admin.";
                header("Location: user_login.php");
                exit();
            }

            // ✅ Check password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['success'] = "✅ Login successful!";
                header("Location: user_home.php");
                exit();
            } else {
                $_SESSION['error'] = "❌ Invalid password!";
                header("Location: user_login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "❌ Email not found!";
            header("Location: user_login.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "⚠️ Please fill in all fields.";
        header("Location: user_login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location: user_login.php");
    exit();
}
?>
