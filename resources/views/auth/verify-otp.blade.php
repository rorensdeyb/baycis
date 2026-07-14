<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify Your Account - BayCIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: #f9fafb;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .otp-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        .otp-input {
            font-size: 2rem;
            letter-spacing: 12px;
            text-align: center;
            font-weight: 700;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 20px;
            color: #111827;
        }
        .otp-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }
    </style>
</head>
<body>

    <div class="otp-card">
        <h2 class="fw-bold mb-2">Check your email</h2>
        <p class="text-muted mb-4">We sent a 6-digit verification code to <br><strong>{{ session('pending_verification_email') }}</strong></p>

        <div id="alert-box" class="alert d-none" style="font-size: 0.9rem;"></div>

        <form id="otp-form">
            <input type="text" id="otp_code" class="form-control otp-input" maxlength="6" placeholder="000000" autocomplete="off" required>
            <button type="submit" id="verify-btn" class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 12px; background-color: #3b82f6; border: none;">
                Verify Account
            </button>
        </form>
    </div>

    <script>
        document.getElementById('otp-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('verify-btn');
            const alertBox = document.getElementById('alert-box');
            const otpCode = document.getElementById('otp_code').value;

            btn.disabled = true;
            btn.innerHTML = 'Verifying...';
            alertBox.classList.add('d-none');

            try {
                const response = await fetch('/verify-otp-process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ otp: otpCode })
                });

                const data = await response.json();

                if (response.ok) {
                    alertBox.className = 'alert alert-success d-block';
                    alertBox.textContent = data.message;
                    // Send them back to login page after 1.5 seconds
                    setTimeout(() => window.location.replace(data.redirect), 1500);
                } else {
                    alertBox.className = 'alert alert-danger d-block';
                    alertBox.textContent = data.message || 'Verification failed.';
                    btn.disabled = false;
                    btn.innerHTML = 'Verify Account';
                }
            } catch (error) {
                alertBox.className = 'alert alert-danger d-block';
                alertBox.textContent = 'Server error. Please try again.';
                btn.disabled = false;
                btn.innerHTML = 'Verify Account';
            }
        });
    </script>
</body>
</html>