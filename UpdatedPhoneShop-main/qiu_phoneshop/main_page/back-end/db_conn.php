<?php
// Implementable php file for the database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "phoneshop";

$conn = new mysqli($servername, $db_username, $db_password, $dbname);
