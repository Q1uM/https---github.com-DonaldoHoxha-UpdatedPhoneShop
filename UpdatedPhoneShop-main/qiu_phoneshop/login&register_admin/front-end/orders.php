<?php
//tells the browser that the content is JSON,not HTML
header('Content-Type: application/json; charset=utf-8');

include 'db_conn.php';


if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "Select * from orders";
$result = $conn->query($sql);
$data = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    header("HTTP/1.1 404 Not Found");
}
json_encode($data, JSON_PRETTY_PRINT);
$conn->close();