<?php
require_once 'DBconf.php';
session_start(); // Запускаем сессию

// Функция для очистки данных
function validateAndClean($data) {
    return trim($data);
}

// Функция для безопасного вывода
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Функция для генерации случайного логина
function generateLogin($full_name) {
    // Очищаем ФИО от спецсимволов
    $clean_name = preg_replace('/[^a-zA-Zа-яА-Я]/u', '', $full_name);
    $clean_name = mb_substr($clean_name, 0, 15);
    
    // Генерируем уникальный логин
    $login = strtolower($clean_name) . '_' . rand(100, 999);
    return $login;
}

// Функция для генерации случайного пароля
function generatePassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    return substr(str_shuffle($chars), 0, $length);
}

// Функция для сохранения ошибок в cookies
function saveErrorsToCookie($errors) {
    if (empty($errors)) {
        setcookie('form_errors', '', time() - 3600, '/');
        return;
    }
    $errors_json = json_encode($errors);
    setcookie('form_errors', base64_encode($errors_json), time() + 3600, '/');
}

// Функция для сохранения старых данных в cookies
function saveOldDataToCookie($data) {
    $data_json = json_encode($data);
    setcookie('old_data', base64_encode($data_json), time() + 60, '/');
}

// Функция для сохранения успешных данных в cookies на год
function saveDefaultDataToCookie($data) {
    $default_data = [
        'full_name' => $data['full_name'] ?? '',
        'phone' => $data['phone'] ?? '',
        'email' => $data['email'] ?? '',
        'gender' => $data['gender'] ?? '',
        'about_self' => $data['about_self'] ?? ''
    ];
    $data_json = json_encode($default_data);
    setcookie('default_data', base64_encode($data_json), time() + 365 * 24 * 3600, '/');
}

// Функция для валидации ФИО
function validateFullName($full_name, &$errors) {
    if (empty($full_name)) {
        $errors['full_name'] = "ФИО обязательно для заполнения";
        return false;
    }
    
    if (strlen($full_name) > 100) {
        $errors['full_name'] = "ФИО не должно превышать 100 символов";
        return false;
    }
    
    if (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s\-]+$/u', $full_name)) {
        $errors['full_name'] = "ФИО может содержать только буквы (русские или английские), дефис и пробелы";
        return false;
    }
    
    if (strlen($full_name) < 5) {
        $errors['full_name'] = "ФИО должно содержать минимум 5 символов";
        return false;
    }
    
    return true;
}

// Функция для валидации телефона
function validatePhone($phone, &$errors) {
    if (empty($phone)) {
        $errors['phone'] = "Телефон обязателен для заполнения";
        return false;
    }
    
    $phone_clean = preg_replace('/[^\d+]/', '', $phone);
    
    if (!preg_match('/^(\+7|8|7)?\d{10}$/', $phone_clean)) {
        $errors['phone'] = "Введите корректный номер телефона. Допустимые форматы: +7 123 456 78 90, 89123456789, 71234567890";
        return false;
    }
    
    return true;
}

// Функция для валидации email
function validateEmail($email, &$errors) {
    if (empty($email)) {
        $errors['email'] = "Email обязателен для заполнения";
        return false;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Введите корректный email адрес";
        return false;
    }
    
    return true;
}

// Функция для валидации даты рождения
function validateBirthDate($birth_date, &$errors, &$db_date) {
    if (empty($birth_date)) {
        $errors['birth_date'] = "Дата рождения обязательна для заполнения";
        return false;
    }
    
    if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $birth_date)) {
        $errors['birth_date'] = "Неверный формат даты. Используйте формат ДД-ММ-ГГГГ (например: 15-05-1990)";
        return false;
    }
    
    $date = DateTime::createFromFormat('d-m-Y', $birth_date);
    
    if (!$date || $date->format('d-m-Y') !== $birth_date) {
        $errors['birth_date'] = "Некорректная дата. Проверьте правильность дня (01-31), месяца (01-12) и года";
        return false;
    }
    
    $today = new DateTime();
    if ($date > $today) {
        $errors['birth_date'] = "Дата рождения не может быть в будущем";
        return false;
    }
    
    $age = $today->diff($date)->y;
    if ($age < 18) {
        $errors['birth_date'] = "Вам должно быть не менее 18 лет";
        return false;
    }
    
    if ($age > 120) {
        $errors['birth_date'] = "Некорректная дата рождения";
        return false;
    }
    
    $db_date = $date->format('Y-m-d');
    return true;
}

// Функция для валидации языков
function validateLanguages($selected_langs, $available_langs, &$errors) {
    if (empty($selected_langs)) {
        $errors['favorite_langs'] = "Выберите хотя бы один язык программирования";
        return false;
    }

    foreach ($selected_langs as $lang) {
        if (!in_array($lang, $available_langs)) {
            $errors['favorite_langs'] = "Выбран недопустимый язык программирования";
            return false;
        }
    }
    
    return true;
}

// Функция для валидации контракта
function validateContract($contract_accepted, &$errors) {
    if (!$contract_accepted) {
        $errors['contract'] = "Необходимо ознакомиться с контрактом";
        return false;
    }
    return true;
}

// Функция для валидации пола
function validateGender($gender, &$errors) {
    if (!empty($gender) && !in_array($gender, ['Мужчина', 'Женщина'])) {
        $errors['gender'] = "Выберите корректное значение для поля 'Пол'";
        return false;
    }
    return true;
}

// Функция для валидации "О себе"
function validateAboutSelf($about_self, &$errors) {
    if (strlen($about_self) > 1000) {
        $errors['about_self'] = "Поле 'О себе' не должно превышать 1000 символов";
        return false;
    }
    return true;
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Доступные языки
$available_langs = ["Pascal", "C", "C++", "JavaScript", "PHP", "Python", "Java", "Haskel", "Clojure", "Prolog", "Scala"];

// Получение и очистка данных
$full_name = validateAndClean($_POST['full_name'] ?? '');
$phone = validateAndClean($_POST['phone'] ?? '');
$email = validateAndClean($_POST['email'] ?? '');
$birth_date_input = trim($_POST['birth_date'] ?? '');
$gender = $_POST['gender'] ?? null;
$about_self = validateAndClean($_POST['about_self'] ?? '');
$contract_accepted = isset($_POST['contract']) ? 1 : 0;
$selected_langs = $_POST['favorite_langs'] ?? [];

// Переменная для даты в формате БД
$birth_date_for_db = '';

// Массив для ошибок
$errors = [];

// Выполняем валидацию
validateFullName($full_name, $errors);
validatePhone($phone, $errors);
validateEmail($email, $errors);
validateBirthDate($birth_date_input, $errors, $birth_date_for_db);
validateLanguages($selected_langs, $available_langs, $errors);
validateContract($contract_accepted, $errors);
validateGender($gender, $errors);
validateAboutSelf($about_self, $errors);

// Если есть ошибки - сохраняем в cookies и возвращаем на форму
if (!empty($errors)) {
    saveErrorsToCookie($errors);
    saveOldDataToCookie($_POST);
    header('Location: index.php');
    exit;
}

// Генерируем логин и пароль
$login = generateLogin($full_name);
$plain_password = generatePassword(12);
$password_hash = password_hash($plain_password, PASSWORD_DEFAULT);

// Если ошибок нет - сохраняем данные по умолчанию в cookies на год
$success_data = [
    'full_name' => $full_name,
    'phone' => $phone,
    'email' => $email,
    'gender' => $gender,
    'about_self' => $about_self
];
saveDefaultDataToCookie($success_data);

// Сохраняем в базу данных
try {
    $pdo->beginTransaction();
    
    $sql = "INSERT INTO submissions (login, password, full_name, phone, email, birth_date, gender, about_self, contract_accepted) 
            VALUES (:login, :password, :full_name, :phone, :email, :birth_date, :gender, :about_self, :contract_accepted)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':login' => $login,
        ':password' => $password_hash,  // сохраняем хеш пароля
        ':full_name' => $full_name,
        ':phone' => $phone,
        ':email' => $email,
        ':birth_date' => $birth_date_for_db,
        ':gender' => $gender,
        ':about_self' => $about_self,
        ':contract_accepted' => $contract_accepted
    ]);
    
    $submission_id = $pdo->lastInsertId();
    
    // Сохраняем выбранные языки
   if (!empty($selected_langs)) {
        $placeholders = str_repeat('?,', count($selected_langs) - 1) . '?';
        $lang_sql = "SELECT id, name FROM programming_languages WHERE name IN ($placeholders)";
        $lang_stmt = $pdo->prepare($lang_sql);
        $lang_stmt->execute($selected_langs);
        $languages = $lang_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $link_sql = "INSERT INTO submission_languages (submission_id, language_id) VALUES (?, ?)";
        $link_stmt = $pdo->prepare($link_sql);
        
        foreach ($selected_langs as $lang_name) {
            if (isset($languages[$lang_name])) {
                $link_stmt->execute([$submission_id, $languages[$lang_name]]);
            }
        }
    }
    
    $pdo->commit();
    
    // Сохраняем логин и пароль в сессию для отображения
    $_SESSION['temp_login'] = $login;
    $_SESSION['temp_password'] = $plain_password;
    $_SESSION['temp_submission_id'] = $submission_id;
    
    // Очищаем cookies с ошибками и старыми данными
    setcookie('form_errors', '', time() - 3600, '/');
    setcookie('old_data', '', time() - 3600, '/');
    
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Успешно</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f0f2f5; }
            .success { background: #d4edda; padding: 20px; border-radius: 10px; max-width: 600px; margin: 0 auto; }
            .info { background: white; padding: 20px; margin-top: 20px; border-radius: 10px; text-align: left; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .credentials { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
            .credentials p { margin: 10px 0; }
            .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
            .btn-blue { background: #2196F3; }
            .btn-red { background: #dc3545; }
            .btn:hover { opacity: 0.9; }
        </style>
    </head>
    <body>
        <div class="success">
            <h2> Данные успешно сохранены!</h2>
            <p><strong>Уникальный идентификатор заявки: #<?= $submission_id ?></strong></p>
            
            <div class="credentials">
                <h3> Ваши данные для входа:</h3>
                <p><strong>Логин:</strong> <code><?= h($login) ?></code></p>
                <p><strong>Пароль:</strong> <code><?= h($plain_password) ?></code></p>
                <p style="font-size: 12px; color: #856404;"> Сохраните эти данные! Они понадобятся для редактирования заявки.</p>
            </div>
            
            <p>Спасибо за регистрацию!</p>
        </div>
        
        <div class="info">
            <h3>Проверьте введённые данные:</h3>
            <p><strong>ФИО:</strong> <?= h($full_name) ?></p>
            <p><strong>Телефон:</strong> <?= h($phone) ?></p>
            <p><strong>Email:</strong> <?= h($email) ?></p>
            <p><strong>Дата рождения:</strong> <?= h($birth_date_input) ?></p>
            <p><strong>Пол:</strong> <?= h($gender ?? 'Не указан') ?></p>
            <p><strong>Любимые языки:</strong> <?= h(implode(', ', $selected_langs)) ?></p>
            <p><strong>О себе:</strong> <?= nl2br(h($about_self)) ?></p>
        </div>
        
        <a href="index.php" class="btn">Заполнить новую форму</a>
        <a href="view_data.php" class="btn btn-blue">Посмотреть все записи</a>
        <a href="edit_form.php?id=<?= $submission_id ?>" class="btn" style="background: #ff9800;">Редактировать</a>
    </body>
    </html>
    <?php
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error: " . $e->getMessage());
    echo "<h2 style='color: red;'> Ошибка при сохранении данных</h2>";
    echo "<p>Пожалуйста, попробуйте позже.</p>";
    echo "<p>Ошибка: " . h($e->getMessage()) . "</p>";
    echo "<a href='index.php'>Вернуться к форме</a>";
}
?>