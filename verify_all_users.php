<?php
// Script to mark all existing users as verified
// Delete this file after use for security

require_once 'config.php';

try {
    // Update all unverified users to be verified
    $stmt = $pdo->prepare('UPDATE users SET is_verified = 1 WHERE is_verified = 0');
    $result = $stmt->execute();
    
    $count = $stmt->rowCount();
    
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background-color: #f9f9f9;'>";
    echo "<h2 style='color: #4CAF50;'>Success!</h2>";
    echo "<p>Successfully verified $count user account(s).</p>";
    echo "<p><strong>Important:</strong> For security reasons, please delete this file (verify_all_users.php) after use.</p>";
    echo "<p><a href='index.php' style='display: inline-block; background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Go to Login</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background-color: #fff0f0;'>";
    echo "<h2 style='color: #e74c3c;'>Database Error</h2>";
    echo "<p>There was a problem updating user accounts:</p>";
    echo "<pre style='background-color: #f8f8f8; padding: 10px; border-radius: 4px; overflow: auto;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "</div>";
}
?>
