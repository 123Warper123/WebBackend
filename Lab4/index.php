<?php
// Функция для получения ошибок из cookies
function getErrorsFromCookie() {
    if (!isset($_COOKIE['form_errors'])) {
        return [];
    }
    $errors_json = base64_decode($_COOKIE['form_errors']);
    $errors = json_decode($errors_json, true);
    
    // Удаляем cookies после чтения
    setcookie('form_errors', '', time() - 3600, '/');
    
    return is_array($errors) ? $errors : [];
}

// Функция для получения старых данных из cookies
function getOldDataFromCookie() {
    if (!isset($_COOKIE['old_data'])) {
        return [];
    }
    $data_json = base64_decode($_COOKIE['old_data']);
    $data = json_decode($data_json, true);
    
    // Удаляем cookies после чтения
    setcookie('old_data', '', time() - 3600, '/');
    
    return is_array($data) ? $data : [];
}

// Функция для получения данных по умолчанию из cookies
function getDefaultDataFromCookie() {
    if (!isset($_COOKIE['default_data'])) {
        return [];
    }
    $data_json = base64_decode($_COOKIE['default_data']);
    return json_decode($data_json, true) ?: [];
}

// Получаем ошибки из cookies
$errors_array = getErrorsFromCookie();

// Получаем старые данные (если были ошибки)
$old_data = getOldDataFromCookie();

// Получаем данные по умолчанию из cookies (успешные отправки)
$default_data = getDefaultDataFromCookie();

// Функция для получения значения поля
function getFieldValue($field, $old_data, $default_data) {
    if (!empty($old_data) && isset($old_data[$field])) {
        return htmlspecialchars($old_data[$field]);
    }
    if (!empty($default_data) && isset($default_data[$field])) {
        return htmlspecialchars($default_data[$field]);
    }
    return '';
}

// Функция для проверки checked/selected
function isChecked($field, $value, $old_data, $default_data) {
    if (!empty($old_data) && isset($old_data[$field])) {
        if (is_array($old_data[$field])) {
            return in_array($value, $old_data[$field]);
        }
        return $old_data[$field] == $value;
    }
    if (!empty($default_data) && isset($default_data[$field])) {
        if (is_array($default_data[$field])) {
            return in_array($value, $default_data[$field]);
        }
        return $default_data[$field] == $value;
    }
    return false;
}

// Функция для проверки selected в select multiple
function isSelected($value, $old_data, $default_data, $field = 'favorite_langs') {
    if (!empty($old_data) && isset($old_data[$field]) && is_array($old_data[$field])) {
        return in_array($value, $old_data[$field]);
    }
    if (!empty($default_data) && isset($default_data[$field]) && is_array($default_data[$field])) {
        return in_array($value, $default_data[$field]);
    }
    return false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрационная форма</title>
    <style>
        .main {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #f9f9f9;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select[multiple] {
            height: 150px;
        }

        .radio-group {
            display: inline-block;
            margin-right: 15px;
        }

        .radio-group label {
            display: inline-block;
            font-weight: normal;
        }

        button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .field-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        
        input.error-field,
        select.error-field,
        textarea.error-field {
            border-color: #dc3545;
            background-color: #fff8f8;
        }

        .success {
            color: green;
            background: #d4edda;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .error-messages {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .error-messages h3 {
            color: #721c24;
            margin-bottom: 10px;
            font-size: 16px;
            margin-top: 0;
        }
        
        .error-messages ul {
            margin-left: 20px;
            color: #721c24;
            margin-bottom: 0;
        }
        
        .error-messages li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <?php if (!empty($errors_array)): ?>
    <div class="main" style="background: #f8d7da; border-color: #f5c6cb; margin-bottom: 0;">
        <div class="error-messages" style="margin-bottom: 0;">
            <h3>⚠️ Пожалуйста, исправьте следующие ошибки:</h3>
            <ul>
                <?php foreach ($errors_array as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>
    
    <form class="main" action="form.php" method="POST">
        <h2>Форма регистрации</h2>

        <div class="form-group">
            <label>ФИО: *</label>
            <input type="text" 
                   name="full_name" 
                   value="<?= getFieldValue('full_name', $old_data, $default_data) ?>"
                   class="<?= (isset($errors_array) && preg_grep('/ФИО/', $errors_array)) ? 'error-field' : '' ?>"
                   placeholder="Иванов Иван Иванович">
            <?php if (isset($errors_array) && preg_grep('/ФИО/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'ФИО') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Телефон: *</label>
            <input type="tel" 
                   name="phone" 
                   value="<?= getFieldValue('phone', $old_data, $default_data) ?>"
                   class="<?= (isset($errors_array) && preg_grep('/Телефон/', $errors_array)) ? 'error-field' : '' ?>"
                   placeholder="+7XXXXXXXXXX">
            <?php if (isset($errors_array) && preg_grep('/Телефон/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'Телефон') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
            <small>Форматы: +7 123 456 78 90, 89123456789, 71234567890</small>
        </div>

        <div class="form-group">
            <label>Email: *</label>
            <input type="email" 
                   name="email" 
                   value="<?= getFieldValue('email', $old_data, $default_data) ?>"
                   class="<?= (isset($errors_array) && preg_grep('/Email/', $errors_array)) ? 'error-field' : '' ?>"
                   placeholder="example@mail.ru">
            <?php if (isset($errors_array) && preg_grep('/Email/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'Email') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Дата рождения: *</label>
            <input type="text" 
                   name="birth_date" 
                   value="<?= getFieldValue('birth_date', $old_data, $default_data) ?>"
                   class="<?= (isset($errors_array) && preg_grep('/Дата/', $errors_array)) ? 'error-field' : '' ?>"
                   placeholder="ДД-ММ-ГГГГ">
            <?php if (isset($errors_array) && preg_grep('/Дата/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'Дата') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
            <small>Формат: ДД-ММ-ГГГГ (например, 15-05-1990)</small>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <div class="radio-group">
                <input type="radio" name="gender" value="Мужчина" id="male"
                       <?= isChecked('gender', 'Мужчина', $old_data, $default_data) ? 'checked' : '' ?>>
                <label for="male">Мужчина</label>
            </div>
            <div class="radio-group">
                <input type="radio" name="gender" value="Женщина" id="female"
                       <?= isChecked('gender', 'Женщина', $old_data, $default_data) ? 'checked' : '' ?>>
                <label for="female">Женщина</label>
            </div>
        </div>
        
        <div class="form-group">
            <label>Любимые языки программирования: *</label>
            <select name="favorite_langs[]" multiple 
                    class="<?= (isset($errors_array) && preg_grep('/язык/', $errors_array)) ? 'error-field' : '' ?>">
                <option value="Pascal" <?= isSelected('Pascal', $old_data, $default_data) ? 'selected' : '' ?>>Pascal</option>
                <option value="C" <?= isSelected('C', $old_data, $default_data) ? 'selected' : '' ?>>C</option>
                <option value="C++" <?= isSelected('C++', $old_data, $default_data) ? 'selected' : '' ?>>C++</option>
                <option value="JavaScript" <?= isSelected('JavaScript', $old_data, $default_data) ? 'selected' : '' ?>>JavaScript</option>
                <option value="PHP" <?= isSelected('PHP', $old_data, $default_data) ? 'selected' : '' ?>>PHP</option>
                <option value="Python" <?= isSelected('Python', $old_data, $default_data) ? 'selected' : '' ?>>Python</option>
                <option value="Java" <?= isSelected('Java', $old_data, $default_data) ? 'selected' : '' ?>>Java</option>
                <option value="Haskel" <?= isSelected('Haskel', $old_data, $default_data) ? 'selected' : '' ?>>Haskel</option>
                <option value="Clojure" <?= isSelected('Clojure', $old_data, $default_data) ? 'selected' : '' ?>>Clojure</option>
                <option value="Prolog" <?= isSelected('Prolog', $old_data, $default_data) ? 'selected' : '' ?>>Prolog</option>
                <option value="Scala" <?= isSelected('Scala', $old_data, $default_data) ? 'selected' : '' ?>>Scala</option>
            </select>
            <small>Удерживайте Ctrl (или Cmd на Mac) для выбора нескольких языков</small>
            <?php if (isset($errors_array) && preg_grep('/язык/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'язык') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Расскажите о себе:</label>
            <textarea name="about_self" rows="5" 
                      class="<?= (isset($errors_array) && preg_grep('/О себе/', $errors_array)) ? 'error-field' : '' ?>"
                      placeholder="Ваши навыки, опыт, интересы..."><?= getFieldValue('about_self', $old_data, $default_data) ?></textarea>
            <?php if (isset($errors_array) && preg_grep('/О себе/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'О себе') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <input type="checkbox" name="contract" value="1" 
                   <?= isChecked('contract', '1', $old_data, $default_data) ? 'checked' : '' ?>>
            <label style="display: inline-block; font-weight: normal;">С контрактом ознакомлен *</label>
            <?php if (isset($errors_array) && preg_grep('/контракт/', $errors_array)): ?>
                <?php foreach($errors_array as $e): if(strpos($e, 'контракт') !== false): ?>
                    <span class="field-error"><?= htmlspecialchars($e) ?></span>
                <?php endif; endforeach; ?>
            <?php endif; ?>
        </div>

        <button type="submit">Отправить</button>
    </form>
</body>
</html>