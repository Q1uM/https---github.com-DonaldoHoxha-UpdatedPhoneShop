<?php
session_start();
include 'db_conn.php';

header('Content-Type: application/json');

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
    // Recupera i prodotti nel carrello
    $stmt = $conn->prepare("SELECT c.product_id, c.quantity, p.price FROM cart c JOIN product p ON c.product_id = p.id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $all_success = true;
    $total_price = 0;

    while ($row = $result->fetch_assoc()) {
        $product_total = $row['quantity'] * $row['price'];
        $total_price += $product_total;

        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, shipping_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiids", $user_id, $row['product_id'], $row['quantity'], $product_total, $shipping_address);

        if (!$stmt->execute()) {
            $all_success = false;
            break;
        }
    }

    if ($all_success) {
        // Svuota il carrello
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(["status" => "success", "message" => "Prodotti acquistati con successo", "total_price" => $total_price]);
    } else {
        $conn->rollback();
        echo json_encode(["status" => "error", "message" => "Errore nell'acquisto del carrello"]);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Errore: " . $e->getMessage()]);
}
