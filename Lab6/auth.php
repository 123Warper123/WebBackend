<?php
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    exit('Доступ запрещен');
}

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=form_database;charset=utf8mb4",
        "warper",
        "zero321468",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3 // Короткий таймаут
        ]
    );
    
    $stmt = $pdo->prepare("SELECT password FROM administrators WHERE login = ? LIMIT 1");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($_SERVER['PHP_AUTH_PW'], $admin['password'])) {
        $GLOBALS['pdo'] = $pdo;
        return;
    }
} catch (Exception $e) {
    // Если БД недоступна, всё равно показываем ошибку авторизации
}

header('WWW-Authenticate: Basic realm="Admin Panel"');
header('HTTP/1.0 401 Unauthorized');
exit('Неверный логин или пароль');
?>