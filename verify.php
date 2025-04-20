<?php
require_once 'config.php';

$error = null;
$success = false;

// Check if verification code is present
if (isset($_GET['code']) && !empty($_GET['code'])) {
    $code = $_GET['code'];
    
    // Find user with matching verification code
    $stmt = $pdo->prepare('SELECT * FROM users WHERE verification_code = ?');
    $stmt->execute([$code]);
    $user = $stmt->fetch();
    
    if ($user) {
        // If user is already verified
        if ($user['is_verified']) {
            $message = 'Your account is already verified. You can log in.';
            $success = true;
        } else {
            // Verify the user
            $updateStmt = $pdo->prepare('UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?');
            
            if ($updateStmt->execute([$user['id']])) {
                $message = 'Your account has been successfully verified! You can now log in.';
                $success = true;
            } else {
                $error = 'Could not verify account. Please try again or contact support.';
            }
        }
    } else {
        $error = 'Invalid verification code or the code has expired.';
    }
} else {
    $error = 'No verification code provided.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - ImagineThat</title>
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
        .btn {
            padding: 0.95rem 2rem;
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
            display: inline-block;
            text-decoration: none;
        }
        .btn:hover, .btn:focus {
            background: linear-gradient(90deg, #818cf8 0%, #6366f1 100%);
            box-shadow: 0 6px 24px rgba(99,102,241,0.13);
            transform: translateY(-2px) scale(1.025);
            outline: none;
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
        .success-msg {
            color: #166534;
            background: #dcfce7dd;
            border-radius: 8px;
            padding: 0.9em 1.2em;
            margin-bottom: 1em;
            font-size: 1.01rem;
            border: 1.2px solid #bbf7d0;
            box-shadow: 0 1px 8px #dcfce755;
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
            text-align: left;
        }
        .success-msg h3 {
            color: #15803d;
            margin-top: 0;
            margin-bottom: 0.5em;
            font-weight: 600;
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
        <h1 class="logo-title">Account Verification</h1>
        
        <?php if ($success): ?>
            <div class="success-msg">
                <h3>Success!</h3>
                <p><?php echo $message; ?></p>
            </div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <a href="index.php" class="btn">Go to Login</a>
    </div>
</body>
</html>
