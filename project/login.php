<?php
// login.php - страница входа
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: profile.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | ToolMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <i class="fas fa-tools fa-3x mb-3"></i>
                <h2>Вход в аккаунт</h2>
                <p class="mb-0">Введите email и пароль</p>
            </div>
            <div class="auth-body">
                <form id="loginForm">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div id="message" class="mb-3"></div>
                    <button type="submit" id="submitBtn" class="btn-primary">
                        <span class="btn-text">Войти</span>
                        <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                    </button>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php">← Вернуться на главную</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_URL = '/project/api.php';
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const messageDiv = document.getElementById('message');
            
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            btn.disabled = true;
            messageDiv.innerHTML = '';
            
            const data = {
                email: document.querySelector('[name="email"]').value,
                password: document.querySelector('[name="password"]').value
            };
            
            try {
                const res = await fetch(API_URL + '?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                    credentials: 'include'
                });
                const result = await res.json();
                
                if (result.success) {
                    messageDiv.innerHTML = '<div class="success-message">✅ Вход выполнен! Перенаправление...</div>';
                    setTimeout(() => window.location.href = '/project/profile.php', 1000);
                } else {
                    messageDiv.innerHTML = '<div class="error-message">❌ ' + result.error + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="error-message">❌ Ошибка соединения</div>';
            } finally {
                btnText.style.display = 'inline-block';
                spinner.style.display = 'none';
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>