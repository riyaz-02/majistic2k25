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
        'price' => 299,
        'image' => 'images/tshirt1.png',
        'image2' => 'images/tshirt2.png'
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
        <div class="row g-4">
            <?php foreach ($merchandise as $index => $item): ?>
            <!-- Product Image Card -->
            <div class="col-md-6 mb-4">
                <div class="product-image-card">
                    <?php if ($index === 0): ?>
                    <span class="badge">New Arrival</span>
                    <?php endif; ?>
                    <div class="product-image-container">
                        <div class="image-scroll-container">
                            <div class="image-scroll-wrapper">
                                <img src="<?= $item['image'] ?>" class="product-image" alt="<?= $item['name'] ?>">
                                <img src="<?= $item['image2'] ?>" class="product-image" alt="<?= $item['name'] ?>">
                                <!-- Duplicate images for infinite scroll effect -->
                                <img src="<?= $item['image'] ?>" class="product-image" alt="<?= $item['name'] ?>">
                                <img src="<?= $item['image2'] ?>" class="product-image" alt="<?= $item['name'] ?>">
                            </div>
                        </div>
                        <div class="image-scroll-controls">
                            <button class="scroll-control prev"><i class="fas fa-chevron-left"></i></button>
                            <button class="scroll-control next"><i class="fas fa-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details Card -->
            <div class="col-md-6 mb-4">
                <div class="product-details-card">
                    <div class="product-category"><?= $item['category'] ?></div>
                    <h2 class="product-title"><?= $item['name'] ?></h2>
                    <p class="product-description"><?= $item['description'] ?></p>
                    <div class="product-price"><span class="price-currency">₹</span><?= $item['price'] ?></div>
                    <button class="buy-now-btn" data-item="<?= $item['name'] ?>" data-price="<?= $item['price'] ?>" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                        <i class="fas fa-shopping-bag me-2"></i>Buy Now
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
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
                        <input type="hidden" name="purchase_item" id="purchaseItem">
                        <input type="hidden" name="purchase_price" id="purchasePrice">
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
        // Buy Now functionality
        document.querySelector('.buy-now-btn').addEventListener('click', function() {
            // Add animation effect
            this.classList.add('animate-click');
            setTimeout(() => this.classList.remove('animate-click'), 300);
            
            const itemName = this.getAttribute('data-item');
            const itemPrice = this.getAttribute('data-price');
            
            // Set the item and price in the hidden inputs for checkout
            document.getElementById('purchaseItem').value = itemName;
            document.getElementById('purchasePrice').value = itemPrice;
            
            // Show notification
            showNotification(`${itemName} added to checkout!`);
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

        // Row-wise Image Scroll Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const scrollWrapper = document.querySelector('.image-scroll-wrapper');
            const prevBtn = document.querySelector('.scroll-control.prev');
            const nextBtn = document.querySelector('.scroll-control.next');
            const imageWidth = document.querySelector('.product-image').offsetWidth;
            let scrollPosition = 0;
            let autoScrollInterval;
            
            // Function to scroll to the next image
            function scrollToNext() {
                scrollPosition += imageWidth;
                
                // Reset position when reaching the end (for infinite loop effect)
                if (scrollPosition >= imageWidth * 4) {
                    scrollPosition = 0;
                }
                
                scrollWrapper.style.transform = `translateX(-${scrollPosition}px)`;
            }
            
            // Function to scroll to the previous image
            function scrollToPrev() {
                scrollPosition -= imageWidth;
                
                // Reset position when reaching the beginning (for infinite loop effect)
                if (scrollPosition < 0) {
                    scrollPosition = imageWidth * 3;
                }
                
                scrollWrapper.style.transform = `translateX(-${scrollPosition}px)`;
            }
            
            // Setup automatic scrolling
            function startAutoScroll() {
                autoScrollInterval = setInterval(scrollToNext, 3000); // Scroll every 3 seconds
            }
            
            // Stop automatic scrolling
            function stopAutoScroll() {
                clearInterval(autoScrollInterval);
            }
            
            // Event listeners for manual controls
            nextBtn.addEventListener('click', function() {
                stopAutoScroll();
                scrollToNext();
                startAutoScroll();
            });
            
            prevBtn.addEventListener('click', function() {
                stopAutoScroll();
                scrollToPrev();
                startAutoScroll();
            });
            
            // Pause scrolling when hovering over image container
            document.querySelector('.product-image-container').addEventListener('mouseenter', stopAutoScroll);
            document.querySelector('.product-image-container').addEventListener('mouseleave', startAutoScroll);
            
            // Adjust scroll position when window is resized
            window.addEventListener('resize', function() {
                const newImageWidth = document.querySelector('.product-image').offsetWidth;
                const currentIndex = Math.round(scrollPosition / imageWidth);
                scrollPosition = currentIndex * newImageWidth;
                scrollWrapper.style.transform = `translateX(-${scrollPosition}px)`;
            });
            
            // Start auto-scrolling
            startAutoScroll();
        });
    </script>

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
            background: linear-gradient(135deg, var(--dark),rgb(87, 7, 81)); /** #242424 */
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
            margin-top: 20px;
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
        
        /* Product Image Card */
        .product-image-card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            height: 100%;
        }
        
        .product-image-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .product-image-card .badge {
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
            height: 450px;
            cursor: zoom-in;
        }
        
        /* Row-wise Image Scroll */
        .image-scroll-container {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .image-scroll-wrapper {
            display: flex;
            width: 100%;
            height: 100%;
            transition: transform 0.5s ease;
        }
        
        .product-image {
            min-width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        /* Zoom effect on hover */
        .image-scroll-container:hover .product-image {
            transform: scale(1.2);
        }
        
        /* Scroll controls */
        .image-scroll-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 1rem;
            z-index: 5;
        }
        
        .scroll-control {
            background: rgba(0, 0, 0, 0.5);
            color: var(--light);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
        }
        
        .product-image-container:hover .scroll-control {
            opacity: 0.8;
            transform: translateY(0);
        }
        
        .scroll-control:hover {
            background: var(--accent);
            opacity: 1;
        }
        
        .scroll-control.prev {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
        }
        
        .scroll-control.next {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0;
        }
        
        .product-image-container:hover .scroll-control.prev,
        .product-image-container:hover .scroll-control.next {
            opacity: 0.8;
        }
        
        /* Product Details Card */
        .product-details-card {
            background: rgba(255, 255, 255, 0.07);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .product-details-card:hover {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.2);
        }
        
        .product-category {
            color: var(--secondary);
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.8rem;
        }
        
        .product-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.3;
            color: var(--light);
        }
        
        .product-description {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
            flex-grow: 1;
        }
        
        .product-price {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 2rem;
        }
        
        .price-currency {
            font-size: 1.4rem;
            font-weight: 400;
            vertical-align: super;
            margin-right: 0.2rem;
        }
        
        .buy-now-btn {
            background: linear-gradient(90deg, var(--accent), #d48e1e);
            border: none;
            color: var(--dark);
            font-weight: 700;
            padding: 1.2rem;
            font-size: 1.2rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .buy-now-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 25px rgba(0, 0, 0, 0.3);
        }
        
        .buy-now-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }
        
        .buy-now-btn:hover::before {
            left: 100%;
        }
        
        /* Modal Styles */
        .modal-dialog {
            max-width: 500px;
            margin: 0 auto;
            position: relative;
            top: 50%;
            transform: translateY(-50%);
        }

        .modal.fade .modal-dialog {
            transform: translate(0, -50%) !important;
            top: 50%;
        }

        .modal.show .modal-dialog {
            transform: translate(0, -50%) !important;
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

        /* Animation for Button */
        .animate-click {
            animation: clickEffect 0.3s ease;
        }

        @keyframes clickEffect {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
        
        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .product-image-container {
                height: 400px;
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
                height: 350px;
            }
            
            .product-title {
                font-size: 1.8rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
            
            .product-details-card {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .product-image-container {
                height: 300px;
            }
            
            .product-title {
                font-size: 1.5rem;
            }
            
            .product-description {
                font-size: 1rem;
            }
            
            .buy-now-btn {
                padding: 1rem;
                font-size: 1rem;
            }
        }
    </style>
</body>
</html>