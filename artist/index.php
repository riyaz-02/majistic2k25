<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Featured Artists - Majistic 2K25</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="artist-page-background"></div>

    <!-- Artist Section Start -->
    <section id="artists-carousel" class="artist-fade-in">
        <div class="artist-swiper-container">
            <div class="swiper-wrapper">
                <!-- Artist 1 -->
                <div class="swiper-slide">
                    <div class="artist-container">
                        <div class="artist-banner">
                            <div class="artist-image" style="background-image: url('https://i.ibb.co/8nvdsMHd/REVEALING-20250319-040742-0000.png')">
                                <div class="artist-overlay">
                                    <h2 class="artist-name">DJ Night</h2>
                                    <p class="artist-type">Get Ready for a Night of Electrifying Beats!</p>
                                </div>
                            </div>
                        </div>
                        <div class="artist-event-details">
                            <h3 class="artist-event-name">Revealing Soon</h3>
                            <div class="artist-event-info">
                                <div class="artist-info-item artist-date-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>11th April, 2025</span>
                                </div>
                                <div class="artist-info-item artist-time-info">
                                    <i class="fas fa-clock"></i>
                                    <span>6:00 PM - 10:00 PM</span>
                                </div>
                                <div class="artist-info-item artist-location-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>JISCE Main Ground</span>
                                </div>
                            </div>
                            <p class="artist-event-description">
                                "The bass is about to drop, and the crowd is about to go wild! A renowned DJ is taking over Majistic 2K25, bringing high-energy electronic beats, pulsating rhythms, and a night of non-stop dancing. Brace yourself for an unforgettable audio-visual spectacle, where music and madness collide!"
                            </p>
                            <button class="artist-register-btn" onclick="registerForEvent('Neon Beats Night')">Register Now</button>
                        </div>
                    </div>
                </div>
                
                <!-- Artist 2 -->
                <div class="swiper-slide">
                    <div class="artist-container">
                        <div class="artist-banner">
                            <div class="artist-image" style="background-image: url('https://i.ibb.co/8nvdsMHd/REVEALING-20250319-040742-0000.png')">
                                <div class="artist-overlay">
                                    <h2 class="artist-name">Band Night</h2>
                                    <p class="artist-type">A High-Octane Band is Coming to Shake Majistic 2K25!</p>
                                </div>
                            </div>
                        </div>
                        <div class="artist-event-details">
                            <h3 class="artist-event-name">Revealing Soon</h3>
                            <div class="artist-event-info">
                                <div class="artist-info-item artist-date-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>12 April, 2025</span>
                                </div>
                                <div class="artist-info-item artist-time-info">
                                    <i class="fas fa-clock"></i>
                                    <span>7:30 PM - 10:30 PM</span>
                                </div>
                                <div class="artist-info-item artist-location-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>JISCE Main Ground</span>
                                </div>
                            </div>
                            <p class="artist-event-description">
                                "Loud guitars, thunderous drums, and an unstoppable wave of energy—a legendary band is set to take the Majistic 2K25 stage by storm! Whether you’re a fan of rock, fusion, or headbanging anthems, this night will be pure musical madness. Prepare for powerful performances, soul-stirring solos, and a crowd that roars along!"
                            </p>
                            <button class="artist-register-btn" onclick="registerForEvent('Rock Revolution')">Register Now</button>
                        </div>
                    </div>
                </div>
                
                <!-- Artist 3 -->
                <div class="swiper-slide">
                    <div class="artist-container">
                        <div class="artist-banner">
                            <div class="artist-image" style="background-image: url('https://i.ibb.co/8nvdsMHd/REVEALING-20250319-040742-0000.png')">
                                <div class="artist-overlay">
                                    <h2 class="artist-name">Solo Singer Concert</h2>
                                    <p class="artist-type">One Voice, One Stage, Infinite Emotions!</p>
                                </div>
                            </div>
                        </div>
                        <div class="artist-event-details">
                            <h3 class="artist-event-name">Revealing Soon</h3>
                            <div class="artist-event-info">
                                <div class="artist-info-item artist-date-info">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>12 April, 2025</span>
                                </div>
                                <div class="artist-info-item artist-time-info">
                                    <i class="fas fa-clock"></i>
                                    <span>6:00 PM - 9:00 PM</span>
                                </div>
                                <div class="artist-info-item artist-location-info">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>JISCE Main Ground</span>
                                </div>
                            </div>
                            <p class="artist-event-description">
                                "A voice that melts hearts, gives chills, and lights up the night—a mystery artist is set to take the Majistic 2K25 stage for an unforgettable solo concert! Whether it's soulful melodies, foot-tapping hits, or powerful ballads, this artist will leave you spellbound. Get ready to sing along, sway to the rhythm, and witness magic unfold under the stars!"
                            </p>
                            <button class="artist-register-btn" onclick="registerForEvent('Melodies Under Moonlight')">Register Now</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Add Navigation -->
            <div class="artist-swiper-button-next"></div>
            <div class="artist-swiper-button-prev"></div>
            
            <!-- Add Pagination -->
            <div class="artist-swiper-pagination"></div>
        </div>
    </section>
    <!-- Artist Section End -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Artist Swiper with unique configuration
            var artistSwiper = new Swiper(".artist-swiper-container", {
                slidesPerView: 1,
                spaceBetween: 30,
                loop: true,
                speed: 800,
                autoHeight: true, /* Added to automatically adjust height */
                keyboard: {
                    enabled: true
                },
                pagination: {
                    el: ".artist-swiper-pagination",
                    clickable: true,
                    dynamicBullets: true
                },
                navigation: {
                    nextEl: ".artist-swiper-button-next",
                    prevEl: ".artist-swiper-button-prev"
                },
                effect: "fade",
                fadeEffect: {
                    crossFade: true
                },
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false
                },
                on: {
                    init: function() {
                        // Update swiper height after initialization
                        setTimeout(() => {
                            this.updateAutoHeight(10);
                        }, 100);
                    },
                    slideChangeTransitionEnd: function() {
                        // Update height after slide changes
                        this.updateAutoHeight(10);
                    }
                }
            });

            // Fade-in animation on scroll
            const artistSection = document.querySelector('#artists-carousel');
            if (artistSection) {
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            artistSection.classList.add('artist-visible');
                        } else {
                            artistSection.classList.remove('artist-visible');
                        }
                    });
                }, { threshold: 0.1 });

                observer.observe(artistSection);
            }
        });

        function registerForEvent(eventName) {
            window.open('registration_inhouse.php', '_blank');
            console.log(`Registration initiated for ${eventName}`);
        }
    </script>

    <style>
        /* Artist section specific styles with unique prefixes to avoid conflicts */
        #artists-carousel {
            opacity: 0;
            transition: opacity 1s ease-in-out;
            padding: 20px 0;
            margin-bottom: 40px;
            position: relative;
            display: flex;
            justify-content: center;
        }
        
        #artists-carousel.artist-visible {
            opacity: 1;
        }
        
        .artist-heading-container {
            text-align: center;
            padding: 40px 20px 10px;
            margin-bottom: 10px;
        }
        
        .artist-section-title {
            font-size: 2.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .artist-section-subtitle {
            font-size: 1.2rem;
            color: #ffffff;
            margin-bottom: 20px;
        }
        
        .artist-swiper-container {
            width: 90%;
            height: auto; /* Changed from fixed height to auto */
            overflow: visible; /* Changed from hidden to visible */
            position: relative;
            z-index: 1;
            margin: 0 auto;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .artist-container {
            display: flex;
            flex-direction: row;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.9);
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.8);
            min-height: 500px; /* Changed from fixed height to min-height */
            height: auto; /* Added to allow container to grow based on content */
            border-radius: 20px;
        }
        
        .artist-banner {
            flex: 2;
            position: relative;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
            overflow: hidden;
            min-height: 500px; /* Added minimum height */
        }
        
        .artist-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: all 0.5s ease;
            background-color: #000;
        }
        
        .artist-image:hover {
            transform: scale(1.03);
        }
        
        .artist-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            background: linear-gradient(0deg, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 50%, rgba(0,0,0,0) 100%);
        }
        
        .artist-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            color: #ffffff;
        }
        
        .artist-type {
            font-size: 1.1rem;
            font-weight: 400;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .artist-event-details {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            background: rgba(18, 18, 20, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
            overflow-y: visible; /* Changed from auto to visible to prevent scrollbars */
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }
        
        .artist-event-name {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .artist-event-info {
            margin-bottom: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        
        .artist-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            color: #ffffff;
        }
        
        .artist-info-item:last-child {
            margin-bottom: 0;
        }
        
        .artist-info-item:hover {
            transform: translateX(10px);
        }
        
        .artist-info-item i {
            color: #ffffff;
            margin-right: 15px;
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .artist-date-info i {
            background: rgba(74, 0, 224, 0.15);
            box-shadow: 0 0 10px rgba(74, 0, 224, 0.3);
        }
        
        .artist-time-info i {
            background: rgba(142, 45, 226, 0.15);
            box-shadow: 0 0 10px rgba(142, 45, 226, 0.3);
        }
        
        .artist-location-info i {
            background: rgba(86, 67, 250, 0.15);
            box-shadow: 0 0 10px rgba(86, 67, 250, 0.3);
        }
        
        .artist-event-description {
            margin-bottom: 30px;
            line-height: 1.8;
            color: #ffffff;
            flex-grow: 1;
            font-size: 1.1rem;
            padding: 0 10px;
            border-left: 3px solid #8e2de2;
            font-style: italic;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .artist-register-btn {
            padding: 18px 35px;
            font-size: 1.2rem;
            font-weight: 600;
            background: linear-gradient(135deg, #4a00e0, #8e2de2);
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            align-self: center;
            box-shadow: 0 10px 20px rgba(74, 0, 224, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 80%;
        }
        
        .artist-register-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 30px rgba(74, 0, 224, 0.4);
        }
        
        .artist-register-btn:active {
            transform: translateY(0) scale(0.98);
        }
        
        /* Swiper navigation and pagination with unique selectors */
        #artists-carousel .swiper-slide {
            height: auto; /* Allow slide to adjust based on content height */
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 20px;
            overflow: hidden;
        }
        
        #artists-carousel .swiper-slide-active {
            opacity: 1;
        }
        
        .artist-swiper-pagination {
            position: absolute;
            bottom: 20px !important;
            left: 0;
            width: 100%;
            text-align: center;
            z-index: 10;
        }
        
        .artist-swiper-pagination .swiper-pagination-bullet {
            width: 12px;
            height: 12px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0.5;
            margin: 0 5px;
        }
        
        .artist-swiper-pagination .swiper-pagination-bullet-active {
            background: #4a00e0;
            opacity: 1;
        }
        
        .artist-swiper-button-next,
        .artist-swiper-button-prev {
            position: absolute;
            top: 50%;
            width: 50px;
            height: 50px;
            margin-top: -25px;
            z-index: 10;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background-color: rgba(74, 0, 224, 0.2);
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .artist-swiper-button-next {
            right: 20px;
        }
        
        .artist-swiper-button-prev {
            left: 20px;
        }
        
        .artist-swiper-button-next:after,
        .artist-swiper-button-prev:after {
            font-family: 'swiper-icons';
            font-size: 20px;
            color: #ffffff;
        }
        
        .artist-swiper-button-next:after {
            content: 'next';
        }
        
        .artist-swiper-button-prev:after {
            content: 'prev';
        }
        
        .artist-swiper-button-next:hover,
        .artist-swiper-button-prev:hover {
            background-color: rgba(74, 0, 224, 0.4);
            transform: scale(1.1);
        }
        
        /* Responsive design */
        @media (max-width: 1200px) {
            .artist-banner {
                flex: 3;
            }
            
            .artist-event-details {
                flex: 2;
            }
            
            .artist-container {
                min-height: 450px; /* Changed from fixed height to min-height */
            }
            
            .artist-swiper-container {
                height: auto; /* Allow container to adjust based on content */
            }
        }
        
        @media (max-width: 992px) {
            .artist-container {
                flex-direction: column;
                height: auto;
            }
            
            .artist-banner {
                min-height: 350px;
                position: relative;
                border-radius: 20px 20px 0 0;
            }
            
            .artist-image {
                position: absolute;
            }
            
            .artist-event-name {
                font-size: 1.8rem;
            }
            
            .artist-name {
                font-size: 2rem;
            }
            
            .artist-event-info {
                padding: 20px;
            }
            
            .artist-swiper-container {
                height: auto;
            }
            
            .artist-event-details {
                border-radius: 0 0 20px 20px;
            }
        }
        
        @media (max-width: 768px) {
            .artist-section-title {
                font-size: 2.2rem;
            }
            
            .artist-banner {
                min-height: 300px;
            }
            
            .artist-event-details {
                padding: 25px;
            }
            
            .artist-event-name {
                font-size: 1.6rem;
                margin-bottom: 20px;
            }
            
            .artist-info-item {
                font-size: 1rem;
                margin-bottom: 15px;
            }
            
            .artist-info-item i {
                font-size: 1.3rem;
                width: 35px;
                height: 35px;
            }
            
            .artist-event-description {
                font-size: 1rem;
                line-height: 1.6;
            }
            
            .artist-register-btn {
                padding: 15px 30px;
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            #artists-carousel {
                padding: 15px 0;
            }
            
            .artist-swiper-container {
                width: 95%;
            }
            
            .artist-section-title {
                font-size: 1.8rem;
            }
            
            .artist-banner {
                min-height: 250px;
            }
            
            .artist-name {
                font-size: 1.5rem;
            }
            
            .artist-type {
                font-size: 0.9rem;
            }
            
            .artist-event-details {
                padding: 20px 15px;
            }
            
            .artist-event-name {
                font-size: 1.4rem;
                margin-bottom: 15px;
            }
            
            .artist-event-info {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .artist-info-item {
                margin-bottom: 12px;
            }
            
            .artist-info-item i {
                font-size: 1.2rem;
                width: 30px;
                height: 30px;
                margin-right: 10px;
            }
            
            .artist-event-description {
                padding: 0 5px;
                border-left: 2px solid #8e2de2;
                margin-bottom: 20px;
            }
            
            .artist-register-btn {
                width: 100%;
                text-align: center;
                padding: 12px 20px;
                font-size: 1rem;
            }
            
            .artist-swiper-button-next,
            .artist-swiper-button-prev {
                width: 40px;
                height: 40px;
            }
        }
    </style>
</body>
</html>