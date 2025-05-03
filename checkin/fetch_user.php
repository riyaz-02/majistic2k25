<?php
// Use the direct check-in specific database config
require_once __DIR__ . '/db_config_checkin.php';

// Check if database connection was successful
if (!isset($conn) || $conn->connect_error) {
    echo "<div class='error-message'>";
    echo "❌ Database connection not established.<br>";
    echo "Check-in system couldn't connect to the database.<br>";
    echo "Error: " . ($conn ? $conn->connect_error : "Connection object not created") . "<br>";
    echo "Please contact the administrator for assistance.";
    echo "</div>";
    exit;
}

if (isset($_GET['jis_id'])) {
    $jis_id = trim($_GET['jis_id']);
    
    if (empty($jis_id)) {
        echo "<div class='error-message'>❌ Invalid JIS ID: Empty value provided.</div>";
        exit;
    }
    
    // Fix for undefined array key warning by adding a default empty value for matches[1]
    if (preg_match('/^jis(u)?\/\d{4}\/\d+$/i', $jis_id, $matches)) {
        $prefix = isset($matches[1]) && $matches[1] ? 'JISU' : 'JIS';
        $jis_id = preg_replace('/^jis(u)?/i', $prefix, $jis_id);
    }

    $found = false;
    $isAlumni = false;

    // First check regular student registrations
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ?");
    if (!$stmt) {
        echo "<div class='error-message'>❌ Prepare failed: " . $conn->error . "</div>";
        exit;
    }

    $stmt->bind_param("s", $jis_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If no exact match and starts with JIS, try case-insensitive search
    if ($result->num_rows === 0 && stripos($jis_id, 'jis') === 0) {
        $stmt->close();
        $like_pattern = $conn->real_escape_string($jis_id);
        $stmt = $conn->prepare("SELECT * FROM registrations WHERE jis_id LIKE ? LIMIT 1");
        $like_pattern = "%" . substr($like_pattern, 4) . "%"; // Remove JIS part for flexible matching
        $stmt->bind_param("s", $like_pattern);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    // Check if found in student registrations
    if ($row = $result->fetch_assoc()) {
        $found = true;
        displayStudentInfo($row);
    } else {
        // If not found in students, check alumni_registrations
        $stmt->close();
        
        // Try exact match in alumni table
        $stmt = $conn->prepare("SELECT * FROM alumni_registrations WHERE jis_id = ?");
        if (!$stmt) {
            echo "<div class='error-message'>❌ Prepare failed: " . $conn->error . "</div>";
            exit;
        }

        $stmt->bind_param("s", $jis_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // If no exact match and starts with JIS, try case-insensitive search
        if ($result->num_rows === 0 && stripos($jis_id, 'jis') === 0) {
            $stmt->close();
            $like_pattern = $conn->real_escape_string($jis_id);
            $stmt = $conn->prepare("SELECT * FROM alumni_registrations WHERE jis_id LIKE ? LIMIT 1");
            $like_pattern = "%" . substr($like_pattern, 4) . "%"; // Remove JIS part for flexible matching
            $stmt->bind_param("s", $like_pattern);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        // Check if found in alumni registrations
        if ($row = $result->fetch_assoc()) {
            $found = true;
            $isAlumni = true;
            displayAlumniInfo($row);
        }
    }
    
    // If not found in either table
    if (!$found) {
        echo "<div class='error-message'>No record found for JIS ID: " . htmlspecialchars($jis_id) . "</div>";
    }
} else {
    echo "<div class='error-message'>No JIS ID provided in the request.</div>";
}

/**
 * Display student information
 * @param array $row Student data from registrations table
 */
function displayStudentInfo($row) {
    echo "<div class='participant-details student-record'>";
    // Move participant type to its own line before other details
    echo "<div class='participant-type-container'>";
    echo "<div class='participant-type'>Student</div>";
    echo "</div>";
    
    echo "<strong>Name:</strong> " . htmlspecialchars($row['student_name']) . "<br>";
    echo "<strong>Email:</strong> " . htmlspecialchars($row['email']) . "<br>";
    echo "<strong>Department:</strong> " . htmlspecialchars($row['department']) . "<br>";
    echo "<strong>Payment Status:</strong> " . htmlspecialchars($row['payment_status']) . "<br>";
    echo "<strong>Day 1 Check-In:</strong> " . ($row['checkin_1'] === 'Yes' ? '<span class="success-message">Checked In</span>' : 'Not Checked In') . "<br>";
    echo "<strong>Day 2 Check-In:</strong> " . ($row['checkin_2'] === 'Yes' ? '<span class="success-message">Checked In</span>' : 'Not Checked In') . "<br>";
    echo "</div>";

    if ($row['checkin_2'] !== "Yes") {
        echo "<div class='button-container'>";
        echo "<button class='checkin-button' data-jisid='" . htmlspecialchars($row['jis_id']) . "' data-type='student' data-day='2'>Check In</button>";
        echo "</div>";
    } else {
        // Add 5:30 hours to the timestamp for display
        $timestamp = isset($row['checkin_2_timestamp']) ? strtotime($row['checkin_2_timestamp']) : 0;
        if ($timestamp) {
            $adjusted_timestamp = date('Y-m-d H:i:s', $timestamp + (5 * 60 * 60) + (30 * 60)); // Add 5 hours 30 minutes
            echo "<div class='status-info success-checkin'>Day 2 Already Checked In at: " . htmlspecialchars($adjusted_timestamp) . " (IST)</div>";
        } else {
            echo "<div class='status-info success-checkin'>Day 2 Already Checked In</div>";
        }
    }
}

/**
 * Display alumni information
 * @param array $row Alumni data from alumni_registrations table
 */
function displayAlumniInfo($row) {
    echo "<div class='participant-details alumni-record'>";
    // Move participant type to its own line before other details
    echo "<div class='participant-type-container'>";
    echo "<div class='participant-type'>Alumni</div>";
    echo "</div>";
    
    echo "<strong>Name:</strong> " . htmlspecialchars($row['alumni_name']) . "<br>";
    echo "<strong>Email:</strong> " . htmlspecialchars($row['email']) . "<br>";
    echo "<strong>Department:</strong> " . htmlspecialchars($row['department']) . "<br>";
    echo "<strong>Passout Year:</strong> " . htmlspecialchars($row['passout_year']) . "<br>";
    echo "<strong>Organization:</strong> " . (isset($row['current_organization']) && !empty($row['current_organization']) ? htmlspecialchars($row['current_organization']) : 'Not specified') . "<br>";
    echo "<strong>Payment Status:</strong> " . htmlspecialchars($row['payment_status']) . "<br>";
    echo "<strong>Day 1 Check-In:</strong> " . ($row['checkin_1'] === 'Yes' ? '<span class="success-message">Checked In</span>' : 'Not Checked In') . "<br>";
    echo "<strong>Day 2 Check-In:</strong> " . ($row['checkin_2'] === 'Yes' ? '<span class="success-message">Checked In</span>' : 'Not Checked In') . "<br>";
    echo "</div>";

    if ($row['checkin_2'] !== "Yes") {
        echo "<div class='button-container'>";
        echo "<button class='checkin-button' data-jisid='" . htmlspecialchars($row['jis_id']) . "' data-type='alumni' data-day='2'>Check In</button>";
        echo "</div>";
    } else {
        // Add 5:30 hours to the timestamp for display
        $timestamp = isset($row['checkin_2_timestamp']) ? strtotime($row['checkin_2_timestamp']) : 0;
        if ($timestamp) {
            $adjusted_timestamp = date('Y-m-d H:i:s', $timestamp + (5 * 60 * 60) + (30 * 60)); // Add 5 hours 30 minutes
            echo "<div class='status-info success-checkin'>Day 2 Already Checked In at: " . htmlspecialchars($adjusted_timestamp) . " (IST)</div>";
        } else {
            echo "<div class='status-info success-checkin'>Day 2 Already Checked In</div>";
        }
    }
}
?>
<script>
// Remove the existing event listener and use a more direct approach
// to avoid duplicate alerts
var buttons = document.querySelectorAll('.checkin-button');
buttons.forEach(function(button) {
    // Clear any existing event listeners
    button.replaceWith(button.cloneNode(true));
});

// Add new event listeners
document.querySelectorAll('.checkin-button').forEach(function(button) {
    button.addEventListener('click', function() {
        var jis_id = this.getAttribute('data-jisid');
        var type = this.getAttribute('data-type');
        var day = this.getAttribute('data-day');
        window.parent.checkinUser(jis_id, type, day);
    });
});
</script>
