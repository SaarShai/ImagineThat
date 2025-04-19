<?php
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'Username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);
            header('Location: login.php?signup=1');
            exit();
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
    <link rel="stylesheet" href="assets/css/style.v2.css">
</head>
<body class="login-bg">
    <div class="centered-card fade-in">
        <h1 class="logo-title">Sign Up</h1>
        <?php if ($errors): ?>
            <div class="error-msg"><?php echo implode('<br>', $errors); ?></div>
        <?php endif; ?>
        <form action="signup.php" method="post" class="form">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit" class="btn">Sign Up</button>
        </form>
        <div class="switch-link">
            <span>Already have an account?</span> <a href="index.php">Log In</a>
        </div>
    </div>
</body>
</html>
