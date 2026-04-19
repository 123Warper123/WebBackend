<?php
require_once 'DBconf.php';
session_start();

$is_authorized = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Все заявки</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn { display: inline-block; padding: 5px 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn:hover { opacity: 0.8; }
        .user-info { background: #e7f3ff; padding: 10px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .logout { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <?php if ($is_authorized): ?>
    <div class="user-info">
        <div>👤 Вы вошли как: <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></div>
        <a href="logout.php" class="logout">Выйти</a>
    </div>
    <?php endif; ?>
    
    <h1>Все заявки</h1>
    
    <?php
    $stmt = $pdo->query("SELECT id, full_name, phone, email, birth_date, gender FROM submissions ORDER BY id DESC");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <table>
        <thead>
            <tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Дата рождения</th><th>Пол</th><th>Действия</th></tr>
        </thead>
        <tbody>
            <?php foreach ($submissions as $sub): ?>
            <tr>
                <td><?= htmlspecialchars($sub['id']) ?></td>
                <td><?= htmlspecialchars($sub['full_name']) ?></td>
                <td><?= htmlspecialchars($sub['phone']) ?></td>
                <td><?= htmlspecialchars($sub['email']) ?></td>
                <td><?= htmlspecialchars($sub['birth_date']) ?></td>
                <td><?= htmlspecialchars($sub['gender'] ?? 'Не указан') ?></td>
                <td>
                    <a href="edit_form.php?id=<?= $sub['id'] ?>" class="btn">✏️ Редактировать</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;"><a href="index.php">← Заполнить новую форму</a></p>
</body>
</html>