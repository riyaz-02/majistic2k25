<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Nexus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            min-height: 100vh;
            color: #fff;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 40px 0;
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #00d4ff;
            text-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
        }

        /* Carousel */
        #events-carousel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .swiper-slide {
            width: 320px;
            height: 450px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease;
            cursor: pointer;
            background-size: cover;
            background-position: center;
        }

        .swiper-slide:hover {
            transform: translateY(-10px);
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);
        }

        .swiper-slide span {
            display: inline-block;
            padding: 6px 15px;
            background: #00d4ff;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .swiper-slide h2 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .explore-btn {
            background: #ff0066;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s ease;
        }

        .explore-btn:hover {
            transform: scale(1.05);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #fff;
            color: #333;
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            border-radius: 15px;
            padding: 30px;
            overflow-y: auto;
            position: relative;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            background: none;
            border: none;
        }

        .modal-content h1 {
            color: #ff0066;
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .modal-content h2 {
            color: #1a1a2e;
            font-size: 1.3rem;
            margin: 20px 0 10px;
        }

        .modal-content p, .modal-content ul {
            line-height: 1.6;
            margin-bottom: 15px;
            color: #444;
        }

        .modal-content ul { padding-left: 20px; }

        .register-link {
            display: inline-block;
            background: #00d4ff;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s ease;
        }

        .register-link:hover {
            transform: scale(1.05);
        }

        /* Specific Event Styles */
        .swiper-slide--one span { background: #ff6b6b; }
        .swiper-slide--two span { background: #4ecdc4; }
        .swiper-slide--three span { background: #ffa07a; }
        .swiper-slide--four span { background: #98ddca; }
        .swiper-slide--five span { background: #ffd166; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Events Nexus</h1>
    </div>

    <section id="events-carousel">
        <div class="swiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide swiper-slide--one" style="background-image: url('https://i.postimg.cc/bNbM15My/1.png');">
                    <div class="slide-content">
                        <span>Taal Se Taal Mila</span>
                        <h2>Groove to Win, Dance to Shine!</h2>
                        <button class="explore-btn" onclick="openModal('modal1')">Explore</button>
                    </div>
                </div>
                <div class="swiper-slide swiper-slide--two" style="background-image: url('https://i.postimg.cc/tgrmXG15/2.png');">
                    <div class="slide-content">
                        <span>Actomania</span>
                        <h2>Lights, Camera, Action!</h2>
                        <button class="explore-btn" onclick="openModal('modal2')">Explore</button>
                    </div>
                </div>
                <div class="swiper-slide swiper-slide--three" style="background-image: url('https://i.postimg.cc/9QYnn7Cs/3.png');">
                    <div class="slide-content">
                        <span>The Poetry Slam</span>
                        <h2>Words That Echo</h2>
                        <button class="explore-btn" onclick="openModal('modal3')">Explore</button>
                    </div>
                </div>
                <div class="swiper-slide swiper-slide--four" style="background-image: url('https://i.postimg.cc/KzRpjpBy/4.png');">
                    <div class="slide-content">
                        <span>Jam Room</span>
                        <h2>Feel the Beat!</h2>
                        <button class="explore-btn" onclick="openModal('modal4')">Explore</button>
                    </div>
                </div>
                <div class="swiper-slide swiper-slide--five" style="background-image: url('https://i.postimg.cc/bvYBcwvr/5.png');">
                    <div class="slide-content">
                        <span>Fashion Fiesta</span>
                        <h2>Own the Glam!</h2>
                        <button class="explore-btn" onclick="openModal('modal5')">Explore</button>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- Modals -->
    <div id="modal1" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('modal1')">×</button>
            <h1>Taal Se Taal Mila</h1>
            <p>An exhilarating inter-college dance competition showcasing dynamic choreography and boundless creativity.</p>
            <h2>Rules & Regulations</h2>
            <ul>
                <li>Max 10 participants per team</li>
                <li>Performance time: 5-7 minutes</li>
                <li>No vulgarity allowed</li>
                <li>Props must be pre-approved</li>
            </ul>
            <h2>Coordinator</h2>
            <p>Upasana Paul<br>+91 6291324934</p>
            <h2>Register</h2>
            <a href="https://forms.gle/dance-registration" target="_blank" class="register-link">Register Now</a>
        </div>
    </div>

    <div id="modal2" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('modal2')">×</button>
            <h1>Actomania</h1>
            <p>A captivating inter-college drama competition featuring aspiring actors and storytellers.</p>
            <h2>Rules & Regulations</h2>
            <ul>
                <li>Teams of 5-15 members</li>
                <li>Performance: 15-20 minutes</li>
                <li>Original scripts preferred</li>
                <li>Minimal props allowed</li>
            </ul>
            <h2>Coordinator</h2>
            <p>Dipanwita Lahiri<br>+91-8653384930</p>
            <h2>Register</h2>
            <a href="https://forms.gle/drama-registration" target="_blank" class="register-link">Register Now</a>
        </div>
    </div>

    <div id="modal3" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('modal3')">×</button>
            <h1>The Poetry Slam</h1>
            <p>Share your powerful verses with an enthusiastic audience in this poetry slam.</p>
            <h2>Rules & Regulations</h2>
            <ul>
                <li>Individual participation</li>
                <li>3-minute limit</li>
                <li>Original work only</li>
                <li>No props allowed</li>
            </ul>
            <h2>Coordinator</h2>
            <p>TBA</p>
            <h2>Register</h2>
            <a href="https://forms.gle/poetry-registration" target="_blank" class="register-link">Register Now</a>
        </div>
    </div>

    <div id="modal4" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('modal4')">×</button>
            <h1>Jam Room</h1>
            <p>A thrilling inter-college band competition showcasing musical talent.</p>
            <h2>Rules & Regulations</h2>
            <ul>
                <li>Bands of 3-8 members</li>
                <li>Performance: 10-12 minutes</li>
                <li>One cover song allowed</li>
                <li>Basic instruments provided</li>
            </ul>
            <h2>Coordinator</h2>
            <p>Ayush Agarwal<br>+91-6297076034</p>
            <h2>Register</h2>
            <a href="https://forms.gle/music-registration" target="_blank" class="register-link">Register Now</a>
        </div>
    </div>

    <div id="modal5" class="modal">
        <div class="modal-content">
            <button class="close-btn" onclick="closeModal('modal5')">×</button>
            <h1>Fashion Fiesta</h1>
            <p>A dazzling inter-college fashion competition where style meets creativity.</p>
            <h2>Rules & Regulations</h2>
            <ul>
                <li>Teams of 5-12 members</li>
                <li>Show: 8-10 minutes</li>
                <li>Theme-based designs</li>
                <li>Music pre-submitted</li>
            </ul>
            <h2>Coordinator</h2>
            <p>TBA</p>
            <h2>Register</h2>
            <a href="https://forms.gle/fashion-registration" target="_blank" class="register-link">Register Now</a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".swiper", {
            slidesPerView: "auto",
            spaceBetween: 30,
            centeredSlides: true,
            loop: true,
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            }
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>