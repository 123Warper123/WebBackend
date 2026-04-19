<?php
session_start();
require_once 'DBconf.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$submission_id = $_GET['id'] ?? 0;

// Проверка, что пользователь редактирует свою заявку
if ($submission_id != $_SESSION['user_id']) {
    die("❌ Ошибка доступа: вы можете редактировать только свою заявку");
}

// Функция для безопасного вывода
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Получаем данные заявки
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE id = ?");
$stmt->execute([$submission_id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    die("Заявка не найдена");
}

// Получаем выбранные языки
$lang_stmt = $pdo->prepare("SELECT pl.name FROM programming_languages pl 
                            JOIN submission_languages sl ON pl.id = sl.language_id 
                            WHERE sl.submission_id = ?");
$lang_stmt->execute([$submission_id]);
$selected_langs = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);

// Обработка обновления
$success = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $birth_date = trim($_POST['birth_date'] ?? '');
    $gender = $_POST['gender'] ?? null;
    $about_self = trim($_POST['about_self'] ?? '');
    $selected_langs_post = $_POST['favorite_langs'] ?? [];
    
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
        $errors[] = "Введите корректный email";
    }
    
    if (empty($selected_langs_post)) {
        $errors[] = "Выберите хотя бы один язык программирования";
    }
    
    // Если нет ошибок - обновляем
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // 1. Обновляем основную информацию
            $update_sql = "UPDATE submissions SET full_name = ?, phone = ?, email = ?, birth_date = ?, gender = ?, about_self = ? WHERE id = ?";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $about_self, $submission_id]);
            
            // 2. ОБНОВЛЯЕМ ЯЗЫКИ (удаляем старые)
            $delete_sql = "DELETE FROM submission_languages WHERE submission_id = ?";
            $delete_stmt = $pdo->prepare($delete_sql);
            $delete_stmt->execute([$submission_id]);
            
            // 3. Добавляем новые языки (исправленный код)
            if (!empty($selected_langs_post)) {
                // Создаем плейсхолдеры для запроса (работает для любого количества языков)
                $placeholders = implode(',', array_fill(0, count($selected_langs_post), '?'));
                $lang_sql = "SELECT id, name FROM programming_languages WHERE name IN ($placeholders)";
                $lang_stmt = $pdo->prepare($lang_sql);
                $lang_stmt->execute($selected_langs_post);
                
                // Получаем соответствие name => id
                $languages = [];
                while ($row = $lang_stmt->fetch(PDO::FETCH_ASSOC)) {
                    $languages[$row['name']] = $row['id'];
                }
                
                // Сохраняем связи
                $link_sql = "INSERT INTO submission_languages (submission_id, language_id) VALUES (?, ?)";
                $link_stmt = $pdo->prepare($link_sql);
                
                foreach ($selected_langs_post as $lang_name) {
                    if (isset($languages[$lang_name])) {
                        $link_stmt->execute([$submission_id, $languages[$lang_name]]);
                    }
                }
            }
            
            $pdo->commit();
            $success = "✅ Данные успешно обновлены!";
            
            // Обновляем локальные переменные для отображения
            $submission['full_name'] = $full_name;
            $submission['phone'] = $phone;
            $submission['email'] = $email;
            $submission['birth_date'] = $birth_date;
            $submission['gender'] = $gender;
            $submission['about_self'] = $about_self;
            $selected_langs = $selected_langs_post;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Ошибка при сохранении: " . $e->getMessage();
        }
    }
}

// Доступные языки для отображения в форме
$available_langs = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskel", "Clojure", "Prolog", "Scala"];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование заявки</title>
    <style>
        .main { max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; background: #f9f9f9; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="tel"], input[type="email"], select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        select[multiple] { height: 150px; }
        .radio-group { display: inline-block; margin-right: 15px; }
        .radio-group label { display: inline-block; font-weight: normal; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .user-info { background: #e7f3ff; padding: 10px; border-radius: 4px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        .logout { background: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .logout:hover { background: #c82333; }
        .btn-back { display: inline-block; margin-left: 10px; padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; }
        .btn-back:hover { background: #5a6268; }
    </style>
</head>
<body>
    <div class="main">
        <div class="user-info">
            <div>👤 Вы вошли как: <strong><?= h($_SESSION['user_name']) ?></strong> (логин: <?= h($_SESSION['user_login']) ?>)</div>
            <a href="logout.php" class="logout">Выйти</a>
        </div>
        
        <h2>✏️ Редактирование заявки #<?= $submission_id ?></h2>
        
        <?php if ($success): ?>
            <div class="success"><?= h($success) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>⚠️ Ошибки:</strong><br>
                <?php foreach ($errors as $error): ?>
                    • <?= h($error) ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>ФИО: *</label>
                <input type="text" name="full_name" value="<?= h($submission['full_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Телефон: *</label>
                <input type="tel" name="phone" value="<?= h($submission['phone']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Email: *</label>
                <input type="email" name="email" value="<?= h($submission['email']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Дата рождения: *</label>
                <input type="date" name="birth_date" value="<?= h($submission['birth_date']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Пол:</label>
                <div class="radio-group">
                    <input type="radio" name="gender" value="Мужчина" id="male" <?= $submission['gender'] == 'Мужчина' ? 'checked' : '' ?>>
                    <label for="male">Мужчина</label>
                </div>
                <div class="radio-group">
                    <input type="radio" name="gender" value="Женщина" id="female" <?= $submission['gender'] == 'Женщина' ? 'checked' : '' ?>>
                    <label for="female">Женщина</label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Любимые языки программирования: *</label>
                <select name="favorite_langs[]" multiple required>
                    <?php foreach ($available_langs as $lang): ?>
                        <option value="<?= h($lang) ?>" <?= in_array($lang, $selected_langs) ? 'selected' : '' ?>>
                            <?= h($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Удерживайте Ctrl (или Cmd на Mac) для выбора нескольких языков</small>
            </div>
            
            <div class="form-group">
                <label>Расскажите о себе:</label>
                <textarea name="about_self" rows="5"><?= h($submission['about_self']) ?></textarea>
            </div>
            
            <button type="submit">💾 Сохранить изменения</button>
            <a href="view_data.php" class="btn-back">← Назад к списку</a>
        </form>
    </div>
</body>
</html>