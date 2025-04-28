<?php
session_start();

// Validate form submission method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Form not submitted.");
}

// Check if username/password fields exist
if (empty($_POST['username']) || empty($_POST['password'])) {
    header("Location: login&register.html?error=missing_fields");
    exit();
}

// Database connection
include '../main_page/back-end/db_conn.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT username, `password` FROM user WHERE username = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $_POST['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($_POST['password'], $user['password'])) {
        // Regenerate session ID for security
        session_regenerate_id(true);
        if (isset($_POST['remember'])) {
            // Set secure cookie (BEFORE ANY OUTPUT)
            $cookie_options = [
                'expires'  => time() + 86400 * 30,
                'path'     => '/',
                'secure'   => true,    // Requires HTTPS
                'httponly' => true,
                'samesite' => 'Strict'
            ];
            setcookie("user", $user['username'], $cookie_options);
        }
        $_SESSION['username'] = $user['username'];
        // Redirect to dashboard
        header("Location: ../main_page/front-end/logged_Index.php");
    } else {
        header("Location: login&register.html?error=invalid_password");
    }
} else {
    header("Location: login&register.html?error=user_not_found");
}



$stmt->close();
$conn->close();
