<?php
// Updated include path to match fetch_user.php
require_once __DIR__ . '/../includes/db_config.php';

if (isset($_GET['jis_id'])) {
    $jis_id = $_GET['jis_id'];
    $stmt = $conn->prepare("UPDATE registrations SET checkin_1 = 'Yes', checkin_1_timestamp = NOW() WHERE jis_id = ?");
    $stmt->bind_param("s", $jis_id);

    if ($stmt->execute()) {
        echo "✅ Check-In Successful!";
    } else {
        echo "❌ Failed to check-in.";
    }
}
?>
