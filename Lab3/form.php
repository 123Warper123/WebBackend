<?php
// process.php - обработка формы с нормализованной БД
session_start();
require_once 'DBconf.php';

// Функция валидации и очистки
function validateAndClean($data) {
    return htmlspecialchars(trim($data));
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Массив для ошибок
$errors = [];

// Получение данных из формы
$full_name = validateAndClean($_POST['full_name'] ?? '');
$phone = validateAndClean($_POST['phone'] ?? '');
$email = validateAndClean($_POST['email'] ?? '');
$birth_date = $_POST['birth_date'] ?? '';
$gender = $_POST['gender'] ?? null;
$about_self = validateAndClean($_POST['about_self'] ?? '');
$contract_accepted = isset($_POST['contract']) ? 1 : 0;
$selected_langs = $_POST['favorite_langs'] ?? [];

// Валидация
if (empty($full_name)) {
    $errors[] = "ФИО обязательно для заполнения";
} elseif (strlen($full_name) < 5) {
    $errors[] = "ФИО должно содержать минимум 5 символов";
}

if (empty($phone)) {
    $errors[] = "Телефон обязателен для заполнения";
}

if (empty($email)) {
    $errors[] = "Email обязателен для заполнения";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Введите корректный email адрес";
}

if (empty($birth_date)) {
    $errors[] = "Дата рождения обязательна";
} elseif ($birth_date > date('Y-m-d')) {
    $errors[] = "Дата рождения не может быть в будущем";
}

if (empty($selected_langs)) {
    $errors[] = "Выберите хотя бы один язык программирования";
}

if (!$contract_accepted) {
    $errors[] = "Необходимо ознакомиться с контрактом";
}

// Если есть ошибки - выводим
if (!empty($errors)) {
    echo "<h2 style='color: red;'>Ошибки валидации:</h2><ul>";
    foreach ($errors as $error) {
        echo "<li>" . $error . "</li>";
    }
    echo "</ul><a href='index.html'>Вернуться к форме</a>";
    exit;
}

try {
    // Начинаем транзакцию (чтобы данные не сохранились частично)
    $pdo->beginTransaction();
    
    // 1. Вставка данных в таблицу submissions
    $sql = "INSERT INTO submissions (full_name, phone, email, birth_date, gender, about_self, contract_accepted) 
            VALUES (:full_name, :phone, :email, :birth_date, :gender, :about_self, :contract_accepted)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':email' => $email,
        ':birth_date' => $birth_date,
        ':gender' => $gender,
        ':about_self' => $about_self,
        ':contract_accepted' => $contract_accepted
    ]);
    
    // Получаем ID новой заявки (автоинкремент)
    $submission_id = $pdo->lastInsertId();
    
    // 2. Обработка языков программирования
    // Получаем соответствие названий языков и ID из справочника
    $placeholders = str_repeat('?,', count($selected_langs) - 1) . '?';
    $lang_sql = "SELECT id, name FROM programming_languages WHERE name IN ($placeholders)";
    $lang_stmt = $pdo->prepare($lang_sql);
    $lang_stmt->execute($selected_langs);
    $languages = $lang_stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [name => id]
    
    // 3. Вставка связей в таблицу submission_languages
    $link_sql = "INSERT INTO submission_languages (submission_id, language_id) VALUES (?, ?)";
    $link_stmt = $pdo->prepare($link_sql);
    
    foreach ($selected_langs as $lang_name) {
        if (isset($languages[$lang_name])) {
            $link_stmt->execute([$submission_id, $languages[$lang_name]]);
        }
    }
    
    // Фиксируем транзакцию
    $pdo->commit();
    
    // Успешное сохранение - показываем результат
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Успешно</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
            .success { color: green; background: #d4edda; padding: 20px; border-radius: 5px; max-width: 600px; margin: 0 auto; }
            .info { background: #e7f3ff; padding: 15px; margin-top: 20px; border-radius: 5px; text-align: left; }
            .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="success">
            <h2>✅ Данные успешно сохранены!</h2>
            <p><strong>Уникальный идентификатор заявки: #<?= $submission_id ?></strong></p>
            <p>Спасибо за регистрацию!</p>
        </div>
        
        <div class="info">
            <h3>Проверьте введённые данные:</h3>
            <p><strong>ФИО:</strong> <?= $full_name ?></p>
            <p><strong>Телефон:</strong> <?= $phone ?></p>
            <p><strong>Email:</strong> <?= $email ?></p>
            <p><strong>Дата рождения:</strong> <?= $birth_date ?></p>
            <p><strong>Пол:</strong> <?= $gender ?? 'Не указан' ?></p>
            <p><strong>Любимые языки:</strong> <?= implode(', ', $selected_langs) ?></p>
            <p><strong>О себе:</strong> <?= nl2br($about_self) ?></p>
        </div>
        
        <a href="index.html" class="btn">Заполнить новую форму</a>
        <a href="view_data.php" class="btn" style="background: #2196F3;">Посмотреть все записи</a>
    </body>
    </html>
    <?php
    
} catch (PDOException $e) {
    // Откат транзакции в случае ошибки
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo "<h2 style='color: red;'>❌ Ошибка при сохранении данных</h2>";
    echo "<p>Пожалуйста, попробуйте позже.</p>";
    echo "<a href='index.html'>Вернуться к форме</a>";
}
?>