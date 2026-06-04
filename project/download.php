<?php
    $require_auth = true;
    require_once 'blocks/check_auth.php';
    require_once 'config/database.php';

    // Получаем ID файла
    $file_id = $_GET['id'] ?? 0;
    if (!$file_id) {
        header('Location: files.php?error=file&msg=Файл не найден');
        exit;
    }

    // Проверяем, что файл принадлежит пользователю
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if (!$file) {
        header('Location: files.php?error=file&msg=Файл не найден или доступ запрещён');
        exit;
    }

    // Путь к файлу на диске
    $file_path = __DIR__ . "/../uploads/user_{$_SESSION['user_id']}/" . $file['saved_name'];

    // Проверяем, существует ли файл физически
    if (!file_exists($file_path)) {
        header('Location: files.php?error=file&msg=Файл на сервере не найден');
        exit;
    }

    // Отдаём файл на скачивание
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
    header('Content-Length: ' . $file['file_size']);
    header('Cache-Control: private, max-age=0, must-revalidate');

    // Читаем и выводим файл
    readfile($file_path);
    exit;
?>