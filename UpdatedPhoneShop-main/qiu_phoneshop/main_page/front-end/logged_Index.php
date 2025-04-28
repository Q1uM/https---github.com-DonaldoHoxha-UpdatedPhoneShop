<?php
// Start the session at the very beginning
session_start();
// Check if the user is logged in via session
if (!isset($_SESSION['username'])) {
    // Session doesn't exist, check for cookies
    if (isset($_COOKIE['user'])) {
        // Validate the cookie against the database for security
        include '../back-end/db_conn.php';
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
                header('Location: ../../login&register/login&register.html?error=invalid_cookie');
                exit();
            }
            $conn->close();
        } else {
            // Database error
            header('Location: ../../login&register/login&register.html?error=db_error');
            exit();
        }
    } else {
        // No session or cookies, redirect to login
        header('Location: ../../login&register/login&register.html');
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const profileContainer = document.querySelector('.user-btn');
            const profileOptions = document.querySelector('.profile-options');
            let hoverTimeout;
            // Show on hover
            profileContainer.addEventListener('mouseenter', () => {
                clearTimeout(hoverTimeout);
                profileOptions.classList.add('show');
            });
            // Hide with delay
            profileContainer.addEventListener('mouseleave', () => {
                hoverTimeout = setTimeout(() => {
                    profileOptions.classList.remove('show');
                }, 300);
            });
            // Keep open if hovering over options
            profileOptions.addEventListener('mouseenter', () => {
                clearTimeout(hoverTimeout);
                profileOptions.classList.add('show');
            })
            profileOptions.addEventListener('mouseleave', () => {
                hoverTimeout = setTimeout(() => {
                    profileOptions.classList.remove('show');
                }, 200);
            });
            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!profileContainer.contains(e.target)) {
                    profileOptions.classList.remove('show');
                }
            });

            // Search bar 
            const searchInput = document.getElementById("search-input");
            const searchBtn = document.getElementById('search-btn');

            // Function to execute the search when we click the search icon
            searchBtn.addEventListener('click', performSearch);

            // Function to execute the search when we press the enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Function to perform the search
            function performSearch() {
                // We first need the query that the user wrote and we trim it for eventual spaces
                const query = searchInput.value.trim();
                if (query !== '') {
                    // AJAX call to search the prdoducts
                    fetch('../back-end/search_products.php?query=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            // Update the product grid to display the products
                            updateProductGrid(data, query);
                        })
                        .catch(error => console.error('Errore:', error));
                } else {
                    // If the search-bar is null, we display every product
                    fetch('../back-end/search_products.php')
                        .then(response => response.json())
                        .then(data => {
                            updateProductGrid(data, '');
                        })
                        .catch(error => console.error('Errore:', error));
                }
            }

            // Function to update the product grid
            function updateProductGrid(products, query) {
                const productsGrid = document.querySelector('.products-grid');

                // Clean the attual grid
                productsGrid.innerHTML = '';

                // If there are products that match the search, we display them
                if (products.length > 0) {
                    // Add a header if it is a search
                    if (query) {
                        const searchHeader = document.createElement('div');
                        searchHeader.className = 'search-results-header';
                        searchHeader.innerHTML = `<h2>Risultati per: "${query}"</h2>`;
                        productsGrid.before(searchHeader);
                        // Remove eventual headers of prior searches
                        const oldHeaders = document.querySelectorAll('.search-results-header');
                        if (oldHeaders.length > 1) {
                            for (let i = 0; i < oldHeaders.length - 1; i++) {
                                oldHeaders[i].remove();
                            }
                        }
                    } else {
                        // Remove eventual search header if we display every product
                        const oldHeaders = document.querySelectorAll('.search-results-header');
                        oldHeaders.forEach(header => header.remove());
                    }
                    // Add the products to the grid
                    products.forEach(product => {
                        const productCard = document.createElement('article');
                        productCard.className = 'product-card';
                        productCard.innerHTML = `
                    <div class="product-info">
                        <h3>${product.name}</h3>
                        <p class="product-price">€${product.price}</p>
                        <div class="product-actions">
                            <button class="quick-view" onclick="showDesc(${product.id})"><i class="fas fa-eye"></i></button>
                            <button class="add-to-cart" onclick="addItem(${product.id})">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>`;
                        productsGrid.appendChild(productCard);
                    });
                } else {
                    // If there aren't products that match the search, display a message
                    productsGrid.innerHTML = `
                <div class="no-results">
                    <h3>Nessun prodotto trovato per: "${query}"</h3>
                    <button class="cta-btn" onclick="document.getElementById('search-input').value='';
                    document.getElementById('search-btn').click();">
                    Mostra tutti i prodotti
                    </button>
                </div>`;
                }
                //change the add-to-cart's icon status to checked after the click, and return to before when mouse left
                document.querySelectorAll('.add-to-cart').forEach(button => {
                    const icon = button.querySelector('i');
                    const originalIconClass = 'fa-cart-plus';
                    const clickedIconClass = 'fa-check-circle';
                    document.addEventListener('click', (e) => {
                        if (event.target.closest('.add-to-cart')) {
                            const button = event.target.closest('.add-to-cart');
                            const icon = button.querySelector('i');
                            icon.classList.replace('fa-cart-plus', 'fa-check-circle');
                            setTimeout(() => {
                                icon.classList.replace('fa-check-circle', 'fa-cart-plus');
                            }, 700);
                        }
                    });
                });
            }
        });
        // Add an item to the cart 
        function addItem(productId) {
            fetch('../back-end/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        // update the cart items count
                        let cartCount = document.querySelector('.cart-count');
                        cartCount.textContent = parseInt(cartCount.textContent) + 1;
                    } else {
                        alert("Errore: " + data.message);
                    }
                })
                .catch(error => console.error('Errore:', error));
        }
        // Function to show the description of the selected phone
        function showDesc(productId) {
            fetch('../back-end/display_product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'product_id=' + encodeURIComponent(productId)
                })
                .then(response => response.json())
                .then(data => {
                    showDescInGrid(data);
                })
        }

        function showDescInGrid(productDesc) {
            const productsGrid = document.querySelector('.products-grid');
            productDesc.forEach(product => {
                productsGrid.innerHTML =
                    `
                <p>Articolo: ${product.name}</p>
                <br>
                <p>Ram: ${product.ram}</p>
                <br>
                <p>Rom: ${product.rom}</p>
                <br>
                <p>Battery: ${product.battery}</p>
                <br>
                <p>Camera: ${product.camera}</p>
                <br>
                <p>Price: ${product.price}</p>
                <button class="cta-btn" onclick="document.getElementById('search-input').value='';
                    document.getElementById('search-btn').click();">
                    Mostra tutti i prodotti
                    </button>
                `
            });
        }
    </script>
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
                <a href="../../login&register/logout.php"><button class="logout">Logout</button></a>
                <button class="user-btn"><i class="fas fa-user">
                        <div class="proflie-img">
                            <div class="profile-options">
                                <a href="profile.php" class="profile-link">Profile</a>
                                <a href="orders.php" class="profile-link">Orders</a>
                                <a href="/settings" class="profile-link">Settings</a>
                                <a href="../../login&register/logout.php" class="profile-link">Logout</a>
                            </div>
                        </div>
                    </i></button>
                <a href="cart.php">
                    <button class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                            <?php
                            include '../back-end/db_conn.php';
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
</body>

</html>