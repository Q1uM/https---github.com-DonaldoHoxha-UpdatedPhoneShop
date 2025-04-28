<?php
session_start();
foreach ($_SESSION['errors'] as $error) {
    echo "<p style='color: red;'>$error</p>";
}
unset($_SESSION['errors']);
