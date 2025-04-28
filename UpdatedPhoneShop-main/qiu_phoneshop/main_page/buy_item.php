<?php
// Start the session
session_start();
// Include database connection
include 'db_conn.php';

// Specify the type of the content received 
header('Content-Type: application/json');

// Check if there is a user problem
if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Utente non autenticato"]);
    exit();
}

// Get the user ID
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id, shipping_address FROM user WHERE username = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Errore nel preparare la query"]);
    exit();
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["status" => "error", "message" => "Utente non trovato"]);
    exit();
}

$user_id = $user['id'];
$shipping_address = $user['shipping_address'];

// Inizia una transazione
$conn->begin_transaction();

try {
    // Recupera il prodotto nel carrello
    $stmt = $conn->prepare("SELECT c.quantity, p.price FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = ? and c.product_id = ?");
    $stmt->bind_param("ii", $user_id, $_POST['product_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        throw new Exception("Prodotto non trovato nel carrello");
    }

    $quantity = $row['quantity'];
    $total_price = $row['price']; // Prezzo per un singolo prodotto

    // Inserisci l'ordine
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, total_price, shipping_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $user_id, $_POST['product_id'], $total_price, $shipping_address);
    $stmt->execute();

    // Gestisci la quantità nel carrello
    if ($quantity == 1) {
        // Se la quantità è 1, elimina la riga
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $_POST['product_id']);
        $stmt->execute();
    } else {
        // Altrimenti decrementa la quantità
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $user_id, $_POST['product_id']);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Prodotto acquistato"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Errore: " . $e->getMessage()]);
}
