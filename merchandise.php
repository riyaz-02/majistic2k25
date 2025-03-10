<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merchandise - maJIStic 2k25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php include 'includes/links.php'; ?>

    <style>
        :root {
            --primary: #116757;
            --secondary: #15a88a;
            --accent: #f5a623;
            --dark: #121212;
            --light: #ffffff;
            --gray: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #121212, #242424);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--light);
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 80% 20%, rgba(21, 168, 138, 0.15), transparent 40%),
                        radial-gradient(circle at 20% 80%, rgba(245, 166, 35, 0.15), transparent 40%);
            z-index: -1;
        }
        
        .page-header {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            padding: 3rem 0;
            margin-top: 50px;
            text-align: center;
            border-radius: 0.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            z-index: 0;
        }
        
        .page-header h1, .page-header p {
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        .page-header h1 {
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            font-size: 1.2rem;
            font-weight: 300;
            opacity: 0.9;
        }
        
        .filters-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .filters-section .form-control,
        .filters-section .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--light);
            font-weight: 400;
            padding: 0.7rem 1rem;
            border-radius: 0.5rem;
        }
        
        .filters-section .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        
        .filters-section .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
        }
        
        .filters-section label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .product-card .badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 2rem;
            z-index: 5;
        }
        
        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 250px;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.1);
        }
        
        .product-info {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
            gap: 1rem;
        }
        
        .product-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
            color: var(--light);
        }
        
        .product-category {
            color: var(--secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }
        
        .product-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1rem;
        }
        
        .price-currency {
            font-size: 1rem;
            font-weight: 400;
            vertical-align: super;
            margin-right: 0.2rem;
        }
        
        .add-to-cart-btn {
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border: none;
            color: var(--light);
            font-weight: 600;
            padding: 0.8rem 1.5rem;
            border-radius: 0.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .add-to-cart-btn:hover {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .add-to-cart-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .add-to-cart-btn:hover::before {
            left: 100%;
        }
        
        .cart-container {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            padding: 2rem;
            height: 100%;
            position: sticky;
            top: 2rem;
            display: flex;
            flex-direction: column;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cart-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cart-empty {
            text-align: center;
            padding: 2rem 0;
            color: rgba(255, 255, 255, 0.5);
            font-style: italic;
        }
        
        .cart-items {
            flex-grow: 1;
            margin-bottom: 1.5rem;
            max-height: 350px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .cart-items::-webkit-scrollbar {
            width: 5px;
        }
        
        .cart-items::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }
        
        .cart-items::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
        
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cart-item-info {
            flex-grow: 1;
        }
        
        .cart-item-name {
            font-weight: 500;
            margin-bottom: 0.3rem;
        }
        
        .cart-item-price {
            color: var(--accent);
            font-size: 0.9rem;
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--light);
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        
        .quantity-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .cart-item-quantity span {
            font-weight: 500;
            min-width: 25px;
            text-align: center;
        }
        
        .cart-summary {
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .cart-total-label {
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .cart-total-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }
        
        .checkout-btn {
            background: linear-gradient(90deg, var(--accent), #d48e1e);
            border: none;
            color: var(--dark);
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .checkout-btn:enabled:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .checkout-btn:disabled {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .modal-content {
            background: #1e1e1e;
            color: var(--light);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
        }
        
        .modal-header .btn-close {
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            opacity: 1;
        }
        
        .modal-title {
            font-weight: 700;
            color: var(--light);
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--light);
            padding: 0.8rem 1rem;
            border-radius: 0.5rem;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--secondary);
            color: var(--light);
            box-shadow: 0 0 0 0.25rem rgba(21, 168, 138, 0.25);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        
        .pay-btn {
            background: linear-gradient(90deg, var(--accent), #d48e1e);
            border: none;
            color: var(--dark);
            font-weight: 700;
            padding: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }
        
        .pay-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 991.98px) {
            .cart-container {
                position: static;
                margin-top: 2rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .page-header {
                padding: 2rem 0;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .page-header p {
                font-size: 1rem;
            }
            
            .product-image-container {
                height: 200px;
            }
            
            .product-title {
                font-size: 1.3rem;
            }
            
            .product-price {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .filters-section {
                padding: 1rem;
            }
            
            .product-card {
                margin-bottom: 1.5rem;
            }
        }

        /* Notification Style */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #15a88a;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Animation for Add to Cart Button */
        .animate-click {
            animation: clickEffect 0.3s ease;
        }

        @keyframes clickEffect {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Merchandise Data -->
    <?php
    $merchandise = [
        [
            'name' => 'maJIStic 2k25 T-Shirt',
            'category' => 'Apparel',
            'description' => 'Official maJIStic 2k25 T-Shirt with vibrant design. Made of 100% cotton for ultimate comfort.',
            'price' => 599,
            'image' => 'https://via.placeholder.com/300x250.png?text=T-Shirt'
        ],
        [
            'name' => 'maJIStic Hoodie',
            'category' => 'Apparel',
            'description' => 'Stay warm with this stylish maJIStic 2k25 hoodie. Perfect for the fest season!',
            'price' => 1299,
            'image' => 'https://via.placeholder.com/300x250.png?text=Hoodie'
        ],
        [
            'name' => 'Event Cap',
            'category' => 'Accessories',
            'description' => 'Limited edition cap with maJIStic 2k25 logo. Adjustable fit for all sizes.',
            'price' => 399,
            'image' => 'https://via.placeholder.com/300x250.png?text=Cap'
        ],
        [
            'name' => 'maJIStic Tote Bag',
            'category' => 'Accessories',
            'description' => 'Eco-friendly tote bag with maJIStic 2k25 branding. Perfect for carrying your essentials.',
            'price' => 299,
            'image' => 'https://via.placeholder.com/300x250.png?text=Tote+Bag'
        ],
        [
            'name' => 'Festival Wristband',
            'category' => 'Accessories',
            'description' => 'Exclusive wristband for maJIStic 2k25 attendees. Show your fest spirit!',
            'price' => 199,
            'image' => 'https://via.placeholder.com/300x250.png?text=Wristband'
        ],
        [
            'name' => 'Graphic Tee',
            'category' => 'Apparel',
            'description' => 'A trendy graphic tee with unique maJIStic 2k25 artwork. Limited stock!',
            'price' => 699,
            'image' => 'https://via.placeholder.com/300x250.png?text=Graphic+Tee'
        ]
    ];
    ?>

    <!-- Page Header -->
    <header class="container page-header">
        <h1><i class="fas fa-tshirt me-2"></i>EXCLUSIVE MERCHANDISE</h1>
        <p>Grab your limited-edition maJIStic 2k25 swag and make a statement!</p>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Filters Section -->
        <div class="filters-section mb-4">
            <div class="row">
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="searchText" class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-white-50"></i></span>
                        <input type="text" class="form-control" id="searchText" placeholder="Search merchandise...">
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="filterCategory" class="form-label">Category</label>
                    <select class="form-select" id="filterCategory">
                        <option value="all" selected>All Categories</option>
                        <option value="Apparel">Apparel</option>
                        <option value="Accessories">Accessories</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sortOption" class="form-label">Sort By</label>
                    <select class="form-select" id="sortOption">
                        <option value="featured" selected>Featured</option>
                        <option value="priceAsc">Price: Low to High</option>
                        <option value="priceDesc">Price: High to Low</option>
                        <option value="nameAsc">Name: A to Z</option>
                        <option value="nameDesc">Name: Z to A</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Products List -->
            <div class="col-lg-8">
                <div class="row g-4" id="merchandiseContainer">
                    <?php foreach ($merchandise as $index => $item): ?>
                    <div class="col-md-6 mb-4 merchandise-item" data-category="<?= $item['category'] ?>" data-name="<?= $item['name'] ?>" data-price="<?= $item['price'] ?>">
                        <div class="product-card">
                            <?php if ($index === 0): ?>
                            <span class="badge">New Arrival</span>
                            <?php endif; ?>
                            <div class="product-image-container">
                                <img src="<?= $item['image'] ?>" class="product-image" alt="<?= $item['name'] ?>">
                            </div>
                            <div class="product-info">
                                <div>
                                    <div class="product-category"><?= $item['category'] ?></div>
                                    <h3 class="product-title"><?= $item['name'] ?></h3>
                                    <p class="product-description"><?= $item['description'] ?></p>
                                </div>
                                <div>
                                    <div class="product-price"><span class="price-currency">₹</span><?= $item['price'] ?></div>
                                    <button class="add-to-cart-btn w-100 add-to-cart" data-item="<?= $item['name'] ?>" data-price="<?= $item['price'] ?>">
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cart -->
            <div class="col-lg-4">
                <div class="cart-container">
                    <h3 class="cart-title"><i class="fas fa-shopping-bag me-2"></i>Your Cart</h3>
                    <div class="cart-items" id="cartItems">
                        <div class="cart-empty" id="emptyCart">
                            <i class="fas fa-shopping-cart mb-3" style="font-size: 2.5rem; opacity: 0.3;"></i>
                            <p>Your cart is empty</p>
                        </div>
                    </div>
                    <div class="cart-summary">
                        <div class="cart-total">
                            <span class="cart-total-label">Total</span>
                            <span class="cart-total-amount">₹<span id="cartTotal">0</span></span>
                        </div>
                        <button class="checkout-btn w-100" id="checkoutBtn" data-bs-toggle="modal" data-bs-target="#checkoutModal" disabled>
                            <i class="fas fa-lock me-2"></i>Checkout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel"><i class="fas fa-shopping-bag me-2"></i>Complete Your Purchase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="checkoutForm" method="POST">
                        <div class="mb-3">
                            <label for="buyerName" class="form-label">Your Name</label>
                            <input type="text" class="form-control" id="buyerName" name="buyer_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="jis_id" class="form-label">JIS ID</label>
                            <input type="text" class="form-control" id="jis_id" name="jis_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="buyerEmail" class="form-label">Your Email</label>
                            <input type="email" class="form-control" id="buyerEmail" name="buyer_email" required>
                        </div>
                        
                        <input type="hidden" name="cart_data" id="cartData">
                        <button type="submit" class="pay-btn w-100">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Payment
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    
    <script>
        // Cart management
        const cart = [];
        const emptyCartElement = document.getElementById('emptyCart');
        
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                // Add animation effect
                this.classList.add('animate-click');
                setTimeout(() => this.classList.remove('animate-click'), 300);
                
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
                
                // Show notification
                showNotification(`${itemName} added to cart!`);
            });
        });
        
        // Update cart UI
        function updateCart() {
            const cartItemsContainer = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            // Clear cart items
            while (cartItemsContainer.firstChild) {
                cartItemsContainer.removeChild(cartItemsContainer.firstChild);
            }
            
            // Show/hide empty cart message
            if (cart.length === 0) {
                cartItemsContainer.appendChild(emptyCartElement);
                checkoutBtn.disabled = true;
            } else {
                emptyCartElement.remove();
                checkoutBtn.disabled = false;
                
                let total = 0;
                
                // Add cart items
                cart.forEach(item => {
                    total += item.price * item.quantity;
                    
                    const cartItem = document.createElement('div');
                    cartItem.className = 'cart-item';
                    
                    cartItem.innerHTML = `
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-price">₹${item.price.toLocaleString()}</div>
                        </div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn reduce-quantity" data-item="${item.name}">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span>${item.quantity}</span>
                            <button class="quantity-btn add-quantity" data-item="${item.name}">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    `;
                    
                    cartItemsContainer.appendChild(cartItem);
                });
                
                // Update total
                cartTotal.textContent = total.toLocaleString();
                
                // Pass cart data to hidden input
                document.getElementById('cartData').value = JSON.stringify(cart);
                
                // Handle quantity buttons
                document.querySelectorAll('.reduce-quantity').forEach(button => {
                    button.addEventListener('click', function() {
                        const itemName = this.getAttribute('data-item');
                        const item = cart.find(item => item.name === itemName);
                        
                        if (item.quantity > 1) {
                            item.quantity--;
                        } else {
                            const index = cart.findIndex(item => item.name === itemName);
                            cart.splice(index, 1);
                        }
                        
                        updateCart();
                    });
                });
                
                document.querySelectorAll('.add-quantity').forEach(button => {
                    button.addEventListener('click', function() {
                        const itemName = this.getAttribute('data-item');
                        const item = cart.find(item => item.name === itemName);
                        item.quantity++;
                        updateCart();
                    });
                });
            }
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
                const itemDescription = item.querySelector('.product-description').textContent.toLowerCase();
                
                if ((selectedCategory === 'all' || itemCategory === selectedCategory) &&
                    (itemName.includes(searchText) || itemDescription.includes(searchText))) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        // Sorting functionality
        document.getElementById('sortOption').addEventListener('change', function() {
            const sortOption = this.value;
            const items = Array.from(document.querySelectorAll('.merchandise-item'));
            const container = document.getElementById('merchandiseContainer');
            
            let sortedItems = [];
            
            switch (sortOption) {
                case 'priceAsc':
                    sortedItems = items.sort((a, b) => parseInt(a.dataset.price) - parseInt(b.dataset.price));
                    break;
                case 'priceDesc':
                    sortedItems = items.sort((a, b) => parseInt(b.dataset.price) - parseInt(a.dataset.price));
                    break;
                case 'nameAsc':
                    sortedItems = items.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
                    break;
                case 'nameDesc':
                    sortedItems = items.sort((a, b) => b.dataset.name.localeCompare(a.dataset.name));
                    break;
                case 'featured':
                default:
                    sortedItems = items.sort((a, b) => {
                        // Assuming the original order in the PHP array is the "featured" order
                        return items.indexOf(a) - items.indexOf(b);
                    });
                    break;
            }
            
            // Clear and re-append sorted items
            container.innerHTML = '';
            sortedItems.forEach(item => container.appendChild(item));
            
            // Reapply filter after sorting
            updateFilter();
        });

        // Notification function
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>