<?php
    // true = перенаправить на index.php, false = перенаправить на files.php;
    $require_auth = $require_auth ?? true; 
    
    // Если пользователь авторизован - перенаправить на files.php, иначе на вход.
    session_start();
    
    if($require_auth && !isset($_SESSION['user_id'])){
        header('Location: /index.php');
        exit;
    } else if (!$require_auth && isset($_SESSION['user_id'])) {
        header('Location: /files.php');
        exit;
    }
?>