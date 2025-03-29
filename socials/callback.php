<?php
session_start();
require_once 'instagram-config.php';

// Handle the callback from Instagram OAuth
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // Exchange code for access token
    $token_url = 'https://api.instagram.com/oauth/access_token';
    $token_data = [
        'client_id' => $instagram_config['app_id'],
        'client_secret' => $instagram_config['app_secret'],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $instagram_config['redirect_uri'],
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $token_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_response = json_decode($response, true);
    
    if (isset($token_response['access_token'])) {
        // Get a long-lived token
        $long_lived_token_url = "https://graph.instagram.com/access_token";
        $long_lived_token_data = [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $instagram_config['app_secret'],
            'access_token' => $token_response['access_token']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $long_lived_token_url . '?' . http_build_query($long_lived_token_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        $long_lived_token = json_decode($response, true);
        
        if (isset($long_lived_token['access_token'])) {
            // Save token for later use (you might want to store this in a database)
            $_SESSION['instagram_access_token'] = $long_lived_token['access_token'];
            $_SESSION['instagram_token_expires'] = time() + $long_lived_token['expires_in'];
            
            echo '<h1>Authentication Successful!</h1>';
            echo '<p>Your access token has been generated and saved. You can use it in your instagram-config.php file:</p>';
            echo '<code>' . $long_lived_token['access_token'] . '</code>';
            echo '<p>This token will expire in approximately 60 days. Remember to refresh it before expiry.</p>';
            echo '<p><a href="index.php">Return to social feed</a></p>';
            exit;
        }
    }
    
    // If we get here, something went wrong
    echo '<h1>Authentication Failed</h1>';
    echo '<p>Failed to get access token. Please try again.</p>';
    echo '<pre>' . print_r($token_response, true) . '</pre>';
    exit;
}

// If no code parameter, redirect to auth
$auth_url = "https://api.instagram.com/oauth/authorize?client_id={$instagram_config['app_id']}&redirect_uri={$instagram_config['redirect_uri']}&scope=user_profile,user_media&response_type=code";
header("Location: $auth_url");
exit;
