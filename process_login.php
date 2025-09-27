<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $stmt = $conn->prepare("SELECT id, name, email, password FROM admin WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: login.php");
            exit();
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            $db_password = $admin['password'];

            // Check hashed password
            if (password_verify($password, $db_password)) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];

                header("Location: home.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid password!";
                header("Location: login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Email not found!";
            header("Location: login.php");
            exit();
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid request.";
    header("Location:login.php");
    exit();
}
?>
