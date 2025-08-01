<?php
// verify.php
include "../db_conn.php";

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    // Check if matching record exists
    $sql = "SELECT * FROM admin WHERE email = ? AND verification_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email, $code]);

    if ($stmt->rowCount() === 1) {
        // Update the user's status to verified
        $updateSql = "UPDATE admin SET is_verified = 1, verification_code = NULL WHERE email = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([$email]);

        $sm = "Your email has been successfully verified! You can now log in.";
        header("Location: ../login.php?signup_success=$sm");
        exit;
    } else {
        $em = "Invalid verification link or already verified.";
        header("Location: ../login.php?signup_error=$em");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
