<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Committee - maJIStic 2k25</title>

    <!-- Added Google Fonts - Roboto for headings -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('images/pageback.png');
            background-repeat: repeat-y !important;
            background-size: 100% !important;
            background-position: top center !important;
            background-attachment: fixed !important; /* Changed from initial to fixed */
            min-height: 100vh; /* Ensure minimum height covers viewport */
            width: 100%; /* Ensure full width */
            overflow-x: hidden; /* Prevent horizontal scrolling */
            font-size: 1.05rem;
            /* Increased base font size */
        }

        /* Add this to ensure background works on all device sizes */
        html {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
        }

        .content-container {
            max-width: 1300px;
            /* Increased from 1200px */
            margin: 0 auto;
            color: white;
            backdrop-filter: blur(10px);
            padding: 25px;
            /* Slightly increased padding */
            border-radius: 10px;
            margin-top: 70px;
            width: 95%;
            /* Using percentage width for better responsiveness */
        }

        .highlight {
            color: #e53e3e;
            font-weight: bold;
        }

        .container-header {
            font-family: 'Roboto', sans-serif;
            padding: 40px 0 20px;
            position: relative;
            letter-spacing: 2px;
            font-size: 2.2rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 35px;
            background: linear-gradient(45deg, #ff5e62, #ff9966);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .container-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(45deg, #ff5e62, #ff9966);
            border-radius: 2px;
        }

        .styled-paragraph {
            line-height: 1.8;
            font-size: 1.08rem;
            /* Increased from 1.05rem */
            letter-spacing: 0.3px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);
            padding: 10px 15px;
            /* Removed the red left border */
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .styled-paragraph:hover {
            /* Removed the border width increase */
            background-color: rgba(255, 255, 255, 0.05);
        }

        .member-card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .member-card {
            background-color: rgba(26, 26, 26, 0.8);
            border-radius: 15px;
            text-align: center;
            padding: 20px 15px;
            width: 220px;
            /* Increased from 200px */
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-bottom: 5px solid #e53e3e;
            backdrop-filter: blur(5px);
        }

        .member-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
        }

        .member-card img {
            width: 130px;
            /* Increased from 120px */
            height: 130px;
            /* Increased from 120px */
            border-radius: 50%;
            margin: 0 auto 15px;
            object-fit: cover;
            border: 3px solid rgba(229, 62, 62, 0.5);
            transition: all 0.3s ease;
        }

        .member-card:hover img {
            border-color: #e53e3e;
            transform: scale(1.05);
        }

        .member-card h3 {
            font-size: 1.15em;
            /* Increased from 1.1em */
            color: #fff;
            margin-bottom: 5px;
        }

        .member-card p {
            font-size: 0.95em;
            /* Increased from 0.9em */
            color: #ccc;
            margin-bottom: 10px;
        }

        .social-icons {
            margin-top: 12px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .social-icons a {
            color: #ccc;
            text-decoration: none;
            font-size: 1.2em;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            color: #e53e3e;
            transform: scale(1.2);
        }

        .section-container {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 15px;
            padding: 35px;
            /* Increased from 30px */
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            /* Removed the red left border */
            transition: all 0.3s ease;
            margin-top: 50px;
            /* Added space between sections */
        }

        .section-container:hover {
            background-color: rgba(0, 0, 0, 0.4);
            transform: translateY(-5px);
        }

        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.15em;
            /* Slightly increased font size */
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: #e53e3e;
        }

        .section-header {
            font-family: 'Roboto', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
            margin-bottom: 30px;
            padding-top: 40px;
            /* Added more padding above */
            background: linear-gradient(45deg, #ff5e62, #ff9966);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0px 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* Increase font size for the Committee Members header */
        .main-header {
            font-size: 3rem !important; /* Increased from 2.2rem */
            margin-bottom: 40px;
        }

        /* Added styles for the notification box */
        .notification-box {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            padding: 25px;
            margin: 30px auto;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            border-left: 5px solid #e53e3e;
            backdrop-filter: blur(10px);
        }

        .notification-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
            text-align: center;
        }

        .meeting-details {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin: 20px 0;
        }

        .meeting-item {
            background: rgba(229, 62, 62, 0.2);
            padding: 12px 20px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .meeting-item i {
            color: #e53e3e;
        }

        /* Responsive styles - Updated for better mobile experience */
        @media (max-width: 1200px) {
            .content-container {
                width: 95%;
            }
        }

        @media (max-width: 992px) {
            .member-card {
                width: 200px;
            }

            .section-container {
                padding: 30px 25px;
            }
        }

        @media (max-width: 768px) {
            .member-card-container {
                gap: 15px;
            }

            .member-card {
                width: calc(50% - 15px);
                max-width: 180px;
            }

            .member-card img {
                width: 100px;
                height: 100px;
            }

            .styled-paragraph {
                font-size: 1rem;
            }

            .section-container {
                padding: 25px 20px;
            }
        }

        @media (max-width: 576px) {
            .member-card {
                width: calc(50% - 10px);
                padding: 15px 10px;
                margin: 5px;
                max-width: 160px;
            }

            .member-card img {
                width: 90px;
                height: 90px;
            }

            .member-card h3 {
                font-size: 1em;
            }

            .member-card p {
                font-size: 0.8em;
            }

            .styled-paragraph {
                font-size: 0.95rem;
            }

            .section-container {
                padding: 20px 15px;
            }

            .social-icons {
                gap: 5px;
            }

            .social-icons a {
                font-size: 1em;
            }
        }

        @media (max-width: 400px) {
            .member-card {
                max-width: 140px;
                padding: 10px 5px;
            }

            .member-card img {
                width: 80px;
                height: 80px;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle all image errors by setting default image
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                // Check if image source is empty, #, or invalid
                if (img.src === window.location.href + '#' || 
                    img.getAttribute('src') === '#' || 
                    img.getAttribute('src') === '' || 
                    img.getAttribute('src') === null) {
                    img.src = 'images/nodp.png';
                }
                
                // Add error handler for broken images
                img.onerror = function() {
                    this.src = 'images/nodp.png';
                    this.onerror = null; // Prevent infinite loops
                };
            });

            // Add animation to paragraphs on scroll
            const paragraphs = document.querySelectorAll('.styled-paragraph, .notification-box');

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            paragraphs.forEach(paragraph => {
                paragraph.style.opacity = '0';
                paragraph.style.transform = 'translateY(20px)';
                paragraph.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(paragraph);
            });
        });
    </script>
</head>

<body>
    <?php include 'includes/links.php'; ?>
    <?php include 'includes/header.php'; ?>

    <!-- About Section -->
    <div class="container mx-auto p-8 content-container">
        <!-- Committee Members Section -->
        <div class="section-container">
            <h1 class="section-header main-header">Committee Members</h1>
            
            <div class="notification-box">
                <div class="notification-title">
                    Committee Members are not Finalized yet. <br><br>The Page will be updated soon after General Meeting.
                </div>
                
                <div class="notification-title">
                    General Meeting Announcement
                </div>
                
                <div class="meeting-details">
                    <div class="meeting-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Monday, 24 March 2025</span>
                    </div>
                    <div class="meeting-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>College Auditorium, JIS Campus</span>
                    </div>
                </div>
                
            </div>
            
            <?php
            /* Committee members data is commented out until finalized
            $sections = [
                'Chief Patron' => [
                    ['name' => 'Mr. Taranjit Singh', 'role' => 'MD, JIS Group', 'image' => 'https://i.postimg.cc/9M6wFdqc/Mr-Taranjit-Singh.jpg', 'social' => ['twitter' => 'https://x.com/jisgroupindia/status/1811268230041989247', 'facebook' => 'https://www.facebook.com/JISGroupEducationalInitiatives', 'instagram' => 'https://www.instagram.com/jisgroup_official/#', 'linkedin' => 'https://www.linkedin.com/posts/jisgroup_md-jis-group-honoured-sardar-taranjit-activity-7100786565513166848-Tvu4']],
                ],
                'Patrons' => [
                    ['name' => 'Mr. Simarpreet Singh', 'role' => 'Director, JIS Group', 'image' => 'https://i.postimg.cc/13dmQ9FQ/Mr-Simarpreet-Singh.jpg', 'social' => ['twitter' => 'https://x.com/jisgroupindia/status/1721090277031776658', 'facebook' => 'https://www.facebook.com/JISGroupEducationalInitiatives', 'instagram' => 'https://www.instagram.com/simarpreet4199/', 'linkedin' => 'https://in.linkedin.com/in/simarpreet-singh-66aa2733']],
                    ['name' => 'Jaspreet Kaur', 'role' => 'Director, JIS Group', 'image' => 'https://i.postimg.cc/YqwZTXhp/Jaspreet-kaur.jpg', 'social' => ['twitter' => 'https://en.wikipedia.org/wiki/Jaspreet_Kaur', 'facebook' => 'https://www.facebook.com/JISGroupEducationalInitiatives', 'instagram' => 'https://www.instagram.com/jisgroup_official/p/C4XP_key6qa/?hl=en&img_index=1', 'linkedin' => 'https://www.linkedin.com/in/jaspreet-kaur-b121292/']],
                    ['name' => 'Dr. Partha Sarkar', 'role' => 'Principal, JIS College of Engineering', 'image' => 'https://i.postimg.cc/jqGmM9Pk/principal.jpg', 'social' => ['twitter' => 'https://x.com/JisCollege/status/1851573882840633570', 'facebook' => 'https://www.facebook.com/OfficialJISCE', 'instagram' => 'https://www.instagram.com/jiscollege/#', 'linkedin' => 'https://in.linkedin.com/in/dr-partha-sarkar-26580018']],
                ],
                'Advisors' => [
                    ['name' => 'Dr. Debashis Sanki', 'role' => 'Dy. Registrar, JIS College of Engineering', 'image' => 'https://i.postimg.cc/6pSvZdDR/Debasish-Sanki.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Mentor' => [
                    ['name' => 'Dr. Sandip Ghosh', 'role' => 'HOD, ME Dept, JIS College of Engineering', 'image' => 'https://i.ibb.co/MyJfnXN3/Sandip-Ghosh.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Convener' => [
                    ['name' => 'Dr. Madhura Chakraborty', 'role' => 'Convener,<br>Mob: 7980979789</br>', 'image' => 'https://i.ibb.co/XrNTgT4p/Madhura-chakraborty.webp', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Dr. Proloy Ghosh', 'role' => 'Convener,<br>Mob: 7980532913</br>', 'image' => 'https://i.postimg.cc/FK6XNKkh/Proloy-Ghosh.png', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/profile.php?id=100009306949586', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Sound, Light and Stage Management Team' => [
                    ['name' => 'Anamitra Mondal', 'role' => 'IT, 4th YR,<br>Mob: 6280654490</br>', 'image' => 'https://i.ibb.co/hbhDVr2/Anamitra-Mondal.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Arnab Das', 'role' => 'IT, 4th YR,<br>Mob:9749536449</br>', 'image' => 'https://i.postimg.cc/52yqc9B4/Arnab-Das.jpg', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/share/1FKGdg6mY3/?mibextid=wwXIfr', 'instagram' => 'https://www.instagram.com/no_ex1stence?igsh=MXZzMnRzbzBvcnRvZA%3D%3D&utm_source=qr', 'linkedin' => '#']],
                    ['name' => 'Alok Thakur', 'role' => 'ECE, 3rd YR', 'image' => 'https://i.postimg.cc/kMWTw5cL/Alok-Thakur.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Sponsorship & Marketing Team' => [
                    ['name' => 'Snehal Bhowmick', 'role' => 'ECE, 4th YR,<br>Mob: 9674558906</br>', 'image' => 'https://i.ibb.co/5gRJP9wG/Snehal-da.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Gaurav Kumar Mehta', 'role' => 'CSE, 3rd YR', 'image' => 'https://i.postimg.cc/NFx7t5CM/Gaurav-Kumar-Mehta.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Priyadeep Mitra', 'role' => 'CSE, 3rd YR', 'image' => 'https://i.postimg.cc/JnNj3sLq/Priyadeep-Mitra.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
            
                ],
                'Website Development Team' => [
                    ['name' => 'Priyanshu Nayan', 'role' => 'CSE, 3rd YR,<br>Mob: 7004706722</br>', 'image' => 'https://i.postimg.cc/bvdKqkhC/Priyanshu-Nayan.jpg', 'social' => ['twitter' => 'https://x.com/priyanshunayan9', 'facebook' => 'https://www.facebook.com/priyanshu.nayan.17/', 'instagram' => 'https://www.instagram.com/priyanshunayan/', 'linkedin' => 'https://www.linkedin.com/in/priyanshu-nayan/']],
                    ['name' => 'Sk Riyaz', 'role' => 'CSE, 3rd YR,<br>Mob: 7029621489</br>', 'image' => 'https://i.ibb.co/zg57yyk/riyaz-profile-1.jpg', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/sk.riyaz.562329', 'instagram' => '#', 'linkedin' => 'https://www.linkedin.com/in/skriyaz1/']],
                    ['name' => 'Ronit Pal', 'role' => 'CSE, 3rd YR', 'image' => 'https://i.postimg.cc/N071myYx/Ronit-Pal.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Mohit Kumar', 'role' => 'CSE, 3rd YR', 'image' => 'https://i.postimg.cc/N0pKQRBZ/Mohit-Kumar.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Deisgn Team' => [
                    ['name' => 'Harsh Kumar Shaw', 'role' => 'CSE M.Tech, 1st YR', 'image' => 'https://i.ibb.co/vxTNy8Xk/Harsh-Kumar-Shaw.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Eshanu Mondal', 'role' => 'BCA, 3rd YR,<br>Mob: 9002540842</br>', 'image' => 'https://i.ibb.co/vvP7QybG/Eshanu-Mondal.jpg', 'social' => ['twitter' => 'https://x.com/EshanuMondal08', 'facebook' => 'https://www.facebook.com/eshanu.mondal.12', 'instagram' => 'https://www.instagram.com/mondaleshanu?igsh=ZWFqZHl5OWt4NzI1', 'linkedin' => '#']],
                ],
                'Cultural Team' => [
                    ['name' => 'Dipanwita Lahiri', 'role' => 'Drama, 3rd YR, BCA, <br>Mob: 8653384930</br>', 'image' => 'https://i.postimg.cc/x8QyBKRh/Dipanwita-Lahiri.jpg', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/profile.php?id=100069767917332&mibextid=ZbWKwL', 'instagram' => 'https://www.instagram.com/dipanwita_glorious_night?igsh=MXV6aGxxY2l6OXFzeA==', 'linkedin' => '#']],
                    ['name' => 'Krishnasish Bose', 'role' => 'Dance, 4th YR, IT<br>Mob: 9531602043</br>', 'image' => 'https://i.postimg.cc/J7Kk1V4G/Krishnasish-Bose.jpg', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/share/1CKHsdSCAL/', 'instagram' => 'https://www.instagram.com/__.krishnasish.bose.__/profilecard/?igsh=Mjl5ejRkemI5M2Vv', 'linkedin' => 'https://www.linkedin.com/in/eshanu-mondal-7692a4317?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app']],
                    ['name' => 'Debalina Talukder ', 'role' => 'Dance, 3rd, YR, CSE', 'image' => 'https://i.postimg.cc/tg156RJS/Debalina-Talukder.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Jotisingdha Das', 'role' => 'Anchoring and Recitation, 3rd YR, AGE,<br>Mob: 9749284221</br>', 'image' => 'https://i.postimg.cc/KzSngndj/Jotisnigdha-Das.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Amit Kumar', 'role' => 'Anchoring and Recitation, 2nd YR, BBA', 'image' => 'https://i.ibb.co/9mgCss3y/Amit-Kumar.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Ayush Agarwal', 'role' => 'Instrument and Band, 4th YR, CSE<br>Mob: 6297076034</br>', 'image' => 'https://i.postimg.cc/xTYtZSp8/Ayush.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Debopriya Das', 'role' => 'Instrument and Band, 3rd YR, ECE', 'image' => 'https://i.postimg.cc/4xwcxD18/Debopriya-Das.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Upasana Paul', 'role' => 'Fashion Show, 4rd YR, ECE,<br>Mob: 6291324934</br>', 'image' => 'https://i.postimg.cc/DZPbJRrp/Upasana-Paul.jpg', 'social' => ['twitter' => '#', 'facebook' => 'https://www.facebook.com/sromoni.paul?mibextid=ZbWKwL', 'instagram' => 'https://www.instagram.com/_steller._.flower_?igsh=MXhxbmZ5bW5oMmxu', 'linkedin' => 'https://www.linkedin.com/in/upasana-paul-294360244?utm_source=share&utm_campaign=share_via&utm_content=profile&utm_medium=android_app']],
                ],
                'Logistics Management Team' => [
                    ['name' => 'Soumili Ghosh', 'role' => 'CSE, 4th YR<br>Mob: 6289878908</br>', 'image' => 'https://i.ibb.co/qYCjbWtk/Screenshot-2025-03-17-233012.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Ayush Gupta ', 'role' => 'ECE, 3rd YR', 'image' => 'https://i.ibb.co/JjZRdvhr/Ayush-Gupta-3.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Invitation & Reception Team' => [
                    ['name' => 'Atmika Paul', 'role' => '3rd YR, BME<br>Mob: 9830334901</br>', 'image' => 'https://i.postimg.cc/qqZq2rzw/Atmika-Paul.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                ],
                'Security & Crowd Management Team' => [
                    ['name' => 'Devashish Basak', 'role' => 'IT, 4th YR<br>Mob: 9883334724</br>', 'image' => 'https://i.ibb.co/TxKvsXNm/Devashish.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Sagnik Ghosh', 'role' => 'CSE, 2nd YR', 'image' => 'https://i.ibb.co/Y4x7CssW/Sagnik-ghosh.jpg', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']], 
                    
                ],
                'Food and Water Management Team' => [
                    ['name' => 'Subhajit Saha', 'role' => 'CSE, M.Tech, 1st YR<br>Mob: 8942976671</br>', 'image' => 'https://i.postimg.cc/Zn7JfZF0/Rick.png', 'social' => ['twitter' => '#', 'facebook' => '#', 'instagram' => '#', 'linkedin' => '#']],
                    ['name' => 'Gourab Nandi', 'role' => 'ECE, 3rd YR', 'image' => 'https://i.postimg.cc/1zZtGJ5k/GOURAB-NANDI.jpg', 'social' => ['twitter' => 'https://x.com/GOURABNANDI2004?s=09', 'facebook' => 'https://www.facebook.com/Gourab600?mibextid=ZbWKwL', 'instagram' => 'https://www.instagram.com/bekar_b.tech_wala?igsh=ZTZuZG5icGZxeHls', 'linkedin' => 'https://www.linkedin.com/in/gourabnandi2004']],
                ],
            ];

            foreach ($sections as $sectionTitle => $members) {
                echo "<div class='mb-5'>";
                echo "<h2 class='text-2xl font-bold mb-3 text-center underline'>{$sectionTitle}</h2>";
                echo "<div class='member-card-container'>";
                foreach ($members as $member) {
                    echo "<div class='member-card'>";
                    echo "<img src='{$member['image']}' alt='Avatar' class='mx-auto' onerror=\"this.src='images/nodp.png';\" style='object-fit: cover;'>";
                    echo "<h3 class='text-lg font-semibold mt-2'>{$member['name']}</h3>";
                    echo "<p class='text-sm'>{$member['role']}</p>";
                    echo "<div class='social-icons'>";
                    echo "<a href='{$member['social']['twitter']}' target='_blank'><i class='fab fa-twitter'></i></a>";
                    echo "<a href='{$member['social']['facebook']}' target='_blank'><i class='fab fa-facebook'></i></a>";
                    echo "<a href='{$member['social']['instagram']}' target='_blank'><i class='fab fa-instagram'></i></a>";
                    echo "<a href='{$member['social']['linkedin']}' target='_blank'><i class='fab fa-linkedin'></i></a>";
                    echo "</div>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
            }
            */
            ?>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>