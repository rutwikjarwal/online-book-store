<?php 
session_start();
include "../db_conn.php";
include "func-validation.php";

// Load PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../libs/PHPMailer/PHPMailer.php';

require '../libs/PHPMailer/SMTP.php';
require '../libs/PHPMailer/Exception.php';

if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    $text = "Email";
    $location = "../login.php";
    $ms = "signup_error";
    is_empty($email, $text, $location, $ms, "");

    $text = "Password";
    is_empty($password, $text, $location, $ms, "");

    if ($password !== $confirm_password) {
        $em = "Passwords don't match";
        header("Location: ../login.php?signup_error=$em");
        exit;
    }

    // Check if email already exists
    $sql = "SELECT email FROM admin WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $em = "Email already exists";
        header("Location: ../login.php?signup_error=$em");
        exit;
    }

    // Hash password and create verification code
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $verification_code = bin2hex(random_bytes(16));

    // Insert user into database
    $sql = "INSERT INTO admin (email, password, verification_code, is_verified) VALUES (?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $hashed_password, $verification_code]);

    // Send verification email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rutwikjarwal.484@gmail.com'; // ✅ Your Gmail
        $mail->Password   = 'your_app_password';          // ✅ Use Gmail App Password here
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('rutwikjarwal.484@gmail.com', 'Book Website');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address';

        $verify_link = "http://localhost/online-book-store/php/verify.php?email=$email&code=$verification_code";

        $mail->Body = "
            <h3>Hello!</h3>
            <p>Thank you for signing up.</p>
            <p>Please click the link below to verify your email:</p>
            <a href='$verify_link'>$verify_link</a>
            <br><br>
            <small>If you did not sign up, please ignore this email.</small>
        ";

        $mail->send();
        $sm = "Account created! Check your email to verify.";
        header("Location: ../login.php?signup_success=$sm");
        exit;

    } catch (Exception $e) {
        $em = "Verification email failed. " . $mail->ErrorInfo;
        header("Location: ../login.php?signup_error=$em");
        exit;
    }

} else {
    header("Location: ../login.php");
    exit;
}
