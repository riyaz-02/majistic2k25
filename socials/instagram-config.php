<?php
/**
 * Instagram API Configuration
 * 
 * To use this:
 * 1. Create an Instagram Developer account
 * 2. Create a new app in the Facebook Developer portal
 * 3. Set up Instagram Basic Display API
 * 4. Generate a long-lived access token
 * 5. Add the token and user ID below
 */

// Instagram API credentials 
$instagram_config = [
    'access_token' => 'YOUR_INSTAGRAM_ACCESS_TOKEN', // Replace with your access token
    'user_id' => 'YOUR_INSTAGRAM_USER_ID', // Replace with your Instagram user ID
    'app_id' => 'YOUR_APP_ID', // Replace with your app ID
    'app_secret' => 'YOUR_APP_SECRET', // Replace with your app secret
    'redirect_uri' => 'https://jiscollege.ac.in/majistic/socials/callback.php', // Replace with your callback URL
    'posts_limit' => 12, // Number of posts to fetch
    'instagram_username' => 'majistic_jisce' // Instagram username for display purposes
];

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
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        return null;
    }
    
    curl_close($ch);
    
    return json_decode($response, true);
}
