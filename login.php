<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$errors = [];
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim($_POST['login'] ?? '');
    $password   = (string)($_POST['password'] ?? '');

    if ($loginValue === '' || $password === '') {
        $errors[] = 'Введите e-mail и пароль.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $loginValue]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id']   = (int)$user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: dashboard.php');
                } else {
                    header('Location: cabinet.php');
                }
                exit;
            } else {
                $errors[] = 'Неверный e-mail или пароль.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход — «Умники и Умницы»</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/main.js"></script>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <div class="logo">
            <div class="logo-circle">у</div>
            <div class="logo-text">
                <div class="logo-title">Умники и Умницы</div>
                <div class="logo-subtitle">центр развития личности</div>
            </div>
        </div>
        <nav class="main-nav">
            <a href="index.html">Главная</a>
            <a href="about.html">О центре</a>
            <a href="programs.php">Программы</a>
            <a href="gallery.html">Фотогалерея</a>
            <a href="parents.php">Родителям</a>
            <a href="contacts.html">Контакты</a>
            <a href="news.php">Новости</a>
            <a href="login.php" class="is-current">Личный кабинет</a>
        </nav>
        <a href="parents.php" class="btn btn-sm btn-outline">Онлайн-заявка</a>
    </div>
</header>

<main>
    <section class="section section-alt">
        <div class="container">
            <div class="breadcrumbs"><a href="index.html">Главная</a> · Личный кабинет</div>
            <h1 class="page-title">Вход в личный кабинет</h1>
            <p class="section-lead">
                Войдите по своему e-mail и паролю. Нет аккаунта? Зарегистрируйтесь.
            </p>

            <?php if (!empty($errors)): ?>
                <div class="form-errors">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="big-form">
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="login"
                           value="<?php echo htmlspecialchars($loginValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           required>
                </div>
                <div class="field">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-full">Войти</button>
            </form>

            <p style="margin-top:14px;font-size:14px;">
                Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a>
            </p>
        </div>
    </section>
</main>
</body>
</html>
