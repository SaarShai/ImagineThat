# ImagineThat User Accounts System

## Description
A ready-to-upload PHP+MySQL user account system for imaginethat.com. Users can sign up, log in, and view a dashboard with account info and game stats. The UI is minimal, modern, and beautiful.

## Setup Instructions

### 1. Database Setup
- In your cPanel, open **phpMyAdmin**.
- Create a new database (e.g., `imaginethat`).
- Import `db.sql` to create the tables.
- Create a MySQL user and grant it access to the new database.

### 2. Configure Database Connection
- Open `config.php`.
- Set `$user` and `$pass` to your MySQL username and password.
- Set `$db` to your database name if different from `imaginethat`.

### 3. Upload Files
- Upload all files and folders to your cPanel hosting (typically `public_html/`).
- Ensure the folder structure is preserved.

### 4. Access Your Site
- Go to `https://imaginethat.com`.
- Sign up for a new account and log in.
- Use the dashboard tabs to view account info and games.

### 5. Customize
- Edit `dashboard.php` to add more games or stats fields as needed.
- Style the UI by editing `assets/css/style.css`.

## Security Notes
- Passwords are hashed using PHP's `password_hash` and `password_verify`.
- Sessions are used for authentication.
- For production, consider enabling HTTPS and further hardening security.

## Expanding
- You can expand the `user_game_stats` table to track more parameters.
- Add more pages or features as your platform grows.

---
Enjoy your new user account system!
