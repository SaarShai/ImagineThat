<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ImagineThat - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-bg">
    <div class="centered-card fade-in">
        <h1 class="logo-title">ImagineThat</h1>
        <form action="login.php" method="post" class="form">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Log In</button>
        </form>
        <div class="switch-link">
            <span>Don't have an account?</span> <a href="signup.php">Sign Up</a>
        </div>
    </div>
</body>
</html>
