<?php
require_once 'config.php';
session_start();

$signup_success = isset($_GET['signup']) && $_GET['signup'] == '1';
$verified = isset($_GET['verified']) && $_GET['verified'] == '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check whether the form has 'login' or 'username' field - depends on which page users are coming from
    $login = isset($_POST['login']) ? trim($_POST['login']) : trim($_POST['username']);
    $password = $_POST['password'];
    
    // Check if login is email or username
    $is_email = filter_var($login, FILTER_VALIDATE_EMAIL);
    
    if ($is_email) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    }
    
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Check if user is verified
        if (!$user['is_verified']) {
            $error = 'Please verify your email address before logging in. Check your inbox for the verification email.';
        } else {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $error = 'Invalid login credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ImagineThat</title>
    <style>
        /* Minimalist, elegant UI */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', 'Segoe UI', Arial, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', sans-serif;
            background: linear-gradient(120deg, #e0e7ff 0%, #f6f8fa 100%);
            color: #222;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .login-bg {
            background: linear-gradient(120deg, #e0e7ff 0%, #f6f8fa 100%);
            min-height: 100vh;
        }
        .centered-card {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(8px) saturate(140%);
            border-radius: 22px;
            box-shadow: 0 8px 40px rgba(99,102,241,0.10), 0 2px 8px rgba(60,60,100,0.07);
            max-width: 380px;
            margin: 80px auto 0 auto;
            padding: 2.7rem 2.2rem 2.2rem 2.2rem;
            text-align: center;
            transition: box-shadow 0.22s cubic-bezier(.4,0,.2,1), background 0.22s;
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
        }
        .logo-title {
            font-size: 2.3rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            margin-bottom: 1.2rem;
            color: #4f46e5;
            text-shadow: 0 2px 18px #6366f133;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }
        .form input {
            width: 100%;
            padding: 0.85rem 1.1rem;
            margin: 0.7rem 0;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1.06rem;
            background: rgba(248,250,252,0.93);
            transition: border 0.22s, box-shadow 0.22s;
            box-shadow: 0 1.5px 8px rgba(99,102,241,0.04);
        }
        .form input:focus {
            border: 1.5px solid #6366f1;
            outline: none;
            box-shadow: 0 0 0 2px #6366f133;
            background: #fff;
        }
        .btn {
            width: 100%;
            padding: 0.95rem 0;
            background: linear-gradient(90deg, #6366f1 0%, #818cf8 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 1.09rem;
            font-weight: 700;
            cursor: pointer;
            margin-top: 1.15rem;
            box-shadow: 0 2px 14px rgba(99,102,241,0.11);
            letter-spacing: 0.01em;
            transition: background 0.22s, box-shadow 0.22s, transform 0.13s;
            will-change: transform;
        }
        .btn:hover, .btn:focus {
            background: linear-gradient(90deg, #818cf8 0%, #6366f1 100%);
            box-shadow: 0 6px 24px rgba(99,102,241,0.13);
            transform: translateY(-2px) scale(1.025);
            outline: none;
        }
        .switch-link {
            margin-top: 1.2rem;
            color: #999;
            font-size: 0.96rem;
        }
        .switch-link a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .switch-link a:hover {
            color: #4f46e5;
        }
        .error-msg {
            color: #b91c1c;
            background: #fee2e2cc;
            border-radius: 8px;
            padding: 0.8em 1.2em;
            margin-bottom: 1em;
            font-size: 1.01rem;
            border: 1.2px solid #fecaca;
            box-shadow: 0 1px 8px #fee2e255;
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
        }
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .fade-in {
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
        }
        @media (max-width: 480px) {
            .centered-card {
                max-width: 98vw;
                padding: 1.2rem 0.4rem;
                margin: 32px auto 0 auto;
            }
            .logo-title {
                font-size: 1.45rem;
            }
        }
    </style>
</head>
<body class="login-bg">
    <div class="centered-card fade-in">
        <h1 class="logo-title">Log In</h1>
        
        <?php if ($verified): ?>
            <div class="success-msg">
                <h3>Email Verified!</h3>
                <p>Your account has been successfully verified. You can now log in with your credentials.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($signup_success): ?>
            <div class="success-msg">
                <h3>Registration Successful!</h3>
                <p>Your account has been created. You may now log in with your credentials.</p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="post" class="form">
            <input type="text" name="login" placeholder="Username or Email" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Log In</button>
        </form>
        
        <div class="switch-link">
            <span>Don't have an account?</span> <a href="signup.php">Sign Up</a>
        </div>
    </div>
</body>
</html>
