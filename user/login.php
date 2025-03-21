<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Correct the include path
require_once __DIR__ . '/../includes/db_config.php';

// If already logged in, redirect to admin panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (isset($_SESSION['admin_role'])) {
        if ($_SESSION['admin_role'] === 'website manager') {
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

$login_attempt = false;
$error = '';
$logout_message = '';

// Check for logout message
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $logout_message = "You have been successfully logged out.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_attempt = true;
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Find user in MongoDB
    $user = $db->admin_users->findOne([
        'username' => $username,
        'role' => $role
    ]);

    if ($user) {
        // Check password
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = (string)$user['_id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'website manager') {
                header('Location: management.php');
            } elseif ($user['role'] === 'Manage Website') {
                header('Location: manage/index.php');
            } elseif ($user['role'] === 'Controller') {
                header('Location: control/index.php');
            } else {
                header('Location: adm/madm.php');
            }
            exit;
        }
    }
    
    $error = "Invalid username, password or role";
}

// Get available roles from MongoDB
$roles = $db->admin_users->distinct('role');
if (empty($roles)) {
    $roles = ['admin', 'website manager', 'verifier', 'HOD', 'faculty coordinator', 'student', 'Manage Website'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaJIStic 2K25 Admin Login</title>
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
            padding: 0;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 30px 20px;
            text-align: center;
            color: var(--white);
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.8;
        }
        
        .login-logo {
            width: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .login-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-group input:focus {
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
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="6"><path d="M0 0l6 6 6-6z" fill="%232c3e50"/></svg>') no-repeat;
            background-position: right 15px center;
            background-color: white;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: var(--secondary);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn-login {
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
        
        .btn-login:hover {
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
        
        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #777;
            padding-bottom: 10px;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .shake {
            animation: shake 0.6s;
        }
        
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-form {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container <?php echo $login_attempt && $error ? 'shake' : ''; ?>">
        <div class="login-header">
            <img src="../images/majisticlogo.png" alt="MaJIStic Logo" class="login-logo">
            <h1>MaJIStic 2K25</h1>
            <p>Admin Panel Login</p>
        </div>
        
        <div class="login-form">
            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($logout_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $logout_message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="fas fa-user icon"></i>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <i class="fas fa-lock icon"></i>
                </div>
                
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select your role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role); ?>" <?php echo isset($_POST['role']) && $_POST['role'] === $role ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars(ucfirst($role)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-user-tag icon"></i>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                &copy; <?php echo date('Y'); ?> MaJIStic 2K25 - All Rights Reserved
            </div>
        </div>
    </div>
    
    <script>
        // Add focus effects
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.icon').style.color = '#3498db';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('.icon').style.color = '#2c3e50';
            });
        });
    </script>
</body>
</html>
