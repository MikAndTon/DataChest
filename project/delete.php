<?php
    $require_auth = true;
    require_once 'blocks/check_auth.php';
    require_once 'config/database.php';

    // Получаем ID файла
    $file_id = $_GET['id'] ?? 0;
    if (!$file_id) {
        header('Location: files.php?error=delete&msg=Файл не найден');
        exit;
    }

    // Проверяем, что файл принадлежит пользователю и получаем его имя
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if (!$file) {
        header('Location: files.php?error=delete&msg=Файл не найден или доступ запрещён');
        exit;
    }

    // Путь к файлу на диске
    $file_path = __DIR__ . "/../uploads/user_{$_SESSION['user_id']}/" . $file['saved_name'];

    // Удаляем файл с диска (если существует)
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Удаляем запись из БД
    $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $_SESSION['user_id']]);

    // Перенаправляем обратно
    header('Location: files.php?success=delete');
    exit;
?>