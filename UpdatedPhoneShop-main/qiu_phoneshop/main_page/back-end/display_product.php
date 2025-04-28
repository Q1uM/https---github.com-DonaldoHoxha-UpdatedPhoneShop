<?php
include 'db_conn.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set content type to JSON
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT * FROM product WHERE id = ?");
$stmt->bind_param("i", $_POST['product_id']);
$stmt->execute();
$result = $stmt->get_result();
// We prepare the query to return in JSON 
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}


// Return the products
echo json_encode($products);
// Close the connection
$stmt->close();
$conn->close();
