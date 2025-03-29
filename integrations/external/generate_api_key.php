<?php
// Generate a 32-character random string for your API key
$api_key = bin2hex(random_bytes(32));
echo "Your secure API key: " . $api_key;
?>
