<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correct the include path
require_once __DIR__ . '/../includes/db_config.php';

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'website manager') {
        header('Location: management.php');
    } else {
        header('Location: adm/madm.php');
    }
    exit();
}

$error = '';
$success = '';
$departments = ['CSE', 'BME', 'ECE', 'EE', 'CE', 'ME', 'MCA', 'MBA', 'BBA'];
$roles = ['Admin', 'Core Team Member', 'Coordinator'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];
    $department = isset($_POST['department']) ? $_POST['department'] : null;

    // Validate form data
    if (empty($name) || empty($mobile) || empty($email) || empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $error = "Mobile number must be 10 digits";
    } elseif ($role === 'Coordinator' && empty($department)) {
        $error = "Department is required for Coordinator role";
    } else {
        // Check if username already exists
        $existing_user = $db->admin_users->findOne(['username' => $username]);
        
        if ($existing_user) {
            $error = "Username already exists. Please choose another.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Create user document
            $user_data = [
                'name' => $name,
                'mobile' => $mobile,
                'email' => $email,
                'username' => $username,
                'password' => $hashed_password,
                'role' => $role,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
            ];
            
            // Add department if coordinator
            if ($role === 'Coordinator') {
                $user_data['department'] = $department;
            }
            
            // Insert user into database
            try {
                $result = $db->admin_users->insertOne($user_data);
                if ($result->getInsertedCount()) {
                    $success = "Registration successful! You can now <a href='login.php'>login</a>.";
                    // Clear form data on success
                    $name = $mobile = $email = $username = '';
                } else {
                    $error = "Registration failed. Please try again.";
                }
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaJIStic 2K25 - Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light: #ecf0f1;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 20px;
        }
        
        .register-container {
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            padding: 0;
        }
        
        .register-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            text-align: center;
            color: var(--white);
        }
        
        .register-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .register-header p {
            opacity: 0.8;
        }
        
        .register-logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .register-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .form-group .icon {
            position: absolute;
            right: 15px;
            top: 40px;
            color: var(--primary);
        }
        
        .form-group select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6"><path d="M0 0l6 6 6-6z" fill="%232c3e50"/></svg>') no-repeat;
            background-position: right 15px center;
            background-color: white;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .department-section {
            display: none;
            margin-top: 5px;
        }
        
        .btn-register {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            background-color: #ffebee;
            color: var(--accent);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error-message i {
            font-size: 18px;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message i {
            font-size: 18px;
        }
        
        .register-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
            padding-bottom: 10px;
        }
        
        .login-link {
            margin-top: 20px;
            text-align: center;
        }
        
        .login-link a {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .register-container {
                max-width: 100%;
            }
            
            .register-header {
                padding: 20px;
            }
            
            .register-form {
                padding: 20px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="register-logo">
            <h1>MaJIStic 2K25</h1>
            <p>Create New Account</p>
        </div>
        
        <div class="register-form">
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    <i class="fas fa-user icon"></i>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="text" id="mobile" name="mobile" required pattern="[0-9]{10}" title="10 digit mobile number" value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>">
                        <i class="fas fa-phone icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        <i class="fas fa-envelope icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    <i class="fas fa-user-tag icon"></i>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <i class="fas fa-lock icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        <i class="fas fa-lock icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select your role</option>
                        <?php foreach ($roles as $role_option): ?>
                            <option value="<?php echo htmlspecialchars($role_option); ?>" <?php echo isset($role) && $role === $role_option ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role_option); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-user-shield icon"></i>
                </div>
                
                <div id="department-section" class="form-group department-section">
                    <label for="department">Department</label>
                    <select id="department" name="department">
                        <option value="" disabled selected>Select your department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo isset($department) && $department === $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-building icon"></i>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Register
                </button>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
            
            <div class="register-footer">
                &copy; <?php echo date('Y'); ?> MaJIStic 2K25 - All Rights Reserved
            </div>
        </div>
    </div>
    
    <script>
        // Show/hide department section based on role selection
        const roleSelect = document.getElementById('role');
        const departmentSection = document.getElementById('department-section');
        
        roleSelect.addEventListener('change', function() {
            if (this.value === 'Coordinator') {
                departmentSection.style.display = 'block';
                document.getElementById('department').setAttribute('required', 'required');
            } else {
                departmentSection.style.display = 'none';
                document.getElementById('department').removeAttribute('required');
            }
        });
        
        // If form is resubmitted and role was Coordinator, show department section
        <?php if (isset($role) && $role === 'Coordinator'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            departmentSection.style.display = 'block';
            document.getElementById('department').setAttribute('required', 'required');
        });
        <?php endif; ?>
        
        // Add focus effects for form elements
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.icon').style.color = '#3498db';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.icon').style.color = '#2c3e50';
            });
        });

        // Password confirmation validation
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    </script>
</body>
</html>
