<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$errors = [];
$success = false;

$child_name   = '';
$child_age    = '';
$program_name = '';
$parent_name  = '';
$parent_phone = '';
$parent_email = '';
$comment      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_name   = trim($_POST['child_name'] ?? '');
    $child_age    = trim($_POST['child_age'] ?? '');
    $program_name = trim($_POST['program_name'] ?? '');
    $parent_name  = trim($_POST['parent_name'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $comment      = trim($_POST['comment'] ?? '');

    if ($child_name === '' || $parent_name === '' || $parent_phone === '') {
        $errors[] = 'Пожалуйста, заполните обязательные поля: имя ребёнка, имя родителя и телефон.';
    } else {
        try {
            $ageVal = $child_age !== '' ? (int)$child_age : null;
            $stmt = $pdo->prepare('INSERT INTO applications (child_name, child_age, program_name, parent_name, parent_phone, parent_email, comment, is_new) VALUES (:child_name, :child_age, :program_name, :parent_name, :parent_phone, :parent_email, :comment, 1)');
            $stmt->execute([
                ':child_name'   => $child_name,
                ':child_age'    => $ageVal,
                ':program_name' => $program_name,
                ':parent_name'  => $parent_name,
                ':parent_phone' => $parent_phone,
                ':parent_email' => $parent_email,
                ':comment'      => $comment,
            ]);
            $success = true;
            $child_name = $child_age = $program_name = $parent_name = $parent_phone = $parent_email = $comment = '';
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
    <title>Родителям — «Умники и Умницы»</title>
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
            <a href="parents.php" class="is-current">Родителям</a>
            <a href="contacts.html">Контакты</a>
            <a href="news.php">Новости</a>
            <a href="login.php">Личный кабинет</a>
        </nav>
        <a href="parents.php" class="btn btn-sm btn-application">Онлайн-заявка</a>
    </div>
</header>

<main>
    <section class="section section-alt">
        <div class="container">
            <div class="breadcrumbs"><a href="index.html">Главная</a> · Родителям</div>
            <h1 class="page-title">Онлайн-заявка</h1>
            <p class="section-lead">
                Заполните форму, и администратор центра свяжется с вами, чтобы подобрать программу и время занятий.
            </p>

            <?php if ($success): ?>
                <div class="form-success">
                    Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.
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
                    <label>Имя ребёнка *</label>
                    <input type="text" name="child_name" required
                           value="<?php echo htmlspecialchars($child_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Возраст ребёнка</label>
                    <input type="number" name="child_age" min="1" max="12"
                           value="<?php echo htmlspecialchars($child_age, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Желаемая программа</label>
                    <input type="text" name="program_name" placeholder="Мини-группа, развитие речи, подготовка к школе..."
                           value="<?php echo htmlspecialchars($program_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Имя родителя *</label>
                    <input type="text" name="parent_name" required
                           value="<?php echo htmlspecialchars($parent_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Телефон *</label>
                    <input type="text" name="parent_phone" required
                           value="<?php echo htmlspecialchars($parent_phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="parent_email"
                           value="<?php echo htmlspecialchars($parent_email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Комментарий</label>
                    <textarea name="comment" placeholder="Уточните удобное время, особенности ребёнка и т.д."><?php echo htmlspecialchars($comment, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                </div>
                <button type="submit" class="btn btn-full">Отправить заявку</button>
            </form>
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
