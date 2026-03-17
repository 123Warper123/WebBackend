<?php
// Если были отправлены POST-данные, обрабатываем их
if ($_POST) {
    // Отправляем правильную кодировку
    header('Content-Type: text/html; charset=UTF-8');

    // Выводим все полученные через POST параметры
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Привет, мир!
    echo "Привет, мир!";

    // Счётчик (хранится в сессии)
    session_start();
    if (isset($_SESSION['v1'])) {
        $_SESSION['v1']++;
    } else {
        $_SESSION['v1'] = 1;
    }
    echo $_SESSION['v1'];

    // Останавливаем выполнение, чтобы не показывать форму повторно
    exit;
}
?>