<?php
// Define available merchandise
$merchandise = [
    ['name' => 'T-Shirt', 'price' => 250, 'image' => 'images/merchandise/1_Merch.jpg', 'description' => 'Exclusive maJIStic 2k25 swags to flaunt your style. Limited stock available!', 'category' => 'Apparel'],
    ['name' => 'Cap', 'price' => 200, 'image' => 'images/merchandise/4_Merch.jpg', 'description' => 'Stay cool with this trendy maJIStic 2k25 cap.', 'category' => 'Accessories']
    
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchandise - maJIStic 2k25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/links.php'; ?>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
        }
        #header-merchant {
        background-color:rgb(17, 103, 87); /* Dark background */
        color: white; /* White text */
        text-align: center; /* Centered text */
        padding: 1rem; /* Equivalent to py-3 */
        margin-top: 50px; /* Gap top */
        position: relative;
        }
        .merchandise-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            background: rgba(255, 255, 255, 0.1); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            margin: 20px 40px; /* Add gap between cards */
        }
        .merchandise-item img {
            width: 100%;
            height: 200px; /* Fixed height for images */
            object-fit: cover; /* Ensure images cover the area */
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .merchandise-item .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex-grow: 1;
        }
        .merchandise-item .card-title {
            color: white;
            font-size: 1.25rem;
            margin-bottom: 10px;
        }
        .merchandise-item .card-text {
            color: white;
            flex-grow: 1;
            margin-bottom: 10px;
        }
        .merchandise-item .btn {
            margin-top: 10px;
        }
        .cart-box {
            background: rgba(255, 255, 255, 0.1); /* Semi-transparent background */
            backdrop-filter: blur(10px); /* Blur effect */
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            padding: 20px;
        }
        .cart-box h4 {
            margin-bottom: 20px;
        }
        .cart-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cart-box ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .cart-box ul li button {
            margin-left: 10px;
        }
        .cart-box h5 {
            margin-top: 20px;
        }
        .cart-box button {
            width: 100%;
            margin-top: 20px;
        }
        .card {
            background: none; /* Remove background */
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <!-- Header -->
    <header  id="header-merchant">
        <h1>Merchandise</h1>
        <p>Grab your exclusive swag now!</p>
    </header>

<!-- Merchandise Section -->
<div class="container my-5" style="max-width: 1200px; margin: auto; position: relative;">
    <div class="row g-4">
        <!-- Merchandise Items -->
        <div class="col-md-8">
            <div class="row g-4">
                <?php foreach ($merchandise as $item): ?>
                    <div class="col-md-6 merchandise-item" data-category="<?= $item['category'] ?>" data-name="<?= $item['name'] ?>" data-price="<?= $item['price'] ?>">
                        <div class="card h-100">
                            <img src="<?= $item['image'] ?>" class="card-img-top" alt="<?= $item['name'] ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= $item['name'] ?></h5>
                                <p class="card-text"><?= $item['description'] ?></p>
                                <h6 class="text-success">₹<?= $item['price'] ?></h6>
                                <button class="btn btn-primary w-100 add-to-cart" data-item="<?= $item['name'] ?>" data-price="<?= $item['price'] ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="cart-box">
                <h4>Your Cart:</h4>
                <ul id="cartItems" class="list-group"></ul>
                <h5 class="mt-3">Total: ₹<span id="cartTotal">0</span></h5>
                <button class="btn btn-success mt-3" 
                    data-bs-toggle="modal" 
                    data-bs-target="#checkoutModal" 
                    id="checkoutBtn" 
                    disabled 
                    style="transition: transform 0.2s; cursor: pointer;"
                    onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.2)';"
                    onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                    Proceed to Checkout
                </button>
            </div>
        </div>
    </div>
</div>
        <!-- Checkout Modal -->
        <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="color: red; opacity: 1;"></button>
                    </div>
                    <div class="modal-body">
                        <form id="checkoutForm" method="POST">
                            <div class="mb-3">
                                <label for="buyerName" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="buyerName" name="buyer_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="JIS_ID" class="form-label">JIS_ID</label>
                                <input type="text" class="form-control" id="jis_id" name="jis_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="buyerEmail" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="buyerEmail" name="buyer_email" required>
                            </div>
                            
                            <input type="hidden" name="cart_data" id="cartData">
                            <button type="submit" 
                                class="btn btn-success w-100" 
                                style="transition: transform 0.2s; cursor: pointer;"
                                onmouseover="this.style.transform='scale(1.1)'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.2)';"
                                onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='none';">
                                Proceed to Pay
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <!-- Footer -->
    
    <?php include 'includes/footer.php'; ?>
  
    <?php include 'includes/scripts.php'; ?>
    <!--  
    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; 2025 maJIStic 2k25. All Rights Reserved.</p>
    </footer> -->
   
     
    <script>
        // Cart management with reduce item quantity
        const cart = [];
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function () {
                const itemName = this.getAttribute('data-item');
                const itemPrice = parseInt(this.getAttribute('data-price'));
                // Check if item already exists in the cart
                const existingItem = cart.find(item => item.name === itemName);
                if (existingItem) {
                    existingItem.quantity++;
                } else {
                    cart.push({ name: itemName, price: itemPrice, quantity: 1 });
                }
                updateCart();
            });
        });
        // Update cart UI
        function updateCart() {
            const cartItemsContainer = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');
            cartItemsContainer.innerHTML = '';
            let total = 0;
            cart.forEach(item => {
                total += item.price * item.quantity;
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                li.innerHTML = `${item.name} - ₹${item.price} x ${item.quantity}
                                <button class="btn btn-danger btn-sm reduce-quantity" data-item="${item.name}">-</button>`;
                cartItemsContainer.appendChild(li);
            });
            cartTotal.textContent = total;
            checkoutBtn.disabled = cart.length === 0;
            // Pass cart data to hidden input
            document.getElementById('cartData').value = JSON.stringify(cart);
            // Handle reduce item quantity
            document.querySelectorAll('.reduce-quantity').forEach(button => {
                button.addEventListener('click', function () {
                    const itemName = this.getAttribute('data-item');
                    const item = cart.find(item => item.name === itemName);
                    if (item.quantity > 1) {
                        item.quantity--;
                    } else {
                        const index = cart.findIndex(item => item.name === itemName);
                        cart.splice(index, 1); // Remove item if quantity is 1
                    }
                    updateCart();
                });
            });
        }
        // Filter and Search functionality
        document.getElementById('filterCategory').addEventListener('change', updateFilter);
        document.getElementById('searchText').addEventListener('input', updateFilter);
        function updateFilter() {
            const selectedCategory = document.getElementById('filterCategory').value;
            const searchText = document.getElementById('searchText').value.toLowerCase();
            document.querySelectorAll('.merchandise-item').forEach(item => {
                const itemCategory = item.dataset.category;
                const itemName = item.dataset.name.toLowerCase();
                const itemDescription = item.querySelector('.card-text').textContent.toLowerCase();
                if ((selectedCategory === 'all' || itemCategory === selectedCategory) &&
                    (itemName.includes(searchText) || itemDescription.includes(searchText))) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        // Sorting functionality
        document.getElementById('sortOption').addEventListener('change', function () {
            const sortOption = this.value;
            const items = Array.from(document.querySelectorAll('.merchandise-item'));
            let sortedItems = [];
            switch (sortOption) {
                case 'priceAsc':
                    sortedItems = items.sort((a, b) => a.dataset.price - b.dataset.price);
                    break;
                case 'priceDesc':
                    sortedItems = items.sort((a, b) => b.dataset.price - a.dataset.price);
                    break;
                case 'nameAsc':
                    sortedItems = items.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
                    break;
                case 'nameDesc':
                    sortedItems = items.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
                    break;
                default:
                    sortedItems = items;
                    break;
            }
            // Reorder the items in the DOM
            const container = document.getElementById('merchandiseContainer');
            sortedItems.forEach(item => {
                container.appendChild(item);
            });
        });

        // Modify form action to include JIS_ID in the URL
        document.getElementById('checkoutForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const jisId = document.getElementById('jis_id').value;
            this.action = merchant_payment.php?jis_id=${jisId};
            this.submit();
        });
    </script>
</body>
</html>