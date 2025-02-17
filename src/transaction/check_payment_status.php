<?php
include '../../includes/db_config.php';

$jis_id = $_POST['jis_id'];
$roll_no = $_POST['roll_no'];

$query = $conn->prepare("SELECT * FROM registrations WHERE jis_id = ? AND roll_no = ?");
$query->bind_param("ss", $jis_id, $roll_no);
$query->execute();
$result = $query->get_result();
$registration = $result->fetch_assoc();

if (!$registration) {
    echo json_encode(['status' => 'error', 'message' => 'Registration not found.']);
    exit;
}

if ($registration['payment_status'] == 'Not Paid') {
    echo json_encode(['status' => 'pending']);
} else {
    echo json_encode(['status' => 'completed', 'message' => 'Payment already completed.']);
}
?>
