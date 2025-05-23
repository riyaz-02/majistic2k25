<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - maJIStic 2k25</title>

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

        .section-header::after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(45deg, #ff5e62, #ff9966);
            border-radius: 2px;
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
        // Add script to replace # image sources with nodp.png
        document.addEventListener('DOMContentLoaded', function () {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                if (img.src === window.location.href + '#' || img.getAttribute('src') === '#') {
                    img.src = 'images/nodp.png'; // Update this path to your no-profile image
                }
            });

            // Add animation to paragraphs on scroll
            const paragraphs = document.querySelectorAll('.styled-paragraph');

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
        <div class="section-container">
            <h2 class="container-header text-center">About the Cultural Fest</h2>
            <p class="styled-paragraph">
                <span class="highlight">maJIStic 2k25</span> is the annual cultural extravaganza of JIS College of
                Engineering, Kalyani, a prestigious institution under the renowned JIS Group. Known for its vibrant
                celebration of talent, creativity, and diversity, <span class="highlight">maJIStic 2k25</span> promises
                to be a spectacular convergence of art, culture, and innovation, showcasing the dynamic spirit of the
                students.
            </p>
            <p class="styled-paragraph">
                Set against the backdrop of JIS College of Engineering's state-of-the-art campus in Kalyani, West
                Bengal, this event is more than just a festival—it's a testament to the institution's legacy of academic
                brilliance and holistic development.
            </p>
            <p class="styled-paragraph">
                <span class="highlight">maJIStic 2k25</span> will feature an array of events, including:
            </p>
            <ul class="list-disc list-inside mb-4 styled-paragraph">
                <li><span class="highlight">Cultural Performances:</span> From electrifying dance battles to soulful musical renditions, students will have the stage to showcase their artistic flair.</li>
                <li><span class="highlight">Celebrity Performances:</span> Special appearances and performances by renowned artists and entertainers to captivate the audience.</li>
                <li><span class="highlight">Food and Fun:</span> A vibrant carnival atmosphere with food stalls, games, and a bustling flea market.</li>
            </ul>
            <p class="styled-paragraph">
                This grand event embodies the ethos of the JIS Group, which is dedicated to nurturing talent and
                fostering innovation. With a 20+ year legacy in education, JIS Group brings its values of excellence,
                creativity, and inclusivity to the forefront of <span class="highlight">maJIStic 2k25</span>.
            </p>
            <p class="styled-paragraph">
                Whether you are a student, alumnus, or visitor, <span class="highlight">maJIStic 2k25</span> offers a
                platform to connect, celebrate, and create memories that will last a lifetime. Join us as we embrace the
                magic of culture, the rhythm of celebration, and the joy of togetherness.
            </p>
            <p class="styled-paragraph">
                Be part of the fest, be part of the magic—<span class="highlight">maJIStic 2k25</span> awaits you!
            </p>
        </div>

        <div class="section-container">
            <h2 class="section-header">About Our College</h2>
            <p class="styled-paragraph">
                Our college, JIS College of Engineering, is a premier institution known for its academic excellence and
                vibrant campus life. Established in 2000, the college has been providing quality education and fostering
                a culture of innovation and creativity. With state-of-the-art infrastructure and a dedicated faculty, we
                strive to create an environment that nurtures the holistic development of our students.
            </p>
            <p class="styled-paragraph">
                The college offers a wide range of undergraduate and postgraduate programs in engineering, management,
                and other disciplines. Our students have consistently excelled in academics, sports, and extracurricular
                activities, making us proud with their achievements.
            </p>
            <p class="styled-paragraph">
                JIS College of Engineering is a college located in Kalyani, West Bengal, India. The college was
                established in 2000. The Institution is declared Autonomous by the University Grants Commission (UGC) in
                2011. In 2022, the college was accredited by NAAC with Grade-A. It is affiliated to Maulana Abul Kalam
                Azad University of Technology, West Bengal (MAKAUT, WB). The institute is ranked by NIRF in the range of
                201–250 in 2021. In Atal Ranking of Institutions on Innovation Achievements (ARIIA) 2020, the institute
                has secured a place in Band B (Rank Between 26th – 50th) among Private or Self-Financed
                College/Institutes in India. On 1 September 2020, the institute celebrated its 20th Birthday.
            </p>
        </div>

        <div class="section-container">
            <h2 class="section-header">About JIS Group</h2>
            <p class="styled-paragraph">
                Sardar Jodh Singh's unbeatable zeal and extraordinary entrepreneurship skills enabled him to be
                associated with many ventures into diverse sectors like dairy, transport, infrastructure, iron & steel,
                cargo, logistics, information technology, agro, overseas, movies, and much more. However, his
                path-breaking initiatives in the field of education are noteworthy and have earned him global
                recognition. The largest education service provider in Eastern India, the first college (JV) under the
                umbrella of JIS Group was Asansol Engineering College. And two years later, JIS followed with the
                establishment of JIS College of Engineering. There has been no stopping since. Presently, with 30
                Institutes, 140 Programs, and over 37000+ Students enrolled in the diverse academic courses, the best
                educational institutes in West Bengal are under the aegis of JIS Group.
            </p>
            <p class="styled-paragraph">
                The hub of academic brilliance and professional excellence, JIS Group continues to be a popular choice
                among students, teachers, and parents. Offering a wide range of new-age academic courses at the
                undergraduate, postgraduate and doctoral level, the best private colleges in Kolkata are under JIS
                Group. Accredited by various government bodies like AICTE, NAAC, NBA, PCI, BCI, DCI, MCI, NCHMCT,
                Academic Impact United Nations and affiliated to MAKAUT and WBSCTVESD, JIS Group aims to serve the
                society by being the torchbearer of knowledge, education, and employment.
            </p>
            <p class="styled-paragraph">
                The largest educational Group in Eastern India, JIS has received several rankings, awards, and accolades
                from various prestigious organizations, industry, and media houses like Zee 24 Ghanta, The Week, NIRF,
                India Today, Outlook-I-Care, Careers 360, ARIIA, FICCI, Digital Learning, and many more. With conscious
                and consistent progressive efforts, JIS Group has emerged as one of the largest educational groups of
                India and aims to spread a 20+-year-old legacy by its collaboration with 550+ Industries, 11 Chambers,
                73 universities, and 21 countries.
            </p>
        </div>

        <!-- Committee Members Section -->
        
        </div>
        <?php include 'includes/footer.php'; ?>
</body>

</html>