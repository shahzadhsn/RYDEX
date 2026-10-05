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
    <title>RYDEX | Customer Registration</title>

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
        body.user-register-page {
            min-height: 100vh;
            background: linear-gradient(135deg, rgba(8, 8, 8, 0.96), rgba(15, 15, 15, 0.98)), url('images/hero-car.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            padding: 30px 20px;
        }

        .user-reg-card {
            width: 100%;
            max-width: 620px;
            background: rgba(21, 21, 21, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(200, 164, 93, 0.25);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.85);
            padding: 40px;
            border-radius: 4px;
            position: relative;
        }

        .user-reg-card::before {
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
            font-size: 32px;
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
            margin-bottom: 30px;
            text-transform: uppercase;
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
            margin-top: 15px;
        }

        .auth-btn:hover {
            background: #dfc27d;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(200, 164, 93, 0.25);
        }
    </style>
</head>
<body class="user-register-page">

    <div class="user-reg-card">
        <div class="auth-brand">RYDEX</div>
        <div class="auth-subtitle">New Client Registration</div>

        <div id="userRegAlert" class="alert-rydex"></div>

        <form id="userRegForm" onsubmit="handleUserRegister(event)">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small">Full Name *</label>
                    <input type="text" id="regName" class="form-control rydex-input" placeholder="e.g. Ahmed Khan" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Email Address *</label>
                    <input type="email" id="regEmail" class="form-control rydex-input" placeholder="name@example.com" required autocomplete="email">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Phone Number *</label>
                    <input type="text" id="regPhone" class="form-control rydex-input" placeholder="+91 98201 12345" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Driving License Number *</label>
                    <input type="text" id="regLicense" class="form-control rydex-input" placeholder="DL-MH02-2022-XXXX" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Password *</label>
                    <input type="password" id="regPass" class="form-control rydex-input" placeholder="At least 6 characters" required autocomplete="new-password">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">Confirm Password *</label>
                    <input type="password" id="regPassConfirm" class="form-control rydex-input" placeholder="Re-enter password" required autocomplete="new-password">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">City</label>
                    <input type="text" id="regCity" class="form-control rydex-input" value="Mumbai">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small">State</label>
                    <input type="text" id="regState" class="form-control rydex-input" value="Maharashtra">
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small">Residential Address</label>
                    <input type="text" id="regAddress" class="form-control rydex-input" placeholder="Building, street, landmark...">
                </div>
            </div>

            <button type="submit" class="auth-btn" id="regSubmitBtn">
                <span id="regBtnText">Create Client Account</span>
                <span id="regBtnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
            </button>
        </form>

        <div class="text-center mt-4 text-muted small">
            <p class="mb-0">Already registered with RYDEX?</p>
            <a href="user_login.php" class="text-gold text-decoration-none fw-semibold">Log In to Your Account <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="text-center mt-3 pt-3 border-top border-secondary">
            <a href="index.php" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left me-1"></i> Return to Homepage</a>
        </div>
    </div>

    <script>
    async function handleUserRegister(e) {
        e.preventDefault();
        const alertBox = document.getElementById('userRegAlert');
        const submitBtn = document.getElementById('regSubmitBtn');
        const btnText = document.getElementById('regBtnText');
        const btnSpinner = document.getElementById('regBtnSpinner');

        alertBox.style.display = 'none';

        const pass = document.getElementById('regPass').value;
        const passConfirm = document.getElementById('regPassConfirm').value;

        if (pass !== passConfirm) {
            alertBox.textContent = 'Passwords do not match.';
            alertBox.style.display = 'block';
            return;
        }

        submitBtn.disabled = true;
        btnText.textContent = 'Creating Account...';
        btnSpinner.classList.remove('d-none');

        const formData = {
            full_name: document.getElementById('regName').value.trim(),
            email: document.getElementById('regEmail').value.trim(),
            phone: document.getElementById('regPhone').value.trim(),
            license_number: document.getElementById('regLicense').value.trim(),
            password: pass,
            city: document.getElementById('regCity').value.trim(),
            state: document.getElementById('regState').value.trim(),
            address: document.getElementById('regAddress').value.trim()
        };

        try {
            const res = await fetch('auth/user_register_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            const data = await res.json();

            if (data.success) {
                btnText.textContent = 'Account Created! Redirecting...';
                window.location.href = data.redirect || 'user_dashboard.php';
            } else {
                alertBox.textContent = data.message || 'Registration failed.';
                alertBox.style.display = 'block';
                submitBtn.disabled = false;
                btnText.textContent = 'Create Client Account';
                btnSpinner.classList.add('d-none');
            }
        } catch (err) {
            alertBox.textContent = 'Network error. Please try again.';
            alertBox.style.display = 'block';
            submitBtn.disabled = false;
            btnText.textContent = 'Create Client Account';
            btnSpinner.classList.add('d-none');
        }
    }
    </script>

</body>
</html>
