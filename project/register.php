<?php
    $require_auth = false;
    require_once 'blocks/check_auth.php';
    
    require_once 'config/database.php';

    $error = null;
    $success = false;

    // Если пользователь нажал "Зарегистрироваться"
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $agree = $_POST['agree'] ?? '';

        // Проверка полей...
        if (empty($login) || empty($password) || empty($confirm)) {
            $error = "Все поля обязательны для заполнения";
        } elseif ($password !== $confirm) {
            $error = "Пароли не совпадают";
        } elseif (strlen($password) < 4) {
            $error = "Пароль должен быть не менее 4 символов";
        } elseif (!$agree) {
            $error = "Вы должны согласиться с условиями использования";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
            $stmt->execute([$login]);
            
            if ($stmt->fetch()) {
                $error = "Пользователь с таким логином уже существует";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (login, password_hash) VALUES (?, ?)");
                
                if ($stmt->execute([$login, $hash])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_login'] = $login;
                    
                    header('Location: files.php');
                    exit;
                } else {
                    $error = "Ошибка при регистрации. Попробуйте позже.";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html>

    <?php
        $title = 'Регистрация. Data Chest';
        require_once 'blocks/head.php';
    ?>
    
    <body>
        <div id="index-pane">
            <p id="index-label">РЕГИСТРАЦИЯ</p>
            
            <form id="authorization-form" action="register.php" method="POST">
                <input class="index-input" type="text" name="login" placeholder="Логин" required/>
                <input class="index-input" type="password" name="password"                placeholder="Пароль" required/>
                <input class="index-input" type="password" name="confirm_password" placeholder="Повторите пароль" required/>
                <label id="index-license">
                    <input id="index-checkbox" type="checkbox" name="agree" value="yes" required/>                    Продолжая, я принимаю <a href="license.php" target="_blank">условия использования</a>
                </label>
                <input id="index-button" type="submit" value="Зарегистрироваться"/>
            </form>
            
            <?php if ($error): ?>
                <p id="index-error"><?= $error ?></p>
            <?php endif; ?>

            <p id="index-helper">Уже есть в системе? <a href="index.php">Войдите!</a></p>
        </div>
    </body>
</html>