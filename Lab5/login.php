<?php
session_start();
require_once 'DBconf.php';

$error = '';

// Обработка входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = "Введите логин и пароль";
    } else {
        // Ищем пользователя по логину
        $stmt = $pdo->prepare("SELECT id, login, password, full_name FROM submissions WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Авторизация успешна
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_login'] = $user['login'];
            $_SESSION['user_name'] = $user['full_name'];
            
            // Перенаправляем на страницу редактирования
            header('Location: edit_form.php?id=' . $user['id']);
            exit;
        } else {
            $error = "Неверный логин или пароль";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для редактирования</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 350px; }
        h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        .error { color: red; text-align: center; margin-bottom: 15px; padding: 10px; background: #f8d7da; border-radius: 4px; }
        .info { text-align: center; margin-top: 15px; font-size: 12px; color: #666; }
        a { color: #2196F3; text-decoration: none; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2> Вход для редактирования</h2>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Логин</label>
                <input type="text" name="login" required placeholder="Введите логин">
            </div>
            
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required placeholder="Введите пароль">
            </div>
            
            <button type="submit">Войти</button>
        </form>
        
        <div class="info">
            <p>Логин и пароль были отправлены вам при регистрации</p>
            <a href="index.php">← Вернуться к форме регистрации</a>
        </div>
    </div>
</body>
</html>