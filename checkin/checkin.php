<?php
// Use the direct check-in specific database config
require_once __DIR__ . '/db_config_checkin.php';

// Check if database connection was successful
if (!isset($conn) || $conn->connect_error) {
    echo "❌ Database connection error. Please contact the administrator.";
    error_log("Check-in system connection error in checkin.php: " . ($conn ? $conn->connect_error : "Connection object not created"));
    exit;
}

if (isset($_GET['jis_id'])) {
    $jis_id = trim($_GET['jis_id']);
    $type = isset($_GET['type']) ? $_GET['type'] : 'student'; // Default to student if not specified
    $day = isset($_GET['day']) ? intval($_GET['day']) : 2; // Default to Day 2 for this update
    
    // Fix for undefined array key warning by adding isset check
    if (preg_match('/^jis(u)?\/\d{4}\/\d+$/i', $jis_id, $matches)) {
        $prefix = isset($matches[1]) && $matches[1] ? 'JISU' : 'JIS';
        $jis_id = preg_replace('/^jis(u)?/i', $prefix, $jis_id);
    }
    
    $tableName = ($type === 'alumni') ? 'alumni_registrations' : 'registrations';
    $idColumn = ($type === 'alumni') ? 'id' : 'id';
    
    // First try to find exact match
    $stmt = $conn->prepare("SELECT $idColumn FROM $tableName WHERE jis_id = ?");
    $stmt->bind_param("s", $jis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0 && stripos($jis_id, 'jis') === 0) {
        // If not found and starts with JIS, try case-insensitive search
        $stmt->close();
        $like_pattern = $conn->real_escape_string($jis_id);
        $stmt = $conn->prepare("SELECT $idColumn, jis_id FROM $tableName WHERE jis_id LIKE ? LIMIT 1");
        $like_pattern = "%" . substr($like_pattern, 4) . "%"; // Remove JIS part for flexible matching
        $stmt->bind_param("s", $like_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Use the exact JIS ID from the database
            $jis_id = $row['jis_id'];
        }
    }
    
    // Determine which column to update based on day parameter
    $checkinField = "checkin_" . $day;
    $timestampField = "checkin_" . $day . "_timestamp";
    
    // Perform the check-in
    $stmt = $conn->prepare("UPDATE $tableName SET $checkinField = 'Yes', $timestampField = NOW() WHERE jis_id = ?");
    $stmt->bind_param("s", $jis_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $participantType = ($type === 'alumni') ? 'Alumni' : 'Student';
        echo "✅ $participantType Day $day Check-In Successful!";
    } else {
        echo "❌ Failed to check-in. No matching record found for JIS ID: " . htmlspecialchars($jis_id);
    }
} else {
    echo "❌ No JIS ID provided.";
}
?>
