<?php
// login.php - Secure Login Screen for KS Electrical and AC Services
require_once 'session_start.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Set your secure username and hashed password
    $correct_username = "admin";
    $correct_password_hash = '$2y$10$T68zsQzLgKuF6Jpyk4m7He39bU3vN1WGq06SQtNwaFHf.WSwwVzaS'; // Hash for KSElectrical@2026
    
    if ($username === $correct_username && password_verify($password, $correct_password_hash)) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = "गलत यूज़रनेम या पासवर्ड! (Invalid Username or Password)";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | KS Electrical and AC Services</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            margin: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 35px 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            text-align: center;
            box-sizing: border-box;
        }
        .login-logo {
            height: 50px;
            width: auto;
            margin-bottom: 15px;
            object-fit: contain;
        }
        .login-title {
            font-size: 20px;
            font-weight: 700;
            color: #38bdf8;
            margin-bottom: 5px;
        }
        .login-subtitle {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }
        .form-control-dark {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: #fff;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .form-control-dark:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.2);
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            background: #0284c7;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover {
            background: #0369a1;
        }
        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-card">
    <?php if (file_exists('logo.png')) { ?>
        <img class="login-logo" src="logo.png" alt="Logo">
    <?php } ?>
    <div class="login-title">KS Electrical and AC Services</div>
    <div class="login-subtitle">Billing System Setup Portal</div>

    <?php if (!empty($error)) { ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username (यूज़रनेम)</label>
            <input type="text" class="form-control-dark" id="username" name="username" required placeholder="Enter username" autocomplete="username">
        </div>
        <div class="form-group">
            <label for="password">Password (पासवर्ड)</label>
            <input type="password" class="form-control-dark" id="password" name="password" required placeholder="Enter password" autocomplete="current-password">
        </div>
        <button type="submit" class="btn-login">Login (लॉग इन करें)</button>
    </form>
</div>

</body>
</html>
