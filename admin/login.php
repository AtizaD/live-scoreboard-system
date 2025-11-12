<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Scoreboard System</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="icon" type="image/png" href="../favicon.png">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Scoreboard Admin</h1>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <div id="errorMessage" class="error-message"></div>
            </form>
            <div class="login-info">
                <p><strong>Default credentials:</strong></p>
                <p>Username: admin</p>
                <p>Password: admin123</p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorMsg = document.getElementById('errorMessage');

            try {
                const response = await fetch('../api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.location.href = '/dashboard';
                } else {
                    errorMsg.textContent = data.error || 'Login failed';
                    errorMsg.style.display = 'block';
                }
            } catch (error) {
                errorMsg.textContent = 'Connection error. Please try again.';
                errorMsg.style.display = 'block';
            }
        });
    </script>
</body>
</html>
