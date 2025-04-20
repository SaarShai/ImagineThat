<?php
require_once 'config.php';

// Function to send verification email via PHPMailer and Amazon SES
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/vendor/autoload.php';

function sendVerificationEmail($email, $username, $verification_code) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $_ENV['SES_SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SES_SMTP_USERNAME'];
        $mail->Password = $_ENV['SES_SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SES_SMTP_PORT'];

        // Recipients
        $mail->setFrom($_ENV['SES_FROM_ADDRESS'], 'ImagineThat');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your ImagineThat Account';

        // Email body
        $verificationLink = "https://imaginethat.one/verify.php?code=$verification_code";
        $mail->Body = "<html><body>"
            . "<h2>Welcome to ImagineThat!</h2>"
            . "<p>Hello $username,</p>"
            . "<p>Thank you for creating an account. To complete your registration, please verify your email by clicking the link below:</p>"
            . "<p><a href='$verificationLink'>Verify My Account</a></p>"
            . "<p>If the button above doesn't work, copy and paste this URL into your browser:</p>"
            . "<p>$verificationLink</p>"
            . "<p>Thank you,<br>The ImagineThat Team</p>"
            . "</body></html>";
        $mail->AltBody = "Hello $username,\n\nThank you for creating an account. To complete your registration, please verify your email by visiting this link: $verificationLink\n\nThank you, The ImagineThat Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validate inputs
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        
        if ($stmt->fetch()) {
            // Run specific checks to give better error messages
            $usernameCheck = $pdo->prepare('SELECT id FROM users WHERE username = ?');
            $usernameCheck->execute([$username]);
            
            $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $emailCheck->execute([$email]);
            
            if ($usernameCheck->fetch()) {
                $errors[] = 'Username already taken.';
            }
            
            if ($emailCheck->fetch()) {
                $errors[] = 'Email address already registered.';
            }
        } else {
            // Generate verification code
            $verification_code = bin2hex(random_bytes(32));
            
            // Hash password for security
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                // Insert the new user (not verified yet)
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)');
                $stmt->execute([$username, $email, $hash, $verification_code]);
                
                // Send verification email
                if (sendVerificationEmail($email, $username, $verification_code)) {
                    $success = true;
                } else {
                    $errors[] = 'Could not send verification email. Please try again or contact support.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
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
    <title>Sign Up - ImagineThat</title>
    <style>
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
            max-width: 100%;
            box-sizing: border-box;
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
        }
        @media (max-width: 450px) {
            .centered-card {
                max-width: 97vw;
                padding: 1.5rem 0.5rem;
            }
        }
    </style>
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
        <h1 class="logo-title">Sign Up</h1>
        <?php if ($success): ?>
            <div class="success-msg">
                <h3>Registration Successful!</h3>
                <p>Your account has been created successfully and is ready to use! You can now log in with your credentials.</p>
                <div class="switch-link" style="margin-top: 15px;">
                    <a href="index.php">Back to Login</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($errors): ?>
                <div class="error-msg"><?php echo implode('<br>', $errors); ?></div>
            <?php endif; ?>
            <form action="signup.php" method="post" class="form">
                <input type="text" name="username" placeholder="Username" required autofocus value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                <input type="email" name="email" placeholder="Email Address" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit" class="btn">Sign Up</button>
            </form>
            <div class="switch-link">
                <span>Already have an account?</span> <a href="index.php">Log In</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
