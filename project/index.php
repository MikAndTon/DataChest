<?php
    $require_auth = false;
    require_once 'blocks/check_auth.php';
    
    $error = null;

    // Если пользователь нажал "Войти"
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once 'config/database.php';
        
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Проверка полей
        if (empty($login) || empty($password)) {
            $error = "Заполните все поля";
        } else {
        
            // Проверка соответствия логина и паролей с БД
            $stmt = $pdo->prepare("SELECT id, login, password_hash FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                
                header('Location: files.php');
                exit;
            } else {
                $error = "Неверный логин или пароль";
            }
        }
    }

?>

<!DOCTYPE html>
<html>

    <?php
        $title = 'Авторизация. Data Chest';
        require_once 'blocks/head.php';
    ?>
    
    <body>
        <div id="index-pane">
            <p id="index-label">АВТОРИЗАЦИЯ</p>
            
            <form id="authorization-form" action="index.php" method="POST">
                <input class="index-input" type="text" name="login" placeholder="Логин" required/>
                <input class="index-input" type="password" name="password" placeholder="Пароль" required/>
                <input id="index-button" type="submit" value="Войти"/>
            </form>
            
            
            <?php if ($error): ?>
                <p id="index-error"><?= $error ?></p>
            <?php endif; ?>
            
            
            <p id="index-helper">Ещё не в системе? <a href="register.php">Зарегистрируйтесь!</a></p>
        </div>
    </body>
</html>