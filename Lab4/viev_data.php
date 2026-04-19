<?php
// view_data.php - просмотр всех заявок
require_once 'DBconf.php';

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Поиск
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Подсчёт общего количества
if ($search) {
    $count_sql = "SELECT COUNT(*) as total FROM v_submissions_full 
                  WHERE full_name LIKE :search OR email LIKE :search OR phone LIKE :search";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute([':search' => "%$search%"]);
} else {
    $count_stmt = $pdo->query("SELECT COUNT(*) as total FROM submissions");
}
$total = $count_stmt->fetch()['total'];
$total_pages = ceil($total / $limit);

// Получение данных с помощью VIEW
if ($search) {
    $sql = "SELECT * FROM v_submissions_full 
            WHERE full_name LIKE :search OR email LIKE :search OR phone LIKE :search
            ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':search', "%$search%");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
} else {
    $sql = "SELECT * FROM v_submissions_full ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
}
$stmt->execute();
$submissions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Просмотр заявок</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .search-box { padding: 8px; width: 300px; margin-right: 10px; }
        .btn { display: inline-block; padding: 5px 10px; background: #2196F3; color: white; text-decoration: none; border-radius: 3px; }
        .pagination { margin-top: 20px; text-align: center; }
        .page { display: inline-block; padding: 5px 10px; margin: 0 2px; background: #f0f0f0; text-decoration: none; border-radius: 3px; }
        .active { background: #4CAF50; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Список заявок</h1>
        
        <form method="GET">
            <input type="text" name="search" class="search-box" placeholder="Поиск по ФИО, email, телефону" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Найти</button>
            <?php if ($search): ?>
                <a href="view_data.php">Сбросить</a>
            <?php endif; ?>
        </form>
        
        <?php if (count($submissions) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ФИО</th>
                        <th>Телефон</th>
                        <th>Email</th>
                        <th>Дата рождения</th>
                        <th>Языки</th>
                        <th>Дата создания</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= $row['birth_date'] ?></td>
                            <td><?= $row['favorite_langs'] ?></td>
                            <td><?= $row['created_at'] ?></td>
                            <td><a href="view_detail.php?id=<?= $row['id'] ?>" class="btn">Подробнее</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                       class="page <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php else: ?>
            <p>Нет данных для отображения</p>
        <?php endif; ?>
    </div>
</body>
</html>