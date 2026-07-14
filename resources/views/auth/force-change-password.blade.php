<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Set New Password - BayCIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f9fafb; font-family: 'DM Sans', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); max-width: 400px; width: 100%; }
        .custom-input { border-radius: 8px; padding: 12px 16px; border: 1px solid #e5e7eb; }
        .custom-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); outline: none; }
    </style>
</head>
<body>

    <div class="auth-card">
        <h3 class="fw-bold mb-2">Set New Password</h3>
        <p class="text-muted mb-4" style="font-size: 14px;">Please choose a new, secure password for your account.</p>

        <div id="alert-box" class="alert d-none" style="font-size: 0.9rem;"></div>

        <form id="password-form">
            <div class="mb-3">
                <label class="form-label fw-semibold text-muted" style="font-size: 13px;">New Password</label>
                <input type="password" id="password" class="form-control custom-input" required minlength="8">
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold text-muted" style="font-size: 13px;">Confirm Password</label>
                <input type="password" id="password_confirmation" class="form-control custom-input" required minlength="8">
            </div>
            
            <button type="submit" id="submit-btn" class="btn w-100 py-3 fw-bold text-white" style="border-radius: 12px; background-color: #3b82f6;">
                Continue
            </button>
        </form>
    </div>

    <script>
        document.getElementById('password-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('submit-btn');
            const alertBox = document.getElementById('alert-box');
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;

            if (password !== password_confirmation) {
                alertBox.className = 'alert alert-danger d-block';
                alertBox.textContent = 'Passwords do not match.';
                return;
            }

            btn.disabled = true;
            btn.innerHTML = 'Processing...';
            alertBox.classList.add('d-none');

            try {
                const response = await fetch('/force-change-password-process', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ password, password_confirmation })
                });

                const data = await response.json();

                if (response.ok) {
                    alertBox.className = 'alert alert-success d-block';
                    alertBox.textContent = data.message;
                    setTimeout(() => window.location.replace(data.redirect), 1000);
                } else {
                    alertBox.className = 'alert alert-danger d-block';
                    alertBox.textContent = data.message || Object.values(data.errors)[0][0] || 'An error occurred.';
                    btn.disabled = false;
                    btn.innerHTML = 'Continue';
                }
            } catch (error) {
                alertBox.className = 'alert alert-danger d-block';
                alertBox.textContent = 'Server error. Please try again.';
                btn.disabled = false;
                btn.innerHTML = 'Continue';
            }
        });
    </script>
</body>
</html>