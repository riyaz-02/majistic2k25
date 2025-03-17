<?php
// PayU Money Credentials
$client_id = "ce90a71710363ff5c1cbab97cf4a89f2d1ba6828b5e8a08b01d2343f24a753d3";
$client_secret = "cfebc9c516fefef64a0fcc0fc05f7f291661e6746698c1c8ecf573ddcf3816f6";
$auth_url = "https://secure.payu.in/auth";

// Step 1: Get Access Token
function getAccessToken($client_id, $client_secret, $auth_url) {
    $headers = array(
        "Content-Type: application/x-www-form-urlencoded"
    );

    $data = "grant_type=client_credentials&client_id=$client_id&client_secret=$client_secret";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $auth_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    return $result['access_token'] ?? null;
}

// Fetch Access Token
$access_token = getAccessToken($client_id, $client_secret, $auth_url);
if (!$access_token) {
    die("Failed to get access token.");
}

// Step 2: Initiate Payment Request
$payment_url = "https://secure.payu.in/_payment";

$payment_data = array(
    "key" => $client_id,
    "txnid" => uniqid(),
    "amount" => "500",
    "productinfo" => "Sample Product",
    "firstname" => "John",
    "email" => "john.doe@example.com",
    "phone" => "9876543210",
    "surl" => "https://yourwebsite.com/success.php",
    "furl" => "https://yourwebsite.com/failure.php",
    "service_provider" => "payu_paisa"
);

?>
<!-- Step 3: Redirect to PayU for Payment -->
<form action="<?php echo $payment_url; ?>" method="POST">
    <?php foreach ($payment_data as $key => $value) { ?>
        <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
    <?php } ?>
    <button type="submit">Pay Now</button>
</form>
