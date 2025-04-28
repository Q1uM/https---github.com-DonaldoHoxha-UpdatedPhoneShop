<?php
session_start();
include '../back-end/db_conn.php';
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Utente non autenticato"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo</title>
    <style>
        table,
        th,
        td {
            border: 1px black solid;
            border-collapse: collapse;
        }
    </style>
</head>

<body>
    <?php
    // Get the user ID
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Utente non trovato"]);
        exit();
    }
    $user_id = $user['id'];
    // We get the username, email, shipping_address and total orders of the user
    $stmt = $conn->prepare("SELECT username, email, shipping_address FROM user WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<p>Nome: " . $row['username'] . "</p>";
        echo "<p>Email: " . $row['email'] . "</p>";
        echo "<p>Indirizzo: " . $row['shipping_address'] . "</p>";
    }
    $stmt = $conn->prepare("SELECT COUNT(*) as number_of_orders from orders where user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<p>Ordini totali: " . $row['number_of_orders'] . "</p>";
    }
    ?>
    <a href="logged_Index.php"> Continua ad acquistare</a>
</body>

</html>