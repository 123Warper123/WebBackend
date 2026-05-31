<?php
// api.php - REST API
require_once __DIR__ . '/config/database.php';

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Получаем action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Если action не указан, пробуем определить из пути
if (empty($action)) {
    $path = $_SERVER['REQUEST_URI'];
    if (preg_match('/api\.php\/([a-zA-Z0-9_]+)/', $path, $matches)) {
        $action = $matches[1];
    }
}

// Определяем метод
$method = $_SERVER['REQUEST_METHOD'];

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Функция ответа
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Тестовый эндпоинт
if ($action === 'test') {
    sendResponse(['status' => 'ok', 'message' => 'API test endpoint works']);
}

// GET /csrf - получение CSRF токена
if ($method === 'GET' && $action === 'csrf') {
    sendResponse(['csrf_token' => generateCsrfToken()]);
}

// GET /check - проверка авторизации
if ($method === 'GET' && $action === 'check') {
    if (isset($_SESSION['user_id'])) {
        sendResponse([
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'] ?? '',
                'name' => $_SESSION['user_name'] ?? ''
            ]
        ]);
    } else {
        sendResponse(['authenticated' => false]);
    }
}

// POST /feedback - отправка формы
if ($method === 'POST' && $action === 'feedback') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // CSRF проверка
    if (isset($input['csrf_token']) && !validateCsrfToken($input['csrf_token'])) {
        sendResponse(['error' => 'CSRF token validation failed'], 403);
    }
    
    // Honeypot
    if (!empty($input['_gotcha'])) {
        sendResponse(['success' => true, 'message' => 'Thank you']);
    }
    
    // Валидация
    $errors = [];
    $name = sanitizeInput($input['name'] ?? '');
    $phone = sanitizeInput($input['phone'] ?? '');
    $email = sanitizeInput($input['email'] ?? '');
    $message = sanitizeInput($input['message'] ?? '');
    
    if (empty($name) || !validateName($name)) $errors['name'] = 'Имя должно содержать только буквы (2-50 символов)';
    if (empty($phone) || !validatePhone($phone)) $errors['phone'] = 'Неверный формат телефона';
    if (empty($email) || !validateEmail($email)) $errors['email'] = 'Неверный формат email';
    if (empty($message) || !validateMessage($message)) $errors['message'] = 'Сообщение должно быть от 10 до 1000 символов';
    
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400);
    }
    
    // Очистка телефона
    $phoneClean = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phoneClean) === 11 && $phoneClean[0] === '7') {
        $phoneClean = substr($phoneClean, 1);
    }
    
    // Поиск или создание пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    $isNewUser = false;
    $generatedPassword = null;
    
    if (!$user) {
        $generatedPassword = generateRandomPassword();
        $hashedPassword = password_hash($generatedPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, name, phone, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$email, $hashedPassword, $name, $phoneClean, $message]);
        $userId = $pdo->lastInsertId();
        $isNewUser = true;
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, message = ? WHERE id = ?");
        $stmt->execute([$name, $phoneClean, $message, $user['id']]);
        $userId = $user['id'];
        
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $name;
    }
    
    // Сохранение сообщения
    $stmt = $pdo->prepare("INSERT INTO feedback_messages (name, phone, email, message, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $phoneClean, $email, $message, $userId]);
    
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    
    $response = [
        'success' => true,
        'message' => 'Сообщение успешно отправлено!',
        'user_id' => $userId
    ];
    
    if ($isNewUser) {
        $response['login'] = $email;
        $response['password'] = $generatedPassword;
    }
    
    sendResponse($response);
}

// POST /login
if ($method === 'POST' && $action === 'login') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        sendResponse(['error' => 'Email and password required'], 400);
    }
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        sendResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name']
            ]
        ]);
    } else {
        sendResponse(['error' => 'Invalid credentials'], 401);
    }
}

// POST /logout
if ($method === 'POST' && $action === 'logout') {
    $_SESSION = [];
    session_destroy();
    sendResponse(['success' => true]);
}

// GET /user - получение данных пользователя
if ($method === 'GET' && $action === 'user') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        sendResponse(['error' => 'User ID required'], 400);
    }
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $id) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }
    
    $stmt = $pdo->prepare("SELECT id, email, name, phone, message FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if ($user) {
        sendResponse($user);
    } else {
        sendResponse(['error' => 'User not found'], 404);
    }
}

// PUT /user - обновление данных
if ($method === 'PUT' && $action === 'user') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        sendResponse(['error' => 'User ID required'], 400);
    }
    
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $id) {
        sendResponse(['error' => 'Unauthorized'], 401);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        sendResponse(['error' => 'Invalid JSON'], 400);
    }
    
    $errors = [];
    $updateData = [];
    
    if (isset($input['name'])) {
        $name = sanitizeInput($input['name']);
        if (!validateName($name)) {
            $errors['name'] = 'Имя должно содержать только буквы (2-50 символов)';
        } else {
            $updateData['name'] = $name;
        }
    }
    
    if (isset($input['phone'])) {
        $phone = sanitizeInput($input['phone']);
        if (!validatePhone($phone)) {
            $errors['phone'] = 'Неверный формат телефона';
        } else {
            $phoneClean = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phoneClean) === 11 && $phoneClean[0] === '7') {
                $phoneClean = substr($phoneClean, 1);
            }
            $updateData['phone'] = $phoneClean;
        }
    }
    
    if (isset($input['message'])) {
        $message = sanitizeInput($input['message']);
        if (!validateMessage($message)) {
            $errors['message'] = 'Сообщение должно быть от 10 до 1000 символов';
        } else {
            $updateData['message'] = $message;
        }
    }
    
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400);
    }
    
    if (empty($updateData)) {
        sendResponse(['message' => 'Nothing to update'], 200);
    }
    
    $fields = [];
    $params = [];
    foreach ($updateData as $field => $value) {
        $fields[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $id;
    
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    sendResponse(['success' => true, 'message' => 'Profile updated'], 200);
}

// Если ничего не найдено
sendResponse(['error' => "Action '$action' not found", 'available_actions' => ['test', 'csrf', 'check', 'feedback', 'login', 'logout', 'user']], 404);
?>