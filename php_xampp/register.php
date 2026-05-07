<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$errors = [];
$success = false;
$name  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $password2 === '') {
        $errors[] = 'Заполните все поля.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный e-mail.';
    } elseif ($password !== $password2) {
        $errors[] = 'Пароли не совпадают.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Пользователь с таким e-mail уже зарегистрирован.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)');
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':hash' => $hash,
                    ':role' => 'parent',
                ]);
                $success = true;
                $name = $email = '';
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
    <title>Регистрация — «Умники и Умницы»</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/main.js"></script>
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <div class="logo">
            <span class="logo-mark">
                <img src="/static/images/logo.png" alt="Умники и Умницы" onerror="this.hidden=true; this.parentElement.classList.add('is-fallback')">
                <span class="logo-fallback">У</span>
            </span>
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
        <a href="parents.php" class="btn btn-sm btn-application">Онлайн-заявка</a>
    </div>
</header>

<main>
    <section class="section section-alt">
        <div class="container">
            <div class="breadcrumbs"><a href="index.html">Главная</a> · Регистрация</div>
            <h1 class="page-title">Регистрация родителя</h1>
            <p class="section-lead">
                Создайте аккаунт, чтобы входить в личный кабинет и оставлять заявки.
            </p>

            <?php if ($success): ?>
                <div class="form-success">
                    Аккаунт успешно создан! Теперь вы можете <a href="login.php">войти</a>.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="form-errors">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="big-form">
                <div class="field">
                    <label>Ваше имя</label>
                    <input type="text" name="name"
                           value="<?php echo htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           required>
                </div>
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="email"
                           value="<?php echo htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>"
                           required>
                </div>
                <div class="field">
                    <label>Пароль</label>
                    <input type="password" name="password" required>
                </div>
                <div class="field">
                    <label>Повторите пароль</label>
                    <input type="password" name="password2" required>
                </div>
                <button type="submit" class="btn btn-full">Зарегистрироваться</button>
            </form>

            <p style="margin-top:14px;font-size:14px;">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </p>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <div class="logo footer-logo">
                <span class="logo-mark">
                    <img src="/static/images/logo.png" alt="Умники и Умницы" onerror="this.hidden=true; this.parentElement.classList.add('is-fallback')">
                    <span class="logo-fallback">У</span>
                </span>
                <div class="logo-text">
                    <div class="logo-title">Умники и Умницы</div>
                    <div class="logo-subtitle">центр развития личности</div>
                </div>
            </div>
            <p class="footer-text">Детский центр в Уфе с развивающими занятиями, подготовкой к школе, логопедом и психологом для детей 3–10 лет.</p>
        </div>
        <div>
            <h3 class="footer-title">Разделы</h3>
            <ul class="footer-list">
                <li><a href="about.html">О центре</a></li>
                <li><a href="programs.php">Программы</a></li>
                <li><a href="gallery.html">Фотогалерея</a></li>
                <li><a href="news.php">Новости</a></li>
            </ul>
        </div>
        <div>
            <h3 class="footer-title">Контакты</h3>
            <ul class="footer-list">
                <li><a href="tel:+73470000000">+7 (347) 000-00-00</a></li>
                <li><a href="mailto:info@umniki-ufa.ru">info@umniki-ufa.ru</a></li>
                <li>г. Уфа, ул. Примерная, 10</li>
            </ul>
        </div>
        <div>
            <h3 class="footer-title">График работы</h3>
            <ul class="footer-list">
                <li>Пн–Пт: 09:00–20:00</li>
                <li>Сб: 10:00–18:00</li>
                <li>Вс: по записи</li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© 2026 Умники и Умницы</span>
        <span>Развивающие занятия, логопед, психолог, подготовка к школе</span>
    </div>
</footer>
</body>
</html>
