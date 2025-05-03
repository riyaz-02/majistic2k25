<?php
// Start session for visitor counter
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>maJIStic | JISCE</title>

    <?php include 'includes/links.php'; ?> <!-- Ensure this line is present to include the site icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <link rel="stylesheet" href="events/styles.css">

    <link rel="stylesheet" href="sponsors/style.css">

    <style>
        .modal-option {
        transition: transform 0.3s ease; /* Smooth transition for the transform */
        }

        .modal-option:hover {
        transform: scale(1.05); /* Scale the button to 105% on hover */
        }

        .close-btn {
        transition: transform 0.3s ease; /* Smooth transition for the close button */
        background-color:red;
        color:white;
        text-weight:bold;
        }

        .close-btn:hover {
        transform: scale(1.05); /* Scale the close button to 105% on hover */
        background-color:red;
        color:white;
        text-weight:bold;
        }
        
        /* Commented out Live Event Banner Styles 
        .live-banner {
            background: linear-gradient(135deg, #ff3e00, #ff8300);
            color: white;
            text-align: center;
            border-radius: 15px;
            padding: 20px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(255, 62, 0, 0.3);
            max-width: 600px;
            width: 90%; 
        }
        
        .live-banner::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0) 70%);
            animation: glare 6s linear infinite;
        }
        
        .live-badge {
            display: inline-block;
            background-color: #fff;
            color: #ff3e00;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 30px;
            margin-bottom: 10px;
            position: relative;
            animation: blink 1.5s infinite;
        }
        
        .live-badge::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #ff3e00;
            border-radius: 50%;
            margin-right: 5px;
            animation: pulse-dot 1.5s infinite;
        }
        
        .live-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .live-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        */
        
        /* Completion Banner Styles */
        .completion-banner {
            background: linear-gradient(135deg, #3a1c71,rgb(240, 86, 101),rgb(255, 144, 70));
            color: white;
            text-align: center;
            border-radius: 20px;
            padding: 30px;
            margin: 5px auto;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(58, 28, 113, 0.4);
            max-width: 900px;
            width: 90%;
            z-index: 1;
            animation: gradient-shift 5s ease infinite;
            background-size: 300% 300%;
        }
        
        @keyframes gradient-shift {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .completion-banner::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><path d="M50 0 L100 50 L50 100 L0 50Z" fill="rgba(255,255,255,0.05)"/></svg>') repeat;
            opacity: 0.3;
            z-index: -1;
            animation: pattern-move 30s linear infinite;
        }
        
        .completion-banner::after {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            animation: shimmer 8s linear infinite;
            z-index: -1;
        }
        
        .completion-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.9);
            color: #3a1c71;
            font-weight: bold;
            padding: 6px 18px;
            border-radius: 30px;
            margin-bottom: 20px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .completion-title {
            font-size: 2.8rem;
            font-weight: 800;
            margin: 15px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
            background: linear-gradient(to right, #ffffff, #e0e0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }
        
        .completion-subtitle {
            font-size: 1.3rem;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .stats-highlight {
            font-size: 2.2rem;
            font-weight: 700;
            color: #ffde59;
            margin: 5px 0;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            display: inline-block;
            position: relative;
            padding: 0 10px;
        }
        
        .stats-highlight::before,
        .stats-highlight::after {
            content: "★";
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .stats-highlight::before {
            left: -15px;
        }
        
        .stats-highlight::after {
            right: -15px;
        }
        
        .completion-message {
            font-size: 1.1rem;
            line-height: 1.5;
            margin: 20px auto;
            max-width: 80%;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 25px;
            margin-bottom: 10px;
        }
        
        .action-btn {
            background: rgba(255, 255, 255, 0.85);
            color: #3a1c71;
            font-weight: 600;
            padding: 10px 25px;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .action-btn:hover {
            background: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            color:rgb(233, 86, 101);
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 20px;
            opacity: 0.8;
            pointer-events: none; /* Prevent confetti from blocking interaction */
            animation: confettiFall linear forwards; /* Changed to forwards to stop at end */
            z-index: 0; /* Keep confetti behind content */
        }
        
        @keyframes confettiFall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 0.7;
            }
            100% {
                transform: translateY(500px) rotate(360deg);
                opacity: 0;
            }
        }
        
        #confetti-container {
            position: absolute;
            top: -15px;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        
        @keyframes shimmer {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pattern-move {
            0% { background-position: 0 0; }
            100% { background-position: 100px 100px; }
        }
        
        /* Responsive styles for completion banner */
        @media (max-width: 992px) {
            .completion-title {
                font-size: 2.4rem;
            }
            
            .completion-subtitle,
            .stats-highlight {
                font-size: 1.2rem;
            }
            
            .completion-banner {
                padding: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .completion-title {
                font-size: 2rem;
                letter-spacing: 1px;
            }
            
            .completion-subtitle {
                font-size: 1.1rem;
            }
            
            .stats-highlight {
                font-size: 1.8rem;
            }
            
            .completion-banner {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
            
            .action-btn {
                width: 80%;
            }
        }
        
        @media (max-width: 576px) {
            .completion-title {
                font-size: 1.8rem;
                letter-spacing: 0.5px;
            }
            
            .completion-subtitle {
                font-size: 1rem;
            }
            
            .stats-highlight {
                font-size: 1.6rem;
            }
            
            .completion-badge {
                padding: 5px 15px;
                font-size: 0.9rem;
            }
            
            .completion-banner {
                padding: 15px;
                width: 95%;
            }
        }
    </style>   
</head>

<body>
    <?php include 'includes/preloader/preloader.php'; ?>
    <?php include 'includes/header.php'; ?>

    <div id="hero" class="hero-content">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="entry-header">
                    <img src="images/majistic2k25_white.png"></img>
                    </div>
                    <!-- .entry-header -->

                    <div class="event-dates">
                        <!--<h2>DATES REVEALING SOON</h2> -->
                        <p></i> 11 <sup>th</sup> & 12 <sup>th</sup> April 2025</p> 
                        
                        <!-- <h2 class="mt-2"><i class="bi bi-geo-alt-fill"></i> JIS College of Engineering</h2> -->
                    </div><!-- .event-dates -->

                    <!-- Commented out countdown section - keeping for future use -->
                    <!--
                    <div class="countdown flex flex-wrap justify-content-center" data-date="2018/06/06">
                        <div class="countdown-holder text-center">
                            <div class="dday">0</div>
                            <label>Days</label>
                        </div>

                        <div class="countdown-holder text-center">
                            <div class="dhour">0</div>
                            <label>Hours</label>
                        </div>

                        <div class="countdown-holder text-center">
                            <div class="dmin">0</div>
                            <label>Minutes</label>
                        </div>

                        <div class="countdown-holder text-center">
                            <div class="dsec">0</div>
                            <label>Seconds</label>
                        </div>
                    </div>
                    -->

                    <!-- Commented out live event banner 
                    <div class="live-banner">
                        <div class="live-badge">LIVE NOW</div>
                        <h2 class="live-title">maJIStic 2k25 IS HAPPENING!</h2>
                        <p class="live-subtitle">Join us for an incredible experience at JISCE</p>
                    </div>
                    -->
                    
                    <!-- Completion & Thank You Banner -->
                    <div class="completion-banner">
                        <!-- <div class="completion-badge">Event Completed</div> -->
                        <h2 class="completion-title">Thank You for the Magic!</h2>
                        <p class="completion-subtitle">maJIStic 2k25 was an incredible journey thanks to YOU</p>
                        <div class="stats-highlight">2500+ Attendees</div>
                        <p class="completion-message">We're overwhelmed by your participation and enthusiasm. Stay tuned for photos and aftermovies. See you next year for an even bigger celebration!</p>
                        
                        <!-- Action buttons -->
                        <div class="action-buttons">
                            <button class="action-btn" id="exploreBtn">Explore</button>
                            <a href="check_status.php" class="action-btn">Check Status</a>
                        </div>
                        
                        <!-- Confetti container -->
                        <div id="confetti-container"></div>
                    </div>
                </div><!-- .col-12 -->
            </div><!-- row -->

            <!--<div class="row">
                <div class="col-12 ">
                    <div class="entry-footer">
                         <!-- Register Button - Commented out 
                         <!-- <button class="btn mb-5" id="registerBtn">Register</button> 
                        <!-- <a href="#" class="btn current">Explore</a> 
                    </div>
                </div>
            </div>-->
        </div><!-- .container -->
    </div><!-- .hero-content -->

<!-- Popup Modal -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <img class="majisticheadlogo" src="images/majisticlogo.png" alt="maJIStic Logo">
        <h2 class="align-center MT-3">Register for maJIStic 2k25</h2>
        <?php
        // Check if registration is enabled
        $registrationEnabled = true;
        if (file_exists('src/config/registration_config.php')) {
            include_once 'src/config/registration_config.php';
        }
        if (defined('REGISTRATION_ENABLED')) {
            $registrationEnabled = REGISTRATION_ENABLED;
        }
        
        // Display appropriate buttons based on registration status
        if ($registrationEnabled): 
        ?>
        <button onclick="window.open('registration_inhouse.php', '_blank')" class="modal-option mt-2">Student Registration</button>
<!--    <button onclick="window.open('registration_outhouse.php', '_blank')" class="modal-option" >Out-house Student</button>  -->
        <button class="modal-option" onclick="window.open('registration_alumni.php', '_blank')" >Alumni Registration</button>
        <?php else: ?>
        <button onclick="window.open('src/handler/registration_closed.php', '_blank')" class="modal-option mt-2">Student Registration</button>
        <button class="modal-option" onclick="window.open('src/handler/registration_closed.php', '_blank')" >Alumni Registration</button>
        <?php endif; ?>
        <button class="modal-option" onclick="window.open('merchandise.php', '_self')" >Merchandise</button>
        <button class="modal-option" onclick="window.open('check_status.php', '_self')" >Check Status</button>
        <button class="close-btn" id = "close-btn" >Close</button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get the modal and related elements
        var modal = document.getElementById("registerModal");
        var closeBtn = document.getElementById("close-btn");
        var exploreBtn = document.getElementById("exploreBtn");
        
        // Track how many times the modal has been shown
        var modalShownCount = parseInt(sessionStorage.getItem('modalShownCount') || '0');
        var maxShowCount = 2; // Maximum times to show the modal
        
        // Function to open the modal
        function openModal() {
            modal.style.display = "block";
            modalShownCount++;
            sessionStorage.setItem('modalShownCount', modalShownCount);
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Event listeners for the buttons
        if (exploreBtn) {
            exploreBtn.addEventListener("click", function() {
                openModal();
            });
        }
        
        if (closeBtn) {
            closeBtn.addEventListener("click", closeModal);
        }

        // Close modal when clicking outside of it
        window.addEventListener("click", function(event) {
            if (event.target == modal) {
                closeModal();
            }
        });

        // Create Intersection Observer for the footer section
        const footerSection = document.querySelector('footer');
        const footerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && modalShownCount < maxShowCount) {
                    // Show the modal when footer section is visible
                    openModal();
                }
            });
        }, { threshold: 0.2 });

        // Start observing the footer section
        if (footerSection) {
            footerObserver.observe(footerSection);
        }
        
        // Improved confetti animation
        let confettiInterval;
        
        function generateConfetti() {
            const container = document.getElementById('confetti-container');
            if (!container) return;
            
            // Clear existing confetti to prevent buildup
            container.innerHTML = '';
            
            const colors = ['#FFDE59', '#D76D77', '#3A1C71', '#FF9D6C', '#FF7C7C', '#FFFFFF'];
            const confettiCount = 40; // Reduced count for better performance
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('confetti');
                
                // Random styling
                const color = colors[Math.floor(Math.random() * colors.length)];
                const left = Math.random() * 100;
                const width = Math.random() * 8 + 3;
                const height = Math.random() * 10 + 5;
                const animationDuration = Math.random() * 3 + 2;
                const animationDelay = Math.random() * 2; // Reduced delay
                
                // Apply styles
                confetti.style.backgroundColor = color;
                confetti.style.left = `${left}%`;
                confetti.style.width = `${width}px`;
                confetti.style.height = `${height}px`;
                confetti.style.animationDuration = `${animationDuration}s`;
                confetti.style.animationDelay = `${animationDelay}s`;
                confetti.style.opacity = Math.random() * 0.7 + 0.3;
                
                // Add to container
                container.appendChild(confetti);
                
                // Auto-remove confetti after animation completes
                setTimeout(() => {
                    if (confetti.parentNode === container) {
                        container.removeChild(confetti);
                    }
                }, (animationDuration + animationDelay) * 1000 + 100);
            }
        }
        
        // Initial confetti generation
        generateConfetti();
        
        // Clear any existing interval
        if (confettiInterval) {
            clearInterval(confettiInterval);
        }
        
        // Set new interval for confetti generation
        confettiInterval = setInterval(generateConfetti, 3500);
        
        // Clean up interval when page is unloaded
        window.addEventListener('beforeunload', function() {
            if (confettiInterval) {
                clearInterval(confettiInterval);
            }
        });
    });
</script>

<div class="heading-container flip-in" id="artist">
    <h1 class="text-center display-4 font-weight-bold section-title">Majistic 2K25: The Big Reveal</h1>
</div>
<?php include 'artist/index.php'; ?>

<!-- Events Section -->
<section id="events">
    <?php include 'events/index.php'; ?>
</section>

<!-- Proshows Section -->
<?php include 'proshows/index.php'; ?>

<!--- After Movies -->
<section class="aftermovies" id="aftermovie-section">
    <div class="heading-container" id="aftermovies">
        <h1 class="text-center display-4 font-weight-bold section-title">THE GRAND RECAP</h1>
    </div>
    <div class="container-fluid p-0">
        <div id="video-container" class="text-center">
            <!-- Responsive iframe container -->
            <div class="video-responsive">
                <div id="youtube-player"></div>
            </div>
        </div>
    </div>
</section>

    <section id="stats">
    <div class="heading-container flip-in" id="highlights">
            <h1 class="text-center display-4 font-weight-bold section-title">IMPACT AT A GLANCE</h1>
        </div>
        <div class="content">
  <div class="box" data-start="0" data-end="9000" data-duration="1000">
    <span class="counter">9000+</span>
    <p>Footfalls</p>
  </div>
  <div class="box" data-start="0" data-end="20" data-duration="1000">
    <span class="counter">20+</span>
    <p>Events</p>
  </div>
  <div class="box" data-start="0" data-end="48" data-duration="1000">
    <span class="counter">48+</span>
    <p>Hours of Program</p>
  </div>
  <div class="box" data-start="0" data-end="5000" data-duration="1000">
    <span class="counter">5000+</span>
    <p>Registrations</p>
  </div>
  </div>
</section>


    <!-- Highlight Section -->

    <section id="gallery" class="home-gallery">
        <div class="heading-container flip-in" id="highlights">
            <h1 class="text-center display-4 font-weight-bold section-title">BEST OF maJIStic</h1>
        </div>
        <div class="container">
            <div class="gallery">
                <!-- Original items -->
                <div class="gallery__item gallery__item--hor"> <!-- Horizontal image 1-->
                    <img src="https://i.postimg.cc/NFFjFHkn/hi1-gallery.jpg" alt="Highlight Image">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/2yTmQ82Z/si13-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/W4DRdwBg/si6-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--hor"> <!-- Horizontal image 2-->
                    <img src="https://i.postimg.cc/tRvgfBXG/hi2-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/gJRfDkk0/si7-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/zvr4PZVF/si8-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--vert"> <!-- Vertical image 1-->
                    <img src="https://i.postimg.cc/KY3VGvT5/vi1-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/15yLGSx9/si9-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/3J9z9q72/si10-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--vert"> <!-- Vertical image 2-->
                    <img src="https://i.postimg.cc/NfGzyxTF/vi3-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--lg"> <!-- Large image 1-->
                    <img src="https://i.postimg.cc/0Q28XwFN/li1-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/0NJ8Tv2T/si11-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/26QC5xWT/si12-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--lg"> <!-- Large image 2-->
                    <img src="https://i.postimg.cc/50RMPk8P/li2-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/xjNdwKqk/si2-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--vert"> <!-- Vertical image 2-->
                    <img src="https://i.postimg.cc/hvc31L5C/vi2-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--lg"> <!-- Large image 3-->
                    <img src="https://i.postimg.cc/FFwYzTwg/li3-gallery.jpg" alt="">
                </div>
                <div class="gallery__item gallery__item--lg"> <!-- Large image 4-->
                    <img src="https://i.postimg.cc/mrZDJJFR/li4-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/W493v8MZ/si1-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/NjDG6DCn/si3-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/V6xzfV5J/si4-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/mk3WbZ0n/si5-gallery.jpg" alt="">
                </div>
                <div class="gallery__item">
                    <img src="https://i.postimg.cc/NFKb4dxk/si14-gallery.jpg" alt="">
                </div>
            </div>
        </div>
    </section>

    <div class="heading-container flip-in" id="sponsors">
        <h1 class="text-center display-4 font-weight-bold section-title">Thank You to Our Previous Sponsors</h1>
    </div>
    <!-- Sponsors Section -->
    <!-- <section class="base-template">
    
	<div class="wrapper base-template__wrapper">
		<div class="base-template__content">
			<div class="horizontal-ticker">

				<!-- Horizontal Ticker: Slider RTL 

				<div id="horizontal-ticker-rtl" class="swiper horizontal-ticker__slider">
					<div class="swiper-wrapper">

                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">

                        </div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
                        </div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
                        </div>
						<div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
                        </div>
						<div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">

                        </div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
						</div>
                        </div>
						<!-- slides copies 

						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">

						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">
                        </div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
							<img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
                            <img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
                            <img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
                        </div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
							<img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
							<img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
						</div>
					</div>
				</div>

				<!-- Horizontal Ticker: Slider LTR 

				<div id="horizontal-ticker-ltr" class="swiper horizontal-ticker__slider">
					<div class="swiper-wrapper">

                    <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/DZ52Rb0q/1-1.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/L5bm8Kz5/2-1.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/Pr0tGHXF/3-1.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
							<img src="https://i.postimg.cc/SKfVf1q3/10.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/L5nM7Crp/5-1.png" alt="Tochiba">
						</div>
						<div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
							<img src="https://i.postimg.cc/Gm017jKW/6-1.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
							<img src="https://i.postimg.cc/nh73CcWH/7.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
							<img src="https://i.postimg.cc/bvh3bfX1/8.png" alt="Tochiba">
						</div>
                        <div class="swiper-slide horizontal-ticker__slide">
							<img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
							<img src="https://i.postimg.cc/LX4DCk8X/9.png" alt="Tochiba">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section> -->

<?php include 'sponsors/index.php'; ?>

    <?php include 'includes/footer.php'; ?>
    <?php include 'includes/scripts.php'; ?>
    <script src="sponsors/script.js"></script>
    <script>
        // Bounce-in, bounce-out, and pulse animation on scroll for stats section
        document.addEventListener('DOMContentLoaded', function() {
            const statsBoxes = document.querySelectorAll('#stats .box');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('bounce-in', 'pulse');
                        entry.target.classList.remove('bounce-out');
                        entry.target.style.visibility = 'visible';
                        entry.target.style.opacity = '1';
                    } else {
                        entry.target.classList.add('bounce-out');
                        entry.target.classList.remove('bounce-in', 'pulse');
                        entry.target.style.visibility = 'hidden';
                        entry.target.style.opacity = '0';
                    }
                });
            }, { threshold: 0.1 });

            statsBoxes.forEach(box => {
                observer.observe(box);
            });
        });

        // Flip-in and flip-out animation on scroll for section headings
        document.addEventListener('DOMContentLoaded', function() {
            const headings = document.querySelectorAll('.heading-container');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('flip-in');
                        entry.target.classList.remove('flip-out');
                    } else {
                        entry.target.classList.add('flip-out');
                        entry.target.classList.remove('flip-in');
                    }
                });
            }, { threshold: 0.1 });

            headings.forEach(heading => {
                observer.observe(heading);
            });
        });

        // YouTube player auto-play implementation
        // Load the YouTube IFrame Player API asynchronously
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        // Variable to store the YouTube player instance
        var player;

        // Function called when YouTube API is ready
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('youtube-player', {
                height: '100%',
                width: '100%',
                videoId: '55DF9m2XX4U', // Same video ID as the original iframe
                playerVars: {
                    'autoplay': 0,
                    'mute': 1,
                    'start': 3, // Start from the 3rd second
                    'controls': 0, // Hide controls
                    'showinfo': 0,
                    'rel': 0,
                    'modestbranding': 1,
                    'disablekb': 1, // Disable keyboard controls
                    'iv_load_policy': 3, // Hide annotations
                    'fs': 0, // Hide fullscreen button
                    'playsinline': 1 // Play inline on mobile
                },
                events: {
                    'onReady': onPlayerReady
                }
            });
        }

        function onPlayerReady(event) {
            // Set up Intersection Observer once the player is ready
            const videoSection = document.getElementById('aftermovie-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Play the video when section is visible
                        if (player && player.getPlayerState() !== 1) { // 1 is YT.PlayerState.PLAYING
                            player.playVideo();
                        }
                    } else {
                        // Pause when out of view
                        if (player && player.getPlayerState() === 1) {
                            player.pauseVideo();
                        }
                    }
                });
            }, { threshold: 0.3 }); // Trigger when 30% of the section is visible

            observer.observe(videoSection);
            
            // Set up click handler for mute/unmute functionality
            const videoContainer = document.getElementById('video-container');
            videoContainer.addEventListener('click', function() {
                if (player.isMuted()) {
                    player.unMute();
                    // You can add a visual indicator for unmuted state if desired
                } else {
                    player.mute();
                    // You can add a visual indicator for muted state if desired
                }
            });
        }
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
	const isDesktop = () => window.innerWidth > 767.9;

	let gap = 15;

	if (isDesktop()) gap = 0.0285 * window.innerWidth;

	const sliders = [];

	["#horizontal-ticker-ltr", "#horizontal-ticker-rtl"].forEach(
		(query, index) => {
			sliders.push(
				new Swiper(query, {
					loop: true,
					slidesPerView: "auto",
					spaceBetween: gap,
					speed: 8000,
					allowTouchMove: false,
					autoplay: {
						delay: 0,
						reverseDirection: index,
						disableOnInteraction: false
					}
				})
			);
		}
	);

	window.addEventListener("resize", () => {
		isDesktop() ? (gap = 0.0285 * window.innerWidth) : (gap = 15);

		sliders.forEach((slider) => {
			slider.params.spaceBetween = gap;
			slider.update();
		});
	});
});
</script>
</body>
<style>
    .base-template__wrapper {
	max-width: 100dvw;
	padding-bottom: 50px;
}

/**
 * Slider Instance
 */

.swiper {
	width: 100%;
}

.swiper-wrapper {
	transition-timing-function: linear !important;
}

#sponsors .swiper-slide {
	height: auto !important;
}

.horizontal-ticker {
	margin: 0 -20px;
	display: flex;
	flex-direction: column;
	row-gap: 2.85vw;
}

@media screen and (max-width: 767.9px) {
	.horizontal-ticker {
		row-gap: 15px;
	}
}

/**
 * Slider Slides
 */

.horizontal-ticker__slide {
	position: relative;
	width: 15.625vw;
	aspect-ratio: 300 / 205;
	border-radius: 10px;
	overflow: hidden;
	backdrop-filter: blur(50px);
}

@media screen and (max-width: 767.9px) {
	.horizontal-ticker__slide {
		width: 150px;
	}
}

.horizontal-ticker__slide img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
	transition: opacity 0.6s ease-out;
}

.horizontal-ticker__slide img:last-child {
	position: absolute;
	inset: 0;
	opacity: 0;
}

@media (hover: hover) and (pointer: fine) {
	.horizontal-ticker__slide:hover img:last-child {
		opacity: 1;
	}
}

/* YouTube player styling */
.aftermovies .container-fluid {
    padding: 0;
    margin: 0;
    max-width: 100%;
}

.video-responsive {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    height: 0;
    overflow: hidden;
    width: 100%;
}

.video-responsive #youtube-player {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* Add a mute/unmute indicator */
.video-responsive::after {
    content: "Click to toggle sound";
    position: absolute;
    bottom: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 14px;
    opacity: 0.7;
    transition: opacity 0.3s;
    pointer-events: none;
}

.video-responsive:hover::after {
    opacity: 1;
}

/* Ensure the aftermovies section spans full width */
#aftermovie-section {
    padding: 0;
    margin-top: 30px;
    margin-bottom: 30px;
}

#aftermovie-section .heading-container {
    margin-bottom: 30px;
}

</style>
</html>