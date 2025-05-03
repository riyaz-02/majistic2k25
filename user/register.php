<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correct the include path
require_once __DIR__ . '/../includes/db_config.php';

// If already logged in, redirect
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['admin_role'])) {
        if ($_SESSION['admin_role'] === 'Super Admin') {
            // Super Admin can access any page, default to admin dashboard
            header('Location: adm/madm.php');
        } elseif ($_SESSION['admin_role'] === 'website manager') {
            header('Location: management.php');
        } elseif ($_SESSION['admin_role'] === 'Manage Website') {
            header('Location: manage/index.php');
        } elseif ($_SESSION['admin_role'] === 'Controller') {
            header('Location: control/index.php');
        } else {
            header('Location: adm/madm.php');
        }
    } else {
        header('Location: adm/madm.php');
    }
    exit();
}

$error = '';
$success = '';
$departments = ['CSE', 'CSE AI-ML', 'CST', 'IT', 'ECE', 'EE', 'BME', 'CE', 'ME', 'AGE', 'BBA', 'MBA', 'BCA', 'MCA', 'Diploma ME', 'Diploma CE', 'Diploma EE', 'B. Pharmacy'];
$roles = ['Super Admin', 'Admin', 'Core Team Member', 'Department Coordinator', 'Convenor', 'Manage Website', 'Controller', 'CheckIn'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']); // Email now optional
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];
    $department = isset($_POST['department']) ? $_POST['department'] : null;

    // Validate form data
    if (empty($name) || empty($mobile) || empty($username) || empty($password) || empty($role)) {
        $error = "Required fields must be filled";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $error = "Mobile number must be 10 digits";
    } elseif (($role === 'Department Coordinator' || $role === 'Convenor') && empty($department)) {
        $error = "Department is required for Coordinator/Convenor role";
    } else {
        try {
            // Check if username already exists
            $query = "SELECT COUNT(*) FROM admin_users WHERE username = :username";
            $stmt = $db->prepare($query);
            $stmt->execute([':username' => $username]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $error = "Username already exists. Please choose another.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Create user data
                $user_data = [
                    'name' => $name,
                    'mobile' => $mobile,
                    'username' => $username,
                    'password' => $hashed_password,
                    'role' => $role,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Add email if provided
                if (!empty($email)) {
                    $user_data['email'] = $email;
                }
                
                // Add department if coordinator or convenor
                if ($role === 'Department Coordinator' || $role === 'Convenor') {
                    $user_data['department'] = $department;
                }
                
                // Insert user into database
                $fields = implode(', ', array_keys($user_data));
                $placeholders = ':' . implode(', :', array_keys($user_data));
                
                $query = "INSERT INTO admin_users ($fields) VALUES ($placeholders)";
                $stmt = $db->prepare($query);
                
                $stmt->execute($user_data);
                
                if ($stmt->rowCount() > 0) {
                    $success = "Registration successful! You can now login with your credentials.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $error = "Registration failed due to a system error. Please try again later.";
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
            <p>Admin Registration</p>
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
                    <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <i class="fas fa-user icon"></i>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile">Mobile Number</label>
                        <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" required value="<?php echo isset($_POST['mobile']) ? htmlspecialchars($_POST['mobile']) : ''; ?>">
                        <i class="fas fa-phone icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address (Optional)</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <i class="fas fa-envelope icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fas fa-user-tag icon"></i>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <i class="fas fa-lock icon"></i>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <i class="fas fa-lock icon"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="" selected disabled>Select Role</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo htmlspecialchars($r); ?>" <?php echo isset($_POST['role']) && $_POST['role'] === $r ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($r); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-user-shield icon"></i>
                </div>
                
                <div class="form-group department-section" id="department-section" style="display: <?php echo isset($_POST['role']) && ($_POST['role'] === 'Department Coordinator' || $_POST['role'] === 'Convenor') ? 'block' : 'none'; ?>;">
                    <label for="department">Department</label>
                    <select id="department" name="department" <?php echo isset($_POST['role']) && ($_POST['role'] === 'Department Coordinator' || $_POST['role'] === 'Convenor') ? 'required' : ''; ?>>
                        <option value="" selected disabled>Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept); ?>" <?php echo isset($_POST['department']) && $_POST['department'] === $dept ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-building icon"></i>
                </div>
                
                <button type="submit" class="btn-register">Register</button>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
        
        <div class="register-footer">
            &copy; <?php echo date('Y'); ?> MaJIStic 2K25 - All Rights Reserved
        </div>
    </div>
    
    <script>
        // Show/hide department section based on role selection
        const roleSelect = document.getElementById('role');
        const departmentSection = document.getElementById('department-section');
        
        roleSelect.addEventListener('change', function() {
            if (this.value === 'Department Coordinator' || this.value === 'Convenor') {
                departmentSection.style.display = 'block';
                document.getElementById('department').setAttribute('required', 'required');
            } else {
                departmentSection.style.display = 'none';
                document.getElementById('department').removeAttribute('required');
            }
        });
        
        // If form is resubmitted and role was Coordinator or Convenor, show department section
        <?php if (isset($role) && ($role === 'Department Coordinator' || $role === 'Convenor')): ?>
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
