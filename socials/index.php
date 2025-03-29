<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maJIStic - Social Feed</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include_once '../includes/header.php'; ?>

    <!-- Hero Section with Headline -->
    <section class="hero-section">
        <div class="container">
            <h1 class="headline">Connect with maJIStic</h1>
            <p class="subheadline">Experience the festival vibes through our social feed</p>
        </div>
    </section>

    <!-- Social Media Feed Section -->
    <section class="social-feed">
        <div class="container">
            <div class="section-header">
                <h2>
                    <span class="social-icon"><i class="fab fa-instagram"></i></span>
                    <span class="social-icon"><i class="fab fa-facebook"></i></span>
                    Latest Updates
                </h2>
                <div class="follow-buttons">
                    <a href="https://www.instagram.com/majistic_jisce/" target="_blank" class="follow-btn insta-btn">
                        <i class="fab fa-instagram"></i> Follow on Instagram
                    </a>
                    <a href="https://www.facebook.com/majistic" target="_blank" class="follow-btn fb-btn">
                        <i class="fab fa-facebook"></i> Like on Facebook
                    </a>
                </div>
            </div>
            
            <div class="social-grid">
                <!-- Instagram Post Example -->
                <div class="social-post instagram-post">
                    <div class="post-badge instagram-badge"><i class="fab fa-instagram"></i></div>
                    <blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="https://www.instagram.com/reel/DHxZnriNGB_/?utm_source=ig_embed&amp;utm_campaign=loading" data-instgrm-version="14"></blockquote>
                </div>
                
                <!-- Facebook Post Example -->
                <div class="social-post facebook-post">
                    <div class="post-badge facebook-badge"><i class="fab fa-facebook"></i></div>
                    <div class="fb-post" data-href="https://www.facebook.com/20531316728/posts/EXAMPLE1/"></div>
                </div>
                
                <!-- Instagram Post Example -->
                <div class="social-post instagram-post">
                    <div class="post-badge instagram-badge"><i class="fab fa-instagram"></i></div>
                    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/DHvZsVhy4S2/?utm_source=ig_web_copy_link&igsh=MzRlODBiNWFlZA=="></blockquote>
                </div>
                
                <!-- Facebook Post Example -->
                <div class="social-post facebook-post">
                    <div class="post-badge facebook-badge"><i class="fab fa-facebook"></i></div>
                    <div class="fb-post" data-href="https://www.facebook.com/20531316728/posts/EXAMPLE2/"></div>
                </div>
                
                <!-- Instagram Post Example -->
                <div class="social-post instagram-post">
                    <div class="post-badge instagram-badge"><i class="fab fa-instagram"></i></div>
                    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/EXAMPLE3/"></blockquote>
                </div>
                
                <!-- Facebook Post Example -->
                <div class="social-post facebook-post">
                    <div class="post-badge facebook-badge"><i class="fab fa-facebook"></i></div>
                    <div class="fb-post" data-href="https://www.facebook.com/20531316728/posts/EXAMPLE3/"></div>
                </div>
                
                <!-- Add more posts as needed - you can mix Instagram and Facebook posts in any order -->
                <!-- These will be hidden initially and shown when "Load More" is clicked -->
                <div class="social-post instagram-post hidden-post">
                    <div class="post-badge instagram-badge"><i class="fab fa-instagram"></i></div>
                    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/EXAMPLE4/"></blockquote>
                </div>
                
                <div class="social-post facebook-post hidden-post">
                    <div class="post-badge facebook-badge"><i class="fab fa-facebook"></i></div>
                    <div class="fb-post" data-href="https://www.facebook.com/20531316728/posts/EXAMPLE4/"></div>
                </div>
                
                <div class="social-post instagram-post hidden-post">
                    <div class="post-badge instagram-badge"><i class="fab fa-instagram"></i></div>
                    <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/EXAMPLE5/"></blockquote>
                </div>
            </div>
            
            <!-- Load more button -->
            <div class="load-more-container">
                <button id="load-more" class="load-more-btn">Load More</button>
            </div>
        </div>
    </section>

    <?php include_once '../includes/footer.php'; ?>

    <!-- Facebook SDK -->
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v13.0"></script>
    
    <!-- Instagram Embed Script -->
    <script async src="//www.instagram.com/embed.js"></script>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
</body>
</html>
