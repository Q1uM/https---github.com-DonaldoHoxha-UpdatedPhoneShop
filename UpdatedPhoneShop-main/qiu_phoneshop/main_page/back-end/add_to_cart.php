<?php
// We check if the user has logged in
session_start();
include 'db_conn.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Utente non autenticato"]);
    exit();
}

// Get the user ID
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// If we didn't find a user we send back a error message
if (!$user) {
    echo json_encode(["status" => "error", "message" => "Utente non trovato"]);
    exit();
}

$user_id = $user['id'];

// Get the product ID from the POST
if (!isset($_POST['product_id'])) {
    echo json_encode(["status" => "error", "message" => "ID prodotto non valido"]);
    exit();
}

$product_id = intval($_POST['product_id']);

// Check if the product is already in the cart
$check_stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check_stmt->bind_param("ii", $user_id, $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // If it is already in the cart, we update the quantity
    $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
    $update_stmt->bind_param("ii", $user_id, $product_id);
    if ($update_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prodotto aggiornato nel carrello"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nell'aggiornamento del carrello"]);
    }
} else {
    // If it is not, we insert it
    $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    if ($insert_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prodotto aggiunto al carrello"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nell'aggiunta al carrello"]);
    }
}
$conn->close();
