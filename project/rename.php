<?php
    $require_auth = true;
    require_once 'blocks/check_auth.php';
    require_once 'config/database.php';

    $file_id = $_GET['id'] ?? 0;
    $error = null;

    // Получаем информацию о файле
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$file_id, $_SESSION['user_id']]);
    $file = $stmt->fetch();

    if (!$file) {
        header('Location: files.php?error=rename&msg=Файл не найден');
        exit;
    }

    // Если форма отправлена
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_name = trim($_POST['new_name'] ?? '');
        
        if (empty($new_name)) {
            $error = "Имя файла не может быть пустым";
        } elseif (!preg_match('/^[a-zA-Zа-яА-Я0-9_\-\.\(\)\s]+$/u', $new_name)) {
            $error = "Имя содержит недопустимые символы";
        } else {
            // Сохраняем расширение от старого имени, если пользователь его не указал
            $old_ext = pathinfo($file['original_name'], PATHINFO_EXTENSION);
            $new_ext = pathinfo($new_name, PATHINFO_EXTENSION);
            
            if (empty($new_ext) && !empty($old_ext)) {
                $new_name .= '.' . $old_ext;
            }
            
            // Обновляем имя в БД
            $stmt = $pdo->prepare("UPDATE files SET original_name = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$new_name, $file_id, $_SESSION['user_id']]);
            
            header('Location: files.php?success=rename');
            exit;
        }
    }
?>

<!DOCTYPE html>
<html>

    <?php 
        $title = 'Переименование. Data Chest';
        require_once 'blocks/head.php'; 
    ?>
    
    <body>
        <div id="rename-pane">
            <p id="rename-label">ПЕРЕИМЕНОВАНИЕ</p>
            
            <form method="POST">
                <input id="rename-input" type="text" name="new_name" value="<?= htmlspecialchars($file['original_name']) ?>" required/>
                <div>
                    <input id="rename-button" type="submit" value="Сохранить"/>
                    <a href="files.php" id="rename-button">Отмена</a>
                </div>
            </form>
            
            <?php if ($error): ?>
                <p id="index-error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            
        </div>
    </body>
</html>