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
    <title>Orders</title>
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
    // We get the orders that the user did
    $stmt = $conn->prepare("SELECT date(o.order_date) as order_date, o.quantity, o.total_price, p.name 
                                    from orders o
                                    join product p on p.id = o.product_id
                                    where user_id = ? 
                                    order by o.order_date desc");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<p>Articolo: " . $row['name'] . "</p>";
        echo "<p>Data: " . $row['order_date'] . "</p>";
        echo "<p>Quantità: " . $row['quantity'] . "</p>";
        echo "<p>Prezzo: " . $row['total_price'] . "</p>";
        echo "----------------------------------";
    }
    ?>
    <br>
    <a href="logged_Index.php"> Continua ad acquistare</a>
</body>

</html>