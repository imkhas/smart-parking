<?php include 'includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="dashboard/assets/css/style.css">
    <link rel="stylesheet" href="dashboard/assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
       
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <h1><i class="fa-solid fa-shield-halved"></i> Admin Panel</h1>
        <p>Enter your credentials to continue</p>

        <?php if (isset($login_error)): ?>
            <div class="login-error"><?= htmlspecialchars($login_error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="login-btn">Sign In</button>
        </form>

        <a href="dashboard/index.php" class="back-link">← Back to Dashboard</a>
    </div>
</div>
</body>
</html>
