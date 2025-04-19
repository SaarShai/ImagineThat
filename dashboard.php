<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
// Example: Fetch user game stats (expand as needed)
$stats = [];
$stats_stmt = $pdo->prepare('SELECT * FROM user_game_stats WHERE user_id = ?');
$stats_stmt->execute([$user_id]);
while ($row = $stats_stmt->fetch()) {
    $stats[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ImagineThat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <div class="dashboard-container fade-in">
        <nav class="top-nav">
            <span class="logo-title">ImagineThat</span>
            <div class="nav-user">
                <span class="user-name">👤 <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="logout.php" class="logout-link">Log Out</a>
            </div>
        </nav>
        <div class="tabs-menu">
            <button class="tab-btn active" data-tab="account">Account</button>
            <button class="tab-btn" data-tab="games">Games</button>
        </div>
        <div class="tab-content" id="account" style="display:block;">
            <h2>Account Information</h2>
            <div class="account-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Member since:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
            </div>
            <h3>Game Stats</h3>
            <div class="game-stats">
                <?php if ($stats): ?>
                    <ul>
                        <?php foreach ($stats as $stat): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($stat['game']); ?>:</strong>
                                <?php echo htmlspecialchars($stat['score']); ?> points
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No game stats yet.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="tab-content" id="games">
            <h2>Games</h2>
            <div class="games-menu">
                <a href="https://game1.imaginethat.com" class="game-link">Game 1</a>
                <a href="https://game2.imaginethat.com" class="game-link">Game 2</a>
                <!-- Add more games as needed -->
            </div>
        </div>
    </div>
</body>
</html>
