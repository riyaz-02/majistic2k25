
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Poppins:wght@300;500;700&family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: transparent; /* Changed from gradient to transparent */
            color: white;
            text-align: center;
            padding: 0; /* Removed padding */
            margin: 0;
            overflow-x: hidden;
        }

        .festival-section {
            max-width: 100%; /* Changed from 1400px to 100% */
            margin: 0 auto;
            padding: 0; /* Removed vertical padding */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .section-header {
            margin: 20px auto; /* Center horizontally with margin auto */
            text-align: center;
            padding: 0 20px;
            max-width: 800px;
            position: relative;
        }

        .section-name {
            font-family: 'Roboto', sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #FF6347;
            margin-bottom: 5px;
            letter-spacing: 3px;
            text-align: left;
            position: relative;
            display: inline-block;
        }
        
        .section-name::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: #FF6347;
        }

        .section-title {
            font-family: 'Roboto', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #fff;
            margin-bottom: 15px;
            letter-spacing: 3px;
            text-align: center;
            position: relative;
            display: inline-block;
            text-shadow: 0 0 10px rgba(255, 99, 71, 0.7), 0 0 20px rgba(255, 99, 71, 0.5);
            line-height: 1.2;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 70px;
            height: 3px;
            background-color: #FF6347;
        }

        .section-subtitle {
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 300;
            color: #f1f1f1;
            text-align: center;
            line-height: 1.5;
            margin-top: 20px;
            margin-bottom: 0;
        }

        .marquee-container {
            position: relative;
            width: 100%;
            overflow: hidden;
            height: 140px;
            margin: 0; /* Removed vertical margins */
        }

        .marquee {
            position: absolute;
            display: flex;
            gap: 20px;
            left: 0;
            top: 0;
            width: auto;
            white-space: nowrap;
            will-change: transform;
        }

        .marquee-inner {
            display: flex;
            gap: 20px;
            min-width: 100%;
        }

        .marquee-forward {
            animation: marquee-forward 30s linear infinite;
        }

        .marquee-reverse {
            animation: marquee-reverse 30s linear infinite;
        }

        @keyframes marquee-forward {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }

        @keyframes marquee-reverse {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(0); }
        }

        .marquee-item {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }

        .marquee img {
            width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .marquee img:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 10px 20px rgba(255, 99, 71, 0.4);
        }

        /* Image reflection effect */
        .marquee-item::after {
            content: "";
            position: absolute;
            bottom: -20px;
            left: 0;
            width: 100%;
            height: 20px;
            background-image: linear-gradient(to bottom, rgba(255,255,255,0.3), transparent);
            transform: scaleY(-1);
            opacity: 0.4;
            border-radius: 0 0 10px 10px;
        }

        .video-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0; /* Removed vertical margins */
            padding: 20px 0;
            flex-wrap: wrap;
            gap: 30px;
            width: 100%;
        }

        .quote {
            width: 22%;
            font-size: 1.2em;
            font-style: italic;
            font-family: 'Montserrat', sans-serif;
            color: #fff;
            opacity: 0;
            position: relative;
            padding: 20px;
            border-left: 3px solid rgba(255, 99, 71, 0.7);
            animation: fadeIn 1s ease forwards;
            animation-delay: 0.5s;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .quote:hover {
            transform: translateY(-5px);
        }

        .quote:first-of-type {
            animation-delay: 0.3s;
        }

        .quote::before {
            content: open-quote;
            font-size: 2em;
            line-height: 0.1em;
            color: rgba(255, 99, 71, 0.7);
            position: absolute;
            left: -10px;
            top: 10px;
        }

        .quote::after {
            content: close-quote;
            font-size: 2em;
            line-height: 0.1em;
            color: rgba(255, 99, 71, 0.7);
            position: absolute;
            right: 10px;
            bottom: 10px;
        }

        .video-player-container {
            width: 45%;
            position: relative;
            border-radius: 15px;
            border: 2px solid #000; /* Added black border */
            box-shadow: 0 0 30px rgba(255, 99, 71, 0.4);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .video-player-container:hover {
            transform: scale(1.02);
            box-shadow: 0 0 40px rgba(255, 99, 71, 0.6);
        }

        .video-player {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 aspect ratio */
            border-radius: 15px;
            overflow: hidden;
        }

        .video-player iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 13px; /* Reduced slightly to accommodate the border */
        }

        .play-button-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background-color: rgba(255, 99, 71, 0.8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
            opacity: 0.9;
            transition: all 0.3s ease;
        }

        .play-button-overlay:hover {
            transform: translate(-50%, -50%) scale(1.1);
            opacity: 1;
            background-color: rgba(255, 99, 71, 1);
        }

        .play-button-overlay::after {
            content: "";
            display: block;
            width: 0;
            height: 0;
            border-top: 15px solid transparent;
            border-bottom: 15px solid transparent;
            border-left: 25px solid white;
            margin-left: 5px;
        }

        .video-glow {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border-radius: 15px;
            box-shadow: inset 0 0 30px rgba(255, 99, 71, 0.5);
            pointer-events: none;
            z-index: 1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .video-section {
                flex-direction: column;
                align-items: center;
            }
            
            .quote {
                width: 80%;
                margin: 10px 0;
                order: 2;
            }
            
            .video-player-container {
                width: 90%;
                order: 1;
            }
            
            .marquee img {
                width: 150px;
                height: 100px;
            }
            
            .marquee-container {
                height: 120px;
            }
            
            .section-title {
                font-size: 2rem;
            }

            .section-header {
                text-align: center;
                margin: 15px auto;
            }
            
            .section-name, .section-title, .section-subtitle {
                text-align: center;
            }
            
            .section-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }

        @media (max-width: 768px) {
            .quote {
                display: none; /* Hide quotes on mobile screens */
            }
            
            .video-section {
                margin: 20px 0; /* Reduce margin to remove gaps */
                gap: 0;
            }
            
            .marquee-container {
                margin: 0; /* Remove margin to eliminate gaps */
            }
            
            .festival-section {
                padding: 0; /* Remove padding completely */
            }
            
            .video-player-container {
                width: 100%; /* Full width on mobile */
            }
        }

        @media (max-width: 576px) {
            .marquee img {
                width: 120px;
                height: 80px;
            }
            
            .marquee-container {
                height: 100px;
            }
            
            .section-title {
                font-size: 1.8rem;
                margin-bottom: 15px; /* Reduce bottom margin */
            }
            
            .play-button-overlay {
                width: 60px;
                height: 60px;
            }
        }
    </style>
</head>
<body>

    <div class="festival-section">
        <div class="section-header">
            <h1 class="section-title">The Big Reveal</h1>
            <p class="section-subtitle">A surprise flash mob and an electrifying promo launch set the stage for the biggest fest of the year!</p>
        </div>
        
        <!-- Top Marquee -->
        <div class="marquee-container">
            <div class="marquee">
                <div class="marquee-inner marquee-forward">
                    <div class="marquee-item"><img src="../images/sponsors/1.1.png" alt="Flash Mob 1"></div>
                    <div class="marquee-item"><img src="flashmob2.jpg" alt="Flash Mob 2"></div>
                    <div class="marquee-item"><img src="promo1.jpg" alt="Promo 1"></div>
                    <div class="marquee-item"><img src="promo2.jpg" alt="Promo 2"></div>
                    <div class="marquee-item"><img src="crowd.jpg" alt="Crowd"></div>
                    <div class="marquee-item"><img src="../images/sponsors/1.1.png" alt="Flash Mob 1"></div>
                    <div class="marquee-item"><img src="flashmob2.jpg" alt="Flash Mob 2"></div>
                    <div class="marquee-item"><img src="promo1.jpg" alt="Promo 1"></div>
                    <div class="marquee-item"><img src="promo2.jpg" alt="Promo 2"></div>
                    <div class="marquee-item"><img src="crowd.jpg" alt="Crowd"></div>
                    <div class="marquee-item"><img src="../images/sponsors/1.1.png" alt="Flash Mob 1"></div>
                    <div class="marquee-item"><img src="flashmob2.jpg" alt="Flash Mob 2"></div>
                    <div class="marquee-item"><img src="promo1.jpg" alt="Promo 1"></div>
                    <div class="marquee-item"><img src="promo2.jpg" alt="Promo 2"></div>
                    <div class="marquee-item"><img src="crowd.jpg" alt="Crowd"></div>
                </div>
                <div class="marquee-inner marquee-forward">
                    <div class="marquee-item"><img src="../images/sponsors/1.1.png" alt="Flash Mob 1"></div>
                    <div class="marquee-item"><img src="flashmob2.jpg" alt="Flash Mob 2"></div>
                    <div class="marquee-item"><img src="promo1.jpg" alt="Promo 1"></div>
                    <div class="marquee-item"><img src="promo2.jpg" alt="Promo 2"></div>
                    <div class="marquee-item"><img src="crowd.jpg" alt="Crowd"></div>
                </div>
            </div>
        </div>

        <!-- Video Section -->
        <div class="video-section">
            <div class="quote">"A surprise that left everyone in awe, the moment that defined our festival journey!"</div>
            
            <div class="video-player-container">
                <div class="video-player">
                    <iframe id="promo-video" src="https://www.youtube.com/embed/YOUR_VIDEO_ID?enablejsapi=1" allowfullscreen></iframe>
                    <div class="play-button-overlay" id="play-button"></div>
                    <div class="video-glow"></div>
                </div>
            </div>
            
            <div class="quote">"The energy was unreal, the performances extraordinary - Majistic 2K25 is here with a bang!"</div>
        </div>

        <!-- Bottom Marquee -->
        <div class="marquee-container">
            <div class="marquee">
                <div class="marquee-inner marquee-reverse">
                    <div class="marquee-item"><img src="flashmob3.jpg" alt="Flash Mob 3"></div>
                    <div class="marquee-item"><img src="flashmob4.jpg" alt="Flash Mob 4"></div>
                    <div class="marquee-item"><img src="promo3.jpg" alt="Promo 3"></div>
                    <div class="marquee-item"><img src="promo4.jpg" alt="Promo 4"></div>
                    <div class="marquee-item"><img src="crowd2.jpg" alt="Crowd 2"></div>
                </div>
                <div class="marquee-inner marquee-reverse">
                    <div class="marquee-item"><img src="flashmob3.jpg" alt="Flash Mob 3"></div>
                    <div class="marquee-item"><img src="flashmob4.jpg" alt="Flash Mob 4"></div>
                    <div class="marquee-item"><img src="promo3.jpg" alt="Promo 3"></div>
                    <div class="marquee-item"><img src="promo4.jpg" alt="Promo 4"></div>
                    <div class="marquee-item"><img src="crowd2.jpg" alt="Crowd 2"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // YouTube API integration for video control
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('promo-video', {
                events: {
                    'onReady': onPlayerReady
                }
            });
        }

        function onPlayerReady(event) {
            document.getElementById('play-button').addEventListener('click', function() {
                player.playVideo();
                this.style.display = 'none';
            });
            
            // Container hover detection for autoplay
            const container = document.querySelector('.video-player-container');
            let isHovering = false;
            
            container.addEventListener('mouseenter', function() {
                isHovering = true;
                setTimeout(() => {
                    if (isHovering) {
                        player.playVideo();
                        document.getElementById('play-button').style.opacity = '0';
                    }
                }, 1000); // 1 second delay before autoplay
            });
            
            container.addEventListener('mouseleave', function() {
                isHovering = false;
                player.pauseVideo();
                document.getElementById('play-button').style.opacity = '0.9';
            });
        }
    </script>
</body>
</html>
