<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: user_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RYDEX | Customer Portal Login</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="css/style.css">

    <style>
        body.user-login-page {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(8, 8, 8, 0.95), rgba(15, 15, 15, 0.98)), url('images/hero-car.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            padding: 20px;
        }

        .user-auth-card {
            width: 100%;
            max-width: 440px;
            background: rgba(21, 21, 21, 0.94);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(200, 164, 93, 0.25);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85);
            padding: 45px 40px;
            border-radius: 4px;
            position: relative;
        }

        .user-auth-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, #c8a45d, transparent);
        }

        .auth-brand {
            font-family: 'Cinzel', serif;
            font-size: 34px;
            font-weight: 700;
            letter-spacing: 6px;
            color: #c8a45d;
            text-align: center;
            margin-bottom: 4px;
        }

        .auth-subtitle {
            font-size: 11px;
            letter-spacing: 2.5px;
            color: #888888;
            text-align: center;
            margin-bottom: 35px;
            text-transform: uppercase;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 22px;
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
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            font-size: 13px;
            padding: 14px 16px 14px 46px;
            border-radius: 3px;
            outline: none;
            transition: all 0.3s;
        }

        .input-group-custom input:focus {
            border-color: #c8a45d;
            box-shadow: 0 0 12px rgba(200, 164, 93, 0.2);
        }

        .auth-btn {
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
            transition: all 0.3s;
            margin-top: 10px;
        }

        .auth-btn:hover {
            background: #dfc27d;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(200, 164, 93, 0.25);
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
<body class="user-login-page">

    <div class="user-auth-card">
        <div class="auth-brand">RYDEX</div>
        <div class="auth-subtitle">Client Portal Login</div>

        <div id="userLoginAlert" class="alert-rydex"></div>

        <form id="userLoginForm" onsubmit="handleUserLogin(event)">
            <div class="input-group-custom">
                <i class="bi bi-envelope"></i>
                <input type="text" id="userEmail" placeholder="Email Address or Phone Number" required autocomplete="email">
            </div>

            <div class="input-group-custom">
                <i class="bi bi-lock"></i>
                <input type="password" id="userPassword" placeholder="Password" required autocomplete="current-password">
            </div>

            <button type="submit" class="auth-btn" id="userSubmitBtn">
                <span id="uBtnText">Log In to Account</span>
                <span id="uBtnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
            </button>
        </form>

        <div class="text-center mt-4 text-muted small">
            <p class="mb-2">Don't have a RYDEX client account?</p>
            <a href="user_register.php" class="text-gold text-decoration-none fw-semibold">Register New Account <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="text-center mt-3 pt-3 border-top border-secondary">
            <p class="text-muted small mb-1">Demo Client Credentials:</p>
            <code class="text-gold small">ahmed.khan@example.com</code> / <code class="text-gold small">Password123!</code>
            <div class="mt-3">
                <a href="index.php" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Return to Homepage</a>
            </div>
        </div>
    </div>

    <script>
    async function handleUserLogin(e) {
        e.preventDefault();
        const alertBox = document.getElementById('userLoginAlert');
        const submitBtn = document.getElementById('userSubmitBtn');
        const btnText = document.getElementById('uBtnText');
        const btnSpinner = document.getElementById('uBtnSpinner');

        alertBox.style.display = 'none';
        submitBtn.disabled = true;
        btnText.textContent = 'Verifying Account...';
        btnSpinner.classList.remove('d-none');

        const formData = {
            email: document.getElementById('userEmail').value.trim(),
            password: document.getElementById('userPassword').value.trim()
        };

        try {
            const res = await fetch('auth/user_login_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                btnText.textContent = 'Success! Redirecting...';
                window.location.href = data.redirect || 'user_dashboard.php';
            } else {
                alertBox.textContent = data.message || 'Invalid credentials.';
                alertBox.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Log In to Account';
                btnSpinner.classList.add('d-none');
            }
        } catch (err) {
            alertBox.textContent = 'Network error. Please try again.';
            alertBox.style.display = 'block';
            submitBtn.disabled = false;
            btnText.textContent = 'Log In to Account';
            btnSpinner.classList.add('d-none');
        }
    }
    </script>

</body>
</html>
