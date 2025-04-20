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
    <style>
        /* Modern, elegant UI matching login screen */
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
        
        .dashboard-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0;
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
        }
        
        .top-nav {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(8px) saturate(140%);
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(99,102,241,0.10), 0 2px 8px rgba(60,60,100,0.07);
            padding: 1.2rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            transition: box-shadow 0.22s cubic-bezier(.4,0,.2,1), background 0.22s;
        }
        
        .logo-title {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            color: #4f46e5;
            text-shadow: 0 2px 18px #6366f133;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }
        
        .nav-user {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .user-name {
            font-weight: 500;
            font-size: 0.95rem;
            color: #4b5563;
        }
        
        .logout-link {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s, transform 0.2s;
            padding: 6px 14px;
            border-radius: 8px;
            background: rgba(244,244,255,0.7);
        }
        
        .logout-link:hover {
            color: #4f46e5;
            background: rgba(235,235,255,0.9);
            transform: translateY(-1px);
        }
        
        .tabs-menu {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.65);
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .tab-btn:hover {
            background: rgba(255, 255, 255, 0.85);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(99,102,241,0.08);
        }
        
        .tab-btn.active {
            background: linear-gradient(90deg, #6366f1 0%, #818cf8 100%);
            color: white;
            box-shadow: 0 4px 14px rgba(99,102,241,0.2);
        }
        
        .tab-content {
            background: rgba(255,255,255,0.82);
            backdrop-filter: blur(8px) saturate(140%);
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(99,102,241,0.10), 0 2px 8px rgba(60,60,100,0.07);
            padding: 30px;
            margin-bottom: 20px;
            display: none;
        }
        
        .tab-content h2 {
            color: #4f46e5;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 24px;
            border-bottom: 1px solid rgba(99,102,241,0.1);
            padding-bottom: 15px;
        }
        
        .tab-content h3 {
            color: #4338ca;
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        .account-info p {
            font-size: 1.05rem;
            line-height: 1.6;
            color: #4b5563;
            margin: 8px 0;
        }
        
        .account-info strong {
            color: #374151;
            font-weight: 600;
        }
        
        .game-stats ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .game-stats li {
            padding: 12px 16px;
            margin: 8px 0;
            background: rgba(248,250,252,0.75);
            border-radius: 10px;
            font-size: 1rem;
            border: 1px solid #e5e7eb;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .game-stats li:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .games-menu {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .game-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.7);
            border-radius: 16px;
            text-decoration: none;
            color: #4f46e5;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 20px rgba(99,102,241,0.15);
            transition: transform 0.22s, box-shadow 0.22s, background 0.22s;
            border: 1px solid rgba(99,102,241,0.1);
        }
        
        .game-link:hover {
            transform: translateY(-5px) scale(1.03);
            background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(240,245,255,0.9));
            box-shadow: 0 12px 28px rgba(99,102,241,0.18);
            color: #4338ca;
        }
        
        .game-link:before {
            content: '🎮';
            font-size: 2.5rem;
            margin-bottom: 12px;
        }
        
        @keyframes fadeInUp {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeInUp 0.7s cubic-bezier(.4,0,.2,1);
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                margin: 20px auto;
                padding: 0 16px;
            }
            
            .top-nav {
                padding: 16px;
            }
            
            .logo-title {
                font-size: 1.4rem;
            }
            
            .tab-content {
                padding: 20px;
            }
            
            .games-menu {
                justify-content: center;
            }
        }
    </style>
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
                <a href="https://memRPG.imaginethat.one" class="game-link">memRPG</a>
            </div>
        </div>
    </div>
    
    <script>
        // Simple tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(tab => {
                    tab.style.display = 'none';
                });
                
                // Remove active class from all buttons
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Show selected tab and mark button as active
                const tabName = button.getAttribute('data-tab');
                document.getElementById(tabName).style.display = 'block';
                button.classList.add('active');
            });
        });
        
        // Make sure the active tab is displayed on page load
        document.getElementById('account').style.display = 'block';
    </script>
</body>
</html>
