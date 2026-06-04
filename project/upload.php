<?php
    $require_auth = true;
    require_once 'blocks/check_auth.php';
    require_once 'config/database.php';


    // Проверка на "загружен ли файл?"
    if (!isset($_FILES['userfile']) || $_FILES['userfile']['error'] !== UPLOAD_ERR_OK) {
        // Ошибка загрузки
        $error = $_FILES['userfile']['error'] ?? 'Файл не загружен';
        header("Location: files.php?error=upload&msg=" . urlencode($error));
        exit;
    }

    $file = $_FILES['userfile'];
    $original_name = $file['name'];
    $tmp_path = $file['tmp_name'];
    $file_size = $file['size'];

    // Проверка на размер файла
    $max_size = 500 * 1024 * 1024; // 500 МБ
    if ($file_size > $max_size) {
        header("Location: files.php?error=size&msg=Файл слишком большой (макс. 500 МБ)");
        exit;
    }

    // Проверка на запрещённые расширения
    $blacklist = ['php', 'exe', 'bat', 'sh', 'js', 'html', 'htaccess'];
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    if (in_array($ext, $blacklist)) {
        header("Location: files.php?error=type&msg=Этот тип файлов запрещён");
        exit;
    }

    // Создание папки user-а, если нету
    $user_id = $_SESSION['user_id'];
    $user_upload_dir = __DIR__ . "/../uploads/user_{$user_id}/";
    if (!file_exists($user_upload_dir)) {
        mkdir($user_upload_dir, 0777, true);
    }
    
    
    // Генерация уникального имени файлу
    $saved_name = uniqid() . '_' . bin2hex(random_bytes(8));
    if ($ext) {
        $saved_name .= '.' . $ext;
    }

    // Помещение файла в директорию
    $destination = $user_upload_dir . $saved_name;
    if (!move_uploaded_file($tmp_path, $destination)) {
        header("Location: files.php?error=save&msg=Не удалось сохранить файл");
        exit;
    }

    // Сохраняем в БД
    try {
        $stmt = $pdo->prepare("
            INSERT INTO files (user_id, original_name, saved_name, file_size) 
            VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $original_name, $saved_name, $file_size]);
    } catch (PDOException $e) {
        // Если ошибка БД — удаляем сохранённый файл
        unlink($destination);
        header("Location: files.php?error=db&msg=Ошибка базы данных");
        exit;
    }

    
    header("Location: files.php?success=upload");
    exit;
?>