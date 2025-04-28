<?php
// Start session to store error messages and form data
session_start();
// Database connection
include '../main_page/back-end/db_conn.php';
// Check connection
if ($conn->connect_error) {
    $_SESSION['errors'] = ["Database connection failed: " . $conn->connect_error];
    header("Location: login&register.html");
    exit();
}
// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize errors array ( an array where the eventual errors will be stored and shown at the end)
    $errors = [];

    // Store form data in session to repopulate the form
    $_SESSION['form_data'] = [
        'email' => $_POST['email'] ?? '',
        'username' => $_POST['username'] ?? ''
        // We don't store passwords in session for security reasons
    ];

    // Get form data and sanitize inputs
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $username = htmlspecialchars($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirmPassword"];
    $shipping_address = $_POST["shipping_address"];

    // Validate username 
    if ($username !== $_POST['username']) {
        header("Location: login&register.html?error=username_not_valid");
        exit();
    }
    // Validate password match
    if ($password !== $confirm_password) {
        header("Location: login&register.html?error=password_mismatch");
        exit();
    }

    // Validate password strength (example: at least 8 characters)
    if (strlen($password) < 8) {
        header("Location: login&register.html?error=password_length");
        exit();
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        header("Location: login&register.html?error=email_exists");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0) {
        header("Location: login&register.html?error=username_exists");
        exit();
    }
    // Insert the user into the database
    try {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare SQL and bind parameters
        $stmt = $conn->prepare("INSERT INTO user (username,email, password, shipping_address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $shipping_address);

        if ($stmt->execute()) {
            // Clear any session data from previous attempts
            unset($_SESSION['errors']);
            unset($_SESSION['form_data']);
            // Redirect to success page or login page
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;
            header("Location: ../main_page/front-end/logged_Index.php"); //nome del file in cui deve andare dopo aver creato l'user
            exit();
        } else {
            $errors[] = "Registration failed: " . $stmt->error;
        }
    } catch (Exception $e) {
        $errors[] = "Registration failed: " . $e->getMessage();
    }


    // If there are errors, store them in session and redirect back to registration page
    if (!empty($errors)) {
        header("Location: test.php?errors");
        exit();
    }
}
// Close the database connection
$conn->close();
