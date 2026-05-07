<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$userId   = $_SESSION['user_id']   ?? null;
$userName = $_SESSION['user_name'] ?? null;
$userRole = $_SESSION['user_role'] ?? 'parent';

if (!$userId) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет — «Умники и Умницы»</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <script defer src="assets/js/main.js"></script>
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
            <div class="breadcrumbs">
                <a href="index.html">Главная</a> · Личный кабинет
            </div>

            <h1 class="page-title">
                Здравствуйте, <?php echo htmlspecialchars($userName ?? 'гость', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>!
            </h1>

            <p class="section-lead">
                Это ваш личный кабинет на сайте «Умники и Умницы».
                Здесь позже можно будет вывести ваши заявки, расписание и другую важную информацию.
            </p>

            <div class="cards-row">
                <article class="card">
                    <h3>Ваш статус</h3>
                    <p class="section-lead">
                        Роль: <strong><?php echo htmlspecialchars($userRole, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></strong>
                    </p>
                </article>
                <article class="card">
                    <h3>Связаться с центром</h3>
                    <p class="section-lead">
                        Если у вас есть вопросы по занятиям, вы можете позвонить администратору
                        или оставить заявку на странице «Родителям».
                    </p>
                    <a href="parents.php" class="btn btn-full">Оставить заявку</a>
                </article>
            </div>
        </div>
    </section>
</main>
</body>
</html>
