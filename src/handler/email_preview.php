<?php
// Include the database configuration
require_once __DIR__ . '/../../includes/db_config.php';

// Include required mailers based on type
$type = isset($_GET['type']) ? $_GET['type'] : 'registration';

// Sample data for email previews
$sample_data = [];

if ($type === 'alumni') {
    // Include the alumni mailer
    require_once __DIR__ . '/../mail/alumni_mailer.php';
    
    $sample_data = [
        'alumni_name' => 'John Doe',
        'jis_id' => 'JIS/2020/0001',
        'department' => 'Computer Science',
        'passout_year' => '2020',
        'mobile' => '9876543210',
        'email' => 'alumni@example.com',
        'registration_date' => date('Y-m-d H:i:s')
    ];
    
    echo generateAlumniRegistrationTemplate($sample_data);
} else {
    // Include the regular registration mailer
    require_once __DIR__ . '/../mail/registration_mailer.php';
    
    $sample_data = [
        'student_name' => 'Jane Smith',
        'jis_id' => 'JIS/2023/0001',
        'department' => 'Computer Science',
        'mobile' => '9876543210',
        'email' => 'student@example.com',
        'registration_date' => date('Y-m-d H:i:s'),
        'competition' => 'Coding Competition'
    ];
    
    echo generateRegistrationEmailTemplate($sample_data);
}
?>
