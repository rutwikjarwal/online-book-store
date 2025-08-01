<?php
session_start();

if (isset($_POST['email']) && isset($_POST['password'])) {
    include "../db_conn.php";
    include "func-validation.php";

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $redirect = $_POST['redirect'] ?? '';

    // Validation
    $text = "Email";
    $location = "../login.php";
    $ms = "error";
    is_empty($email, $text, $location, $ms, $redirect);

    $text = "Password";
    is_empty($password, $text, $location, $ms, $redirect);

    // Check if email exists
    $sql = "SELECT * FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch();

        // Password check
        if (password_verify($password, $user['password'])) {
            // Check if email is verified
            if (!$user['is_verified']) {
                $em = "Please verify your email first.";
                header("Location: ../login.php?error=$em");
                exit;
            }

            // Login success: Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // Safe redirect (optional)
            if (!empty($redirect)) {
                $allowed_hosts = ['yourdomain.com', 'localhost'];
                $parsed = parse_url($redirect);

                if (in_array($parsed['host'] ?? '', $allowed_hosts, true)) {
                    header("Location: $redirect");
                    exit;
                }
            }

            header("Location: ../admin.php");
            exit;
        }
    }

    // Failed login
    $em = "Incorrect email or password.";
    header("Location: ../login.php?error=$em" . (!empty($redirect) ? "&redirect=$redirect" : ""));
    exit;
} else {
    header("Location: ../login.php");
    exit;
}
