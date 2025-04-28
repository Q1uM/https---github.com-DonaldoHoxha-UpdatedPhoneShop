<?php
// We check if the user has logged in 
session_start();
if (!isset($_SESSION['username'])) {
    // Session doesn't exist, check for cookies
    if (isset($_COOKIE['user'])) {
        // Validate these cookies (potentially against database)
        // If valid, recreate the session
        $_SESSION['username'] = $_COOKIE['user'];
        // Additional session setup as needed
        header("Location: logged_Index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechPhone - Negozio di Smartphone</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <!-- Header -->
    <header class="main-header">
        <div class="header-top">
            <div class="logo-container">
                <img src="logo.png" alt="TechPhone Logo" class="logo">
                <h1>TechPhone</h1>
            </div>
            <div class="search-container">
                <input type="search" placeholder="Cerca smartphone..." class="search-bar">
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <div class="login-register">
                    <a href="../../login&register/login&register.html"><button class="login">Login</button></a>
                    <a href="../../login&register/login&register.html"><button class="register">SignUp</button></a>
                </div>
                <button class="user-btn"><i class="fas fa-user"></i></button>
                <button class="cart-btn"><i class="fas fa-shopping-cart"></i><span class="cart-count">0</span></button>
            </div>
        </div>
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="#novita">Novità</a></li>
                <li><a href="#brand">Brand</a></li>
                <li><a href="#offerte">Offerte</a></li>
                <li><a href="#usato">Usato Certificato</a></li>
                <li><a href="#assistenza">Assistenza</a></li>
            </ul>
        </nav>
    </header>
    <!-- Main Content -->
    <main class="content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h2>Ultimi Modelli 2024</h2>
                <p>Scopri le novità dei principali brand</p>
                <button class="cta-btn">Scopri ora</button>
            </div>
        </section>
        <!-- Products Grid -->
        <section class="products-grid">

            <?php
            include '../back-end/db_conn.php';
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("SELECT * FROM product");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {

                echo "<article class='product-card'>";
                echo "<div class='product-info'>";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<p class='product-price'>€" . $row['price'] . "</p>";
                echo "<div class='product-actions'>";
                echo "<button class='quick-view'><i class='fas fa-eye'></i></button>";

                echo "<button class='add-to-cart' onclick='addItem(" . $row['id'] . ")'><i class='fas fa-cart-plus'></i></button>";

                echo "</div>";
                echo "</div>";
                echo "</article>";
            }
            ?>


            <!-- Altri prodotti... -->
        </section>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-grid">
            <div class="footer-section">
                <h4>Contatti</h4>
                <ul>
                    <li><i class="fas fa-phone"></i> +39 02 1234567</li>
                    <li><i class="fas fa-envelope"></i> info@techphone.it</li>
                    <li><i class="fas fa-map-marker-alt"></i> Milano, Via Roma 123</li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Servizi</h4>
                <ul>
                    <li>Garanzia Estesa</li>
                    <li>Ritiro Gratuito</li>
                    <li>Finanziamenti</li>
                </ul>
            </div>

            <div class="footer-section">
                <h4>Social</h4>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>