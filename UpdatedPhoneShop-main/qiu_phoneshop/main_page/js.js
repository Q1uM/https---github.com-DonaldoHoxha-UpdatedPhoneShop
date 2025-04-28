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
                    fetch('search_products.php?query=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            // Update the product grid to display the products
                            updateProductGrid(data, query);
                        })
                        .catch(error => console.error('Errore:', error));
                } else {
                    // If the search-bar is null, we display every product
                    fetch('search_products.php')
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
                
            }
            document.querySelectorAll('.add-to-cart').forEach(button => {
                document.addEventListener('click', (e) => {
                    if (e.target.closest('.add-to-cart')) {
                        const button = e.target.closest('.add-to-cart');
                        const icon = button.querySelector('i');
                        icon.classList.replace('fa-cart-plus', 'fa-check-circle');
                        setTimeout(() => {
                            icon.classList.replace('fa-check-circle', 'fa-cart-plus');
                        }, 700);
                    }
                });
            });
        });
        // Add an item to the cart 
        function addItem(productId) {
            fetch('add_to_cart.php', {
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
            fetch('display_product.php', {
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