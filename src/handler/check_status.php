<?php
include '../../includes/db_config.php';

$message = '';
$student_data = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $jis_id = isset($_POST['jis_id']) ? $_POST['jis_id'] : '';
    
    if (!empty($jis_id)) {
        $stmt = $conn->prepare("SELECT student_name, department, email, mobile, registration_date, inhouse_competition, competition_name FROM registrations WHERE jis_id = ?");
        $stmt->bind_param("s", $jis_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $student_data = $result->fetch_assoc();
        } else {
            $message = 'No registration found for the provided JIS ID.';
        }
        
        $stmt->close();
    } else {
        $message = 'Please enter your JIS ID.';
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Registration Status - maJIStic 2k25</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <?php include '../../includes/links.php'; ?>
    <link rel="stylesheet" href="../../style.css">
    <style>
        body {
            background-image: url('../../images/pageback.png');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
            font-family: 'Roboto', sans-serif;
            color: #ffffff;
        }
        
        .status-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .status-card {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 30px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #3498db, #8e44ad);
            padding: 20px;
            text-align: center;
            position: relative;
        }
        
        .logo {
            width: 120px;
            margin-bottom: 10px;
        }
        
        .card-body {
            padding: 30px;
            text-align: center;
        }
        
        .form-container {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #bdc3c7;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.5);
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
        
        .student-details {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .student-details h3 {
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #3498db;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .detail-label {
            font-weight: 500;
            color: #95a5a6;
        }
        
        .detail-value {
            font-weight: 400;
            color: #ecf0f1;
        }
        
        .message-box {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-weight: 500;
        }
        
        .message-box.error {
            background-color: rgba(231, 76, 60, 0.2);
            border-left: 4px solid #e74c3c;
            color: #e74c3c;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                margin-bottom: 15px;
            }
            
            .detail-value {
                margin-top: 5px;
                word-break: break-all;
            }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    
    <div class="status-container">
        <div class="status-card">
            <div class="card-header">
                <img src="https://i.ibb.co/RGQ7Lj6K/majisticlogo.png" alt="maJIStic Logo" class="logo">
                <h2>Check Registration Status</h2>
            </div>
            
            <div class="card-body">
                <?php if (!empty($message)): ?>
                    <div class="message-box error"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <div class="form-container">
                    <form method="post">
                        <div class="form-group">
                            <label for="jis_id">JIS ID</label>
                            <input type="text" id="jis_id" name="jis_id" placeholder="JIS/20XX/0000" required>
                        </div>
                        <div style="text-align: center;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Check Status
                            </button>
                        </div>
                    </form>
                </div>
                
                <?php if ($student_data): ?>
                    <div class="student-details">
                        <h3>Your Registration Details</h3>
                        <div class="detail-row">
                            <span class="detail-label">Name:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_data['student_name']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">JIS ID:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($jis_id); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Department:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_data['department']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_data['email']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Mobile:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_data['mobile']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Registration Date:</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student_data['registration_date']); ?></span>
                        </div>
                        <?php if (!empty($student_data['inhouse_competition']) && $student_data['inhouse_competition'] === 'Yes'): ?>
                            <div class="detail-row">
                                <span class="detail-label">Inhouse Competition:</span>
                                <span class="detail-value">Yes</span>
                            </div>
                            <?php if (!empty($student_data['competition_name'])): ?>
                                <div class="detail-row">
                                    <span class="detail-label">Competition:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($student_data['competition_name']); ?></span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="note" style="background-color: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; padding: 15px; margin: 20px 0; text-align: left; border-radius: 4px;">
                        <p><strong>Important Note:</strong> You'll be notified soon about the payment details for your event ticket. Keep an eye on your email inbox and our social media channels for updates!</p>
                    </div>

                    <div class="action-buttons">
                        <a href="../../index.php" class="btn btn-primary">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                        <a href="../../merchandise.php" class="btn btn-accent" style="background: linear-gradient(135deg, #2ecc71, #27ae60); color: white;">
                            <i class="fas fa-tshirt"></i> Buy Merchandise
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../../includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation to form
            const formContainer = document.querySelector('.form-container');
            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                formContainer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateY(0)';
            }, 100);
            
            // Format JIS ID input
            const jisIdInput = document.getElementById('jis_id');
            if (jisIdInput) {
                jisIdInput.addEventListener('input', function(e) {
                    let value = e.target.value.toUpperCase();
                    
                    // Auto format with slashes
                    if (value.length > 0 && value.indexOf('JIS/') !== 0 && !value.startsWith('JIS')) {
                        value = 'JIS/' + value;
                    } else if (value.length > 0 && value === 'JIS') {
                        value = 'JIS/';
                    }
                    
                    // Add second slash automatically
                    if (value.length === 8 && value.charAt(7) !== '/') {
                        value = value + '/';
                    }
                    
                    e.target.value = value;
                });
            }
        });
    </script>
</body>
</html>
