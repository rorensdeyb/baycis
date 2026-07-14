<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - IMS</title>
    
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .offline-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }
        .offline-icon-wrap {
            width: 100px;
            height: 100px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 24px;
            color: #64748b;
        }
        .offline-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a3a4a;
            margin-bottom: 12px;
        }
        .offline-desc {
            font-size: 15px;
            color: #5a6175;
            max-width: 400px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-retry {
            background: #1a3a4a;
            color: #fff;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 8px;
            border: none;
            transition: 0.2s;
        }
        .btn-retry:hover {
            background: #254e64;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="offline-container">
        <div class="offline-icon-wrap shadow-sm">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22.61 16.95A5 5 0 0 0 18 10h-1.26a8 8 0 0 0-7.05-6M5 5a8 8 0 0 0 4 15h9a5 5 0 0 0 1.7-.3"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        </div>
        
        <h1 class="offline-title">You are offline</h1>
        <p class="offline-desc">
            It looks like you've lost your internet connection. The Inventory Management System requires an active connection to sync data.
        </p>
        
        <button class="btn-retry" onclick="tryAgain()">Try Again</button>
    </div>
        <script>
                // 1. Grab the URL the user came from out of the address bar
                const urlParams = new URLSearchParams(window.location.search);
                // If they just typed /offline directly, default back to the home page '/'
                const returnUrl = urlParams.get('returnTo') || '/';

                // 2. Function for the "Try Again" button
                function tryAgain() {
                    window.location.href = returnUrl;
                }

                // 3. Auto-return when the browser detects the internet is back
                window.addEventListener('online', () => {
                    window.location.href = returnUrl;
                });
        </script>
    </body>
</html>