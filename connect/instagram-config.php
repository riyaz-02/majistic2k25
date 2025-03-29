<?php
/**
 * Instagram API Configuration
 * 
 * To use this:
 * 1. Create an Instagram Developer account at https://developers.facebook.com/
 * 2. Create a new app in the Facebook Developer portal
 * 3. Set up Instagram Basic Display API
 * 4. Generate a long-lived access token
 * 5. Add the token and user ID below
 */

// Instagram API credentials 
$instagram_config = [
    'access_token' => 'IGAAJiScC5GFVBZAFBiNUQxM0ZAQY21YS0FGaGhZAWjkwZAEVFNlZAQT3ktT2V4RzRtU0VGcTBkWWxkeHFsQ2ZAXLWJlek9aTjM0dlREUk5vMlZANaXF2UmlUUVRQUDJ6X2Fjb1pkMDFwa0RtcjhZAM3ZAjMnpSV040VC1MWXphLUlrclZApbwZDZD', // Replace with your access token
    'user_id' => 'majistic_jisce', // Replace with your Instagram user ID
    'app_id' => '671018858911829', // Replace with your app ID
    'app_secret' => '20aa5c4917ceceb6ffea8aacd7007297', // Replace with your app secret
    'redirect_uri' => 'https://majistic.org/socials/callback.php', // Replace with your callback URL
    'posts_limit' => 50, // Number of posts to fetch
    'instagram_username' => 'majistic_jisce', // Instagram username for display purposes
    'config_version' => '1.0.1' // Increment this when you change the config
];

// Set session flag if configuration has changed
if (!isset($_SESSION['config_version']) || $_SESSION['config_version'] !== $instagram_config['config_version']) {
    $_SESSION['config_changed'] = true;
    $_SESSION['config_version'] = $instagram_config['config_version'];
}

/**
 * Fetch Instagram posts using the Instagram Basic Display API
 */
function fetchInstagramPosts($config, $limit = null) {
    if (!$limit) {
        $limit = $config['posts_limit'];
    }
    
    // Check if we have a valid access token
    if (empty($config['access_token']) || $config['access_token'] === 'YOUR_INSTAGRAM_ACCESS_TOKEN') {
        error_log('Instagram API Error: No valid access token provided');
        return ['error' => true, 'message' => 'No valid access token provided'];
    }
    
    $url = "https://graph.instagram.com/me/media?fields=id,caption,media_type,media_url,thumbnail_url,permalink,timestamp&access_token={$config['access_token']}&limit={$limit}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout of 10 seconds
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification - for development only
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        error_log('Instagram API Error: ' . curl_error($ch));
        return ['error' => true, 'message' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        error_log('Instagram API Error: ' . $result['error']['message']);
        return ['error' => true, 'message' => $result['error']['message']];
    }
    
    return $result;
}

/**
 * Get Instagram profile information
 */
function getInstagramProfile($config) {
    $url = "https://graph.instagram.com/me?fields=id,username,account_type,media_count&access_token={$config['access_token']}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification - for development only
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        return null;
    }
    
    curl_close($ch);
    
    return json_decode($response, true);
}

/**
 * Get a refreshed long-lived access token
 */
function refreshInstagramToken($config) {
    if (empty($config['access_token'])) {
        return ['error' => true, 'message' => 'No access token to refresh'];
    }
    
    $url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token={$config['access_token']}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        return ['error' => true, 'message' => curl_error($ch)];
    }
    
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        return ['error' => true, 'message' => $result['error']['message']];
    }
    
    return $result; // Should contain new access_token and expires_in
}

/**
 * Cache Instagram posts to avoid frequent API calls
 */
function cacheInstagramPosts($posts) {
    $cache_file = __DIR__ . '/cache/instagram-posts.json';
    
    // Ensure cache directory exists
    if (!file_exists(__DIR__ . '/cache')) {
        mkdir(__DIR__ . '/cache', 0755, true);
    }
    
    $cache_data = [
        'timestamp' => time(),
        'username' => isset($GLOBALS['instagram_config']['instagram_username']) ? $GLOBALS['instagram_config']['instagram_username'] : '',
        'config_version' => isset($GLOBALS['instagram_config']['config_version']) ? $GLOBALS['instagram_config']['config_version'] : '',
        'posts' => $posts
    ];
    
    file_put_contents($cache_file, json_encode($cache_data));
}

/**
 * Get cached Instagram posts
 */
function getCachedInstagramPosts() {
    $cache_file = __DIR__ . '/cache/instagram-posts.json';
    
    if (!file_exists($cache_file)) {
        return null;
    }
    
    $cache_data = json_decode(file_get_contents($cache_file), true);
    
    // Check if cache is expired (older than 1 hour)
    if (time() - $cache_data['timestamp'] > 3600) {
        return null;
    }
    
    // Check if username has changed
    if (!isset($cache_data['username']) || 
        $cache_data['username'] !== $GLOBALS['instagram_config']['instagram_username']) {
        return null;
    }
    
    // Check if config version has changed
    if (!isset($cache_data['config_version']) || 
        $cache_data['config_version'] !== $GLOBALS['instagram_config']['config_version']) {
        return null;
    }
    
    return $cache_data['posts'];
}
