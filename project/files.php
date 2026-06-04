<?php
    $require_auth = true;
    require_once 'blocks/check_auth.php';
    
    require_once 'config/database.php';
    
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $files = $stmt->fetchAll();
    
    $success_msg = '';
    $error_msg = '';

    if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'upload':
            $success_msg = 'Файл успешно загружен!';
            break;
        case 'delete':
            $success_msg = 'Файл успешно удалён!';
            break;
        case 'rename':
            $success_msg = 'Файл успешно переименован!';
            break;
    }
}

    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'size':
                $error_msg = ($_GET['msg'] ?? 'Файл слишком большой');
                break;
            case 'type':
                $error_msg = ($_GET['msg'] ?? 'Тип файла запрещён');
                break;
            case 'save':
                $error_msg = ($_GET['msg'] ?? 'Ошибка сохранения');
                break;
            case 'db':
                $error_msg = ($_GET['msg'] ?? 'Ошибка базы данных');
                break;
            default:
                $error_msg = ($_GET['msg'] ?? 'Ошибка загрузки');
        }
    }
    
    function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' МБ';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' КБ';
        } else {
            return $bytes . ' Б';
        }
    }
?>

<!DOCTYPE html>
<html>

    <?php
        $title = 'Мой сундук. Data Chest';
        require_once 'blocks/head.php';
    ?>
    
    <body>
        <div id="files-tools-pane">
            <div id="tools-pane">
            
                <div id="left-tools-pane">
                    <button class="tool-button" onclick="location.href='logout.php'" title="Выйти">
                        <img src="logout.png" alt="Выйти">
                    </button>
                </div>
                
                <div id="right-tools-pane">
                    <!-- Кнопка добавления папки
                    <button class="tool-button" id="createFolderBtn">Н</button>
                    -->
                    <button class="tool-button" id="uploadBtn" title="Загрузить">
                        <img src="upload.png" alt="Загрузить">
                    </button>
                </div>
                
            </div>
            
            <div id="files-pane">
            
                <?php if ($success_msg): ?>
                    <p id="files-success"><?= htmlspecialchars($success_msg) ?></p>
                <?php endif; ?>
                <?php if ($error_msg): ?>
                    <p id="files-error"><?= htmlspecialchars($error_msg) ?></p>
                <?php endif; ?>
                
                <table class="file-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" class="checkbox-input"></th>
                            <th>Имя файла</th>
                            <th style="width: 100px;">Размер</th>
                            <th style="width: 140px;">Дата изменения</th>
                            <th style="width: 120px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody id="fileTableBody">
                    
                        <?php if (empty($files)): ?>
                            <tr>
                                <td colspan="5" class="empty-message">Пусто...</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="checkbox-input" data-id="<?= $file['id'] ?>">
                                    </td>
                                    <td><?= htmlspecialchars($file['original_name']) ?></td>
                                    <td><?= formatFileSize($file['file_size']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($file['created_at'])) ?></td>
                                    <td class="file-actions">
                                        <button class="file-btn" onclick="location.href='download.php?id=<?= $file['id'] ?>'" title="Скачать"><img src="download.png" alt="Скачать"></button>
                                        <button class="file-btn" onclick="location.href='rename.php?id=<?= $file['id'] ?>'" title="Переименовать"><img src="rename.png" alt="Переименовать">️</button>
                                        <button class="file-btn" onclick="if(confirm('Точно удалить файл «<?= htmlspecialchars($file['original_name']) ?>»?')) location.href='delete.php?id=<?= $file['id'] ?>'" title="Удалить"><img src="delete.png" alt="Удалить">️</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                    </tbody>
                </table>

                <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data" style="display: none;">
                    <input type="file" name="userfile" id="hiddenFileInput">
                </form>
            </div>
        </div>


        <script>
            const uploadBtn = document.getElementById('uploadBtn');
            const hiddenInput = document.getElementById('hiddenFileInput');
            const uploadForm = document.getElementById('uploadForm');

            uploadBtn.addEventListener('click', function() {
                hiddenInput.click();
            });

            hiddenInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    uploadForm.submit();
                }
            });

            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function(e) {
                    const checkboxes = document.querySelectorAll('.checkbox-input');
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                });
            }
        </script>
    </body>
</html>