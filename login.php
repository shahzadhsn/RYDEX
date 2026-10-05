<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RYDEX | Admin Portal Login</title>

    <!-- Google Fonts - Cinzel & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Main Styles -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        body.login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(8, 8, 8, 0.96), rgba(15, 15, 15, 0.98)), url('images/hero-car.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            margin: 0;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(21, 21, 21, 0.92);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(200, 164, 93, 0.25);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85);
            padding: 45px 40px;
            border-radius: 4px;
            position: relative;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c8a45d, transparent);
        }

        .login-brand {
            font-family: 'Cinzel', serif;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #c8a45d;
            text-align: center;
            margin-bottom: 6px;
        }

        .login-subtitle {
            font-size: 11px;
            letter-spacing: 2.5px;
            color: #888888;
            text-align: center;
            margin-bottom: 35px;
            text-transform: uppercase;
        }

        .form-label {
            font-size: 11px;
            letter-spacing: 1.5px;
            color: #cccccc;
            text-transform: uppercase;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 24px;
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #c8a45d;
            font-size: 16px;
            z-index: 10;
        }

        .input-group-custom input {
            width: 100%;
            background: #0d0d0d;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
            font-size: 13px;
            padding: 14px 16px 14px 46px;
            border-radius: 3px;
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .input-group-custom input:focus {
            border-color: #c8a45d;
            box-shadow: 0 0 12px rgba(200, 164, 93, 0.15);
        }

        .login-btn {
            width: 100%;
            background: #c8a45d;
            color: #080808;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background: #dfc27d;
            box-shadow: 0 10px 20px rgba(200, 164, 93, 0.25);
            transform: translateY(-2px);
        }

        .login-footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #666666;
        }

        .login-footer-text a {
            color: #c8a45d;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-footer-text a:hover {
            color: #ffffff;
        }

        .alert-rydex {
            background: rgba(220, 53, 69, 0.15);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            font-size: 12px;
            padding: 12px;
            border-radius: 3px;
            margin-bottom: 20px;
            display: none;
        }
    </style>
</head>
<body class="login-page">

    <div class="login-card">
        <div class="login-brand">RYDEX</div>
        <div class="login-subtitle">Management Portal</div>

        <div id="loginAlert" class="alert-rydex" role="alert"></div>

        <form id="loginForm" onsubmit="handleLoginSubmit(event)">
            <div class="input-group-custom">
                <i class="bi bi-person"></i>
                <input type="text" id="username" name="username" placeholder="Username or Email" required autocomplete="username">
            </div>

            <div class="input-group-custom">
                <i class="bi bi-lock"></i>
                <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn" id="submitBtn">
                <span id="btnText">Log In</span>
                <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
            </button>
        </form>

        <div class="login-footer-text">
            <p class="mb-1">Default Login: <strong>admin</strong> / <strong>Password123!</strong></p>
            <a href="index.php"><i class="bi bi-arrow-left me-1"></i> Return to Main Website</a>
        </div>
    </div>

    <script>
    async function handleLoginSubmit(e) {
        e.preventDefault();
        const alertBox = document.getElementById('loginAlert');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        alertBox.style.display = 'none';
        submitBtn.disabled = true;
        btnText.textContent = 'Authenticating...';
        btnSpinner.classList.remove('d-none');

        const formData = {
            username: document.getElementById('username').value.trim(),
            password: document.getElementById('password').value.trim()
        };

        try {
            const res = await fetch('auth/login_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                btnText.textContent = 'Success! Redirecting...';
                window.location.href = data.redirect || 'dashboard.php';
            } else {
                alertBox.textContent = data.message || 'Invalid credentials.';
                alertBox.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Log In';
                btnSpinner.classList.add('d-none');
            }
        } catch (err) {
            alertBox.textContent = 'Connection error. Please try again.';
            alertBox.style.display = 'block';
            submitBtn.disabled = false;
            btnText.textContent = 'Log In';
            btnSpinner.classList.add('d-none');
        }
    }
    </script>
</body>
</html>
