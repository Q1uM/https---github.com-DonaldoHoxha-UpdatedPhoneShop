<?php
include 'db_conn.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set content type to JSON
header('Content-Type: application/json');

// We check if there is a query in the search
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    $stmt = $conn->prepare("SELECT * FROM product WHERE name LIKE ?");
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("s", $search_param);
} else {
    // If there isn't a query, we return all products
    $stmt = $conn->prepare("SELECT * FROM product");
}

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
