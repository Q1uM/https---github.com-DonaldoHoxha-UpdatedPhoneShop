<?php
// Start the session at the very beginning
session_start();
// Check if the user is logged in via session
if (!isset($_SESSION['username'])) {
    // Session doesn't exist, check for cookies
    if (isset($_COOKIE['user'])) {
        // Validate the cookie against the database for security
        include 'db_conn.php';
        $stmt = $conn->prepare("SELECT username FROM user WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $_COOKIE['user']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                // Cookie is valid, recreate the session
                $_SESSION['username'] = $_COOKIE['user'];
                // Regenerate session ID for security
                session_regenerate_id(true);
                $stmt->close();
            } else {
                // Invalid cookie, clear it and redirect
                setcookie("user", "", time() - 3600, "/");
                header('Location: ../login&register/login&register.html?error=invalid_cookie');
                exit();
            }
            $conn->close();
        } else {
            // Database error
            header('Location: ../login&register/login&register.html?error=db_error');
            exit();
        }
    } else {
        // No session or cookies, redirect to login
        header('Location: ../login&register/login&register.html');
        exit();
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
                <input type="search" id="search-input" placeholder="Cerca smartphone..." class="search-bar">
                <button id="search-btn" class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <a href="../login&register/logout.php"><button class="logout">Logout</button></a>
                <button class="user-btn"><i class="fas fa-user">
                        <div class="proflie-img">
                            <div class="profile-options">
                                <a href="profile.php" class="profile-link">Profile</a>
                                <a href="orders.php" class="profile-link">Orders</a>
                                <a href="/settings" class="profile-link">Settings</a>
                                <a href="../login&register/logout.php" class="profile-link">Logout</a>
                            </div>
                        </div>
                    </i></button>
                <a href="cart.php">
                    <button class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                            <?php
                            include 'db_conn.php';
                            if ($conn->connect_error) {
                                die("Connection failed: " . $conn->connect_error);
                            }
                            // Ottieni l'ID dell'utente
                            $username = $_SESSION['username'];
                            $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user = $result->fetch_assoc();
                            if (!$user) {
                                echo json_encode(["status" => "error", "message" => "Utente non trovato"]);
                                exit();
                            }
                            $user_id = $user['id'];
                            $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?;");
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            if ($row['total'] > 0) {
                                echo $row['total'];
                            } else {
                                echo "0";
                            }
                            ?>
                        </span></button></a>
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
    <?php
    ?>

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
            $stmt = $conn->prepare("SELECT * FROM product");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<article class='product-card'>";
                echo "<div class='product-info'>";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<p class='product-price'>€" . $row['price'] . "</p>";
                echo "<div class='product-actions'>";
                echo "<button class='quick-view' onclick='showDesc(" . $row['id'] . ")'><i class='fas fa-eye'></i></button>";
                echo "<button class='add-to-cart' onclick='addItem(" . $row['id'] . ")'><i class='fas fa-cart-plus'></i></button>";
                echo "</div>";
                echo "</div>";
                echo "</article>";
            }
            ?>
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
    <script src="js.js"></script>
</body>

</html>
