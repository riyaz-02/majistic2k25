<?php
session_start();

// Instagram API configuration
require 'instagram-config.php';

// Force clear cache if requested or if config has changed
$clear_cache = isset($_GET['clear_cache']) || isset($_SESSION['config_changed']);
unset($_SESSION['config_changed']);

// Try to get cached posts first (but skip if we need to clear cache)
$instagram_posts = $clear_cache ? null : getCachedInstagramPosts();

// If no cache or expired, fetch fresh posts
if (!$instagram_posts) {
    // Delete any existing cache file
    $cache_file = __DIR__ . '/cache/instagram-posts.json';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
    
    $instagram_posts = fetchInstagramPosts($instagram_config, $instagram_config['posts_limit']);
    
    // Cache the posts if successful
    if (!isset($instagram_posts['error'])) {
        cacheInstagramPosts($instagram_posts);
    }
}

// Get profile information if needed
$profile = null;
if (isset($instagram_config['show_profile_info']) && $instagram_config['show_profile_info']) {
    $profile = getInstagramProfile($instagram_config);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>maJIStic - Instagram Feed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include '../includes/links.php'; ?> <!-- Ensure this line is present to include the site icon -->
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include_once '../includes/header.php'; ?>

    <!-- Hero Section with Headline -->
    <section class="hero-section">
        <div class="container">
            <h1 class="headline">Connect with maJIStic</h1>
            <p class="subheadline">Experience the festival vibes through our Instagram feed</p>
        </div>
    </section>

    <!-- Instagram Feed Section -->
    <section class="instagram-feed">
        <div class="container">
            <div class="section-header">
                <h2>
                    <span class="insta-icon"><i class="fab fa-instagram"></i></span>
                    Latest from @<?php echo htmlspecialchars($instagram_config['instagram_username']); ?>
                </h2>
                <a href="https://www.instagram.com/<?php echo htmlspecialchars($instagram_config['instagram_username']); ?>/" target="_blank" class="follow-btn">
                    <i class="fab fa-instagram"></i> Follow Us
                </a>
            </div>
            
            <div class="instagram-grid">
                <?php if (isset($instagram_posts['data']) && count($instagram_posts['data']) > 0): ?>
                    <?php foreach ($instagram_posts['data'] as $index => $post): ?>
                        <div class="instagram-post" data-aos="fade-up" data-aos-delay="<?php echo ($index % 9) * 100; ?>">
                            <?php if ($post['media_type'] === 'VIDEO'): ?>
                                <div class="post-media video">
                                    <div class="playable-video">
                                        <video 
                                            src="<?php echo $post['media_url']; ?>" 
                                            poster="<?php echo isset($post['thumbnail_url']) ? $post['thumbnail_url'] : ''; ?>" 
                                            preload="metadata"
                                            playsinline
                                            controls>
                                        </video>
                                        <div class="video-controls">
                                            <div class="video-progress">
                                                <div class="video-progress-bar"></div>
                                            </div>
                                            <div class="video-time">0:00</div>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif ($post['media_type'] === 'CAROUSEL_ALBUM'): ?>
                                <div class="post-media carousel">
                                    <div class="carousel-icon"><i class="fas fa-clone"></i></div>
                                    <img src="<?php echo $post['media_url']; ?>" alt="Instagram Carousel">
                                </div>
                            <?php else: ?>
                                <div class="post-media">
                                    <img src="<?php echo $post['media_url']; ?>" alt="Instagram Image">
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-overlay">
                                <div class="post-bottom">
                                    <?php if (isset($post['caption'])): ?>
                                        <div class="post-caption">
                                            <p><?php echo substr($post['caption'], 0, 100) . (strlen($post['caption']) > 100 ? '...' : ''); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    <div class="post-meta">
                                        <a href="<?php echo $post['permalink']; ?>" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> View on Instagram
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback for when Instagram API fails -->
                    <div class="api-error">
                        <div class="error-icon"><i class="fas fa-exclamation-circle"></i></div>
                        <h3>Could not load Instagram posts</h3>
                        <p>Please visit our <a href="https://www.instagram.com/<?php echo htmlspecialchars($instagram_config['instagram_username']); ?>/" target="_blank">Instagram page</a> to see our latest updates.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Custom Script -->
    <script src="js/script.js"></script>
    <?php include_once '../includes/footer.php'; ?>
</body>
</html>
