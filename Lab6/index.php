<?php
// index.php
require_once 'auth.php';

$action = $_POST['action'] ?? '';
$submission_id = intval($_POST['submission_id'] ?? 0);
$message = '';

if ($action === 'delete' && $submission_id > 0) {
    $pdo->prepare("DELETE FROM submission_languages WHERE submission_id = ?")->execute([$submission_id]);
    $pdo->prepare("DELETE FROM submissions WHERE id = ?")->execute([$submission_id]);
    $message = 'Запись успешно удалена';
}

if ($action === 'update' && $submission_id > 0) {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth_date = $_POST['birth_date'] ?? '';
    $gender = $_POST['gender'] ?? null;
    $about_self = $_POST['about_self'] ?? '';
    $contract_accepted = isset($_POST['contract_accepted']) ? 1 : 0;
    $languages = $_POST['languages'] ?? [];
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            UPDATE submissions 
            SET full_name=?, phone=?, email=?, birth_date=?, gender=?, about_self=?, contract_accepted=?
            WHERE id=?
        ");
        $stmt->execute([$full_name, $phone, $email, $birth_date, $gender, $about_self, $contract_accepted, $submission_id]);
        
        $pdo->prepare("DELETE FROM submission_languages WHERE submission_id = ?")->execute([$submission_id]);
        
        if (!empty($languages)) {
            $stmt = $pdo->prepare("INSERT INTO submission_languages (submission_id, language_id) VALUES (?, ?)");
            foreach ($languages as $lang_id) {
                $stmt->execute([$submission_id, $lang_id]);
            }
        }
        
        $pdo->commit();
        $message = 'Запись успешно обновлена';
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Ошибка при обновлении';
    }
}

$stmt = $pdo->query("SELECT * FROM submissions ORDER BY id DESC LIMIT 100");
$submissions = $stmt->fetchAll();

if (!empty($submissions)) {
    $ids = array_column($submissions, 'id');
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT sl.submission_id, GROUP_CONCAT(pl.name SEPARATOR ', ') as langs
        FROM submission_languages sl
        JOIN programming_languages pl ON sl.language_id = pl.id
        WHERE sl.submission_id IN ($placeholders)
        GROUP BY sl.submission_id
    ");
    $stmt->execute($ids);
    $langs_data = $stmt->fetchAll();
    
    $langs_map = [];
    foreach ($langs_data as $row) {
        $langs_map[$row['submission_id']] = $row['langs'];
    }
    
    foreach ($submissions as &$sub) {
        $sub['favorite_langs'] = $langs_map[$sub['id']] ?? 'Не выбраны';
    }
    unset($sub);
}

$stmt = $pdo->query("SELECT * FROM programming_languages ORDER BY sort_order");
$all_languages = $stmt->fetchAll();

$stmt = $pdo->query("
    SELECT pl.name, COUNT(sl.submission_id) as count 
    FROM programming_languages pl
    LEFT JOIN submission_languages sl ON pl.id = sl.language_id
    GROUP BY pl.id, pl.name
    ORDER BY count DESC
");
$language_stats = $stmt->fetchAll();

$total_submissions = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
$accepted_contract = $pdo->query("SELECT COUNT(*) FROM submissions WHERE contract_accepted = 1")->fetchColumn();

$stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM submissions WHERE gender IS NOT NULL GROUP BY gender");
$gender_stats = $stmt->fetchAll();
$gender_data = [];
foreach ($gender_stats as $row) {
    $gender_data[$row['gender']] = $row['count'];
}

$submission_langs = [];
$stmt = $pdo->query("SELECT * FROM submission_languages");
while ($row = $stmt->fetch()) {
    $submission_langs[$row['submission_id']][] = $row['language_id'];
}

// Очищаем HTML перед передачей в JSON
function cleanForJson($data) {
    if (is_array($data)) {
        foreach ($data as &$value) {
            if (is_string($value)) {
                $value = htmlspecialchars_decode($value);
            }
        }
    }
    return $data;
}

$submissions_clean = cleanForJson($submissions);

function sanitizeForJson($data) {
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Удаляем все HTML теги и декодируем сущности
                $result[$key] = strip_tags(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
            } elseif (is_array($value)) {
                $result[$key] = sanitizeForJson($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
    return $data;
}

$submissions_clean = [];
foreach ($submissions as $sub) {
    $submissions_clean[] = sanitizeForJson($sub);
}

include 'admin.html';
?>