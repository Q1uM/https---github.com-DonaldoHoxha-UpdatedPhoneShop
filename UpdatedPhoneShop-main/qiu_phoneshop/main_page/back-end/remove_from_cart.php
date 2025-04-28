<?php
// We check if the user has logged in
session_start();
include 'db_conn.php';

// Set content type to JSON
header('Content-Type: application/json');

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

// Removing an item from the cart
$check_stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
$check_stmt->bind_param("ii", $user_id, $product_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$row = $check_result->fetch_assoc();

// If there is more than one item in the cart, we update the quantity
if ($row && $row['quantity'] > 1) {
    $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
    $update_stmt->bind_param("ii", $user_id, $product_id);
    if ($update_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prodotto rimosso dal carrello"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nell'aggiornamento del carrello"]);
    }
} else {
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
    $delete_stmt->bind_param("ii", $user_id, $product_id);
    if ($delete_stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Prodotto rimosso dal carrello"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Errore nell'aggiornamento del carrello"]);
    }
}
