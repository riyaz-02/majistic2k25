<?php

// Correct the include path
require_once __DIR__ . '/../includes/db_config.php';

if (!isset($conn) || $conn === null) {
    // More descriptive error message
    echo "<div class='error-message'>";
    echo "❌ Database connection not established.<br>";
    echo "Please check that the file exists at: " . __DIR__ . '/../includes/db_config.php' . "<br>";
    echo "And verify your database credentials are correct.";
    echo "</div>";
    exit;
}

if (isset($_GET['jis_id'])) {
    $jis_id = $_GET['jis_id'];

    $stmt = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ?");
    if (!$stmt) {
        echo "<div class='error-message'>❌ Prepare failed: " . $conn->error . "</div>";
        exit;
    }

    $stmt->bind_param("s", $jis_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<div class='participant-details'>";
        echo "<strong>Name:</strong> " . $row['student_name'] . "<br>";
        echo "<strong>Email:</strong> " . $row['email'] . "<br>";
        echo "<strong>Department:</strong> " . $row['department'] . "<br>";
        echo "<strong>Payment Status:</strong> " . $row['payment_status'] . "<br>";
        echo "<strong>Check-In Status:</strong> " . $row['checkin_1'] . "<br>";
        echo "</div>";

        if ($row['checkin_1'] !== "Yes") {
            echo "<button class='checkin-button' data-jisid='$jis_id'>Check In</button>";
        } else {
            echo "<div class='status-info'>Already Checked In at: " . $row['checkin_1_timestamp'] . "</div>";
        }
    } else {
        echo "<div class='error-message'>No record found for this ID!</div>";
    }
}
?>
<script>
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('checkin-button')) {
        var jis_id = event.target.getAttribute('data-jisid');
        checkinUser(jis_id);
    }
});

function checkinUser(jis_id) {
    fetch("checkin.php?jis_id=" + jis_id)
    .then(res => res.text())
    .then(data => {
        alert(data);
        location.reload();
    });
}
</script>
