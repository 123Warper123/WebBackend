<?php
// profile.php - личный кабинет
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль | ToolMaster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-tools me-2"></i>ToolMaster</a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-home"></i> Главная</a>
                <button id="logoutBtn" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Выйти</button>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <i class="fas fa-user-circle fa-4x mb-3"></i>
                        <h2>Личный кабинет</h2>
                        <p>Управление контактными данными</p>
                    </div>
                    <div class="profile-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Важная информация:</strong> Email используется как логин и не может быть изменён. 
                            Пароль выдается при регистрации и не может быть изменён.
                        </div>
                        
                        <div id="profile-content">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                                <p>Загрузка...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const API_URL = '/project/api.php';
        const userId = <?= json_encode($userId) ?>;
        
        async function loadProfile() {
            try {
                const res = await fetch(API_URL + '?action=user&id=' + userId, { credentials: 'include' });
                const user = await res.json();
                
                if (res.ok && !user.error) {
                    displayProfile(user);
                } else {
                    showError(user.error || 'Ошибка загрузки профиля');
                }
            } catch (error) {
                console.error('Load error:', error);
                showError('Ошибка загрузки');
            }
        }
        
        function displayProfile(user) {
            const container = document.getElementById('profile-content');
            container.innerHTML = `
                <div class="alert alert-secondary">
                    <strong>Email (логин):</strong> ${escapeHtml(user.email)}
                    <div class="small text-muted">Email нельзя изменить</div>
                </div>
                <form id="profileForm">
                    <div class="mb-3">
                        <label class="form-label">Имя</label>
                        <input type="text" class="form-control" name="name" value="${escapeHtml(user.name || '')}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Телефон</label>
                        <input type="tel" class="form-control" name="phone" value="${escapeHtml(user.phone || '')}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Сообщение</label>
                        <textarea class="form-control" name="message" rows="4">${escapeHtml(user.message || '')}</textarea>
                    </div>
                    <div id="formMessage" class="mb-3"></div>
                    <button type="submit" class="btn-primary">
                        <span class="btn-text">Сохранить изменения</span>
                        <span class="spinner-border spinner-border-sm" style="display: none;"></span>
                    </button>
                </form>
            `;
            
            document.getElementById('profileForm').addEventListener('submit', updateProfile);
        }
        
        async function updateProfile(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const btnText = btn.querySelector('.btn-text');
            const spinner = btn.querySelector('.spinner-border');
            const messageDiv = document.getElementById('formMessage');
            
            btnText.style.display = 'none';
            spinner.style.display = 'inline-block';
            btn.disabled = true;
            messageDiv.innerHTML = '';
            
            const data = {
                name: document.querySelector('[name="name"]').value,
                phone: document.querySelector('[name="phone"]').value,
                message: document.querySelector('[name="message"]').value
            };
            
            try {
                const res = await fetch(API_URL + '?action=user&id=' + userId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                    credentials: 'include'
                });
                const result = await res.json();
                
                if (res.ok && result.success) {
                    messageDiv.innerHTML = '<div class="success-message">✅ ' + result.message + '</div>';
                    setTimeout(() => messageDiv.innerHTML = '', 3000);
                } else if (result.errors) {
                    let html = '<div class="error-message"><ul>';
                    for (const [field, error] of Object.entries(result.errors)) {
                        html += `<li><strong>${field}:</strong> ${error}</li>`;
                    }
                    html += '</ul></div>';
                    messageDiv.innerHTML = html;
                } else {
                    messageDiv.innerHTML = '<div class="error-message">❌ ' + (result.error || 'Ошибка обновления') + '</div>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<div class="error-message">❌ Ошибка соединения</div>';
            } finally {
                btnText.style.display = 'inline-block';
                spinner.style.display = 'none';
                btn.disabled = false;
            }
        }
        
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }
        
        function showError(msg) {
            document.getElementById('profile-content').innerHTML = `<div class="error-message">❌ ${escapeHtml(msg)}</div>`;
        }
        
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            await fetch(API_URL + '?action=logout', { method: 'POST', credentials: 'include' });
            window.location.href = '/project/index.php';
        });
        
        loadProfile();
    </script>
</body>
</html>