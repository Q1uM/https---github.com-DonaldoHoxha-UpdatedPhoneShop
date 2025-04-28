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
    <title>Carrello</title>
    <style>
        table,
        th,
        td {
            border: 1px black solid;
            border-collapse: collapse;
        }
    </style>
    <script>
        // Function to buy a singular item from the cart
        function buyItem(productId) {
            fetch('../back-end/buy_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        location.reload();
                    } else {
                        alert("Errore : " + data.message);
                    }
                })
                .catch(error => console.error('Errore: ', error));
        }
        // Function to remove an item ( if there is an item which has a quantity bigger than 1, it reduces it)
        function removeItem(productId) {
            // Sending a POST form to send the product_id to the remove_from_cart page
            fetch('../back-end/remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        // Reloads the cart 
                        location.reload();
                    } else {
                        alert("Errore: " + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }
        // Function to buy all the items in the cart
        function buyCart() {
            fetch('../back-end/buy_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        location.reload();
                    } else {
                        alert("Errore: " + data.message)
                    }
                })
                .catch(error => console.error('Errore:', error));
        }
    </script>
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

    // We get the ID, name, quantity and price of the items in the cart
    $stmt = $conn->prepare("SELECT c.product_id, p.name, c.quantity, p.price  FROM product p JOIN cart c on p.id = c.product_id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    //simple print to be updated
    echo "<table>";
    echo "<tr>";
    echo "<th>Prodotto</th>";
    echo "<th>Quantità</th>";
    echo "<th>Prezzo</th>";
    echo "</tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['quantity'] . "</td>";
        echo "<td>" . $row['price'] . "</td>";
        echo "<td><button onclick='buyItem(" . $row['product_id'] . ")'> Compra oggetto</button></td>";
        echo "<td><button onclick='removeItem(" . $row['product_id'] . ")'> Rimuovi oggetto </button></td>";
        echo "</tr>";
    }
    echo "</table>";
    // printing the total price of the cart
    $stmt = $conn->prepare("SELECT SUM(p.price * c.quantity) as total FROM cart c join product p on c.product_id = p.id where c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "<p>Totale: " . $row['total'] . "</p>";
    }
    ?>
    <button onclick="buyCart()">buy cart</button>
    <br>
    <a href="logged_Index.php"> Continua ad acquistare</a>
</body>

</html>