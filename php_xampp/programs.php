<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';


function program_icon(string $slug): string {
    $map = [
        'razvivaika-4plus' => 'ui-icon-chart',
        'doshkolyata-5plus' => 'ui-icon-users',
        'reading' => 'ui-icon-book',
        'calligraphy' => 'ui-icon-pen',
        'speech-therapist' => 'ui-icon-message',
        'english' => 'ui-icon-book',
        'vocal' => 'ui-icon-music',
        'acting' => 'ui-icon-star',
        'drawing' => 'ui-icon-palette',
        'dance' => 'ui-icon-activity',
        'ofp-wrestling' => 'ui-icon-activity',
        'school-prep' => 'ui-icon-star',
    ];
    return $map[$slug] ?? 'ui-icon-book';
}
try {
    $stmt = $pdo->query("SELECT * FROM programs WHERE is_active = 1 ORDER BY sort_order, id");
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    $programs = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Программы — «Умники и Умницы»</title>
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
            <a href="programs.php" class="is-current">Программы</a>
            <a href="gallery.html">Фотогалерея</a>
            <a href="parents.php">Родителям</a>
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
            <div class="breadcrumbs"><a href="index.html">Главная</a> · Программы</div>
            <h1 class="page-title">Программы центра</h1>
            <p class="section-lead">
                Каждая программа адаптирована под возраст и потребности детей.
                Нажмите на программу, чтобы посмотреть подробное описание.
            </p>

            <?php if (!$programs): ?>
                <p>Пока нет добавленных программ.</p>
            <?php else: ?>
                <div class="cards-row">
                    <?php foreach ($programs as $p): ?>
                        <article class="program-card">
                            <h3>
                                <a href="program.php?slug=<?php echo urlencode($p['slug']); ?>" style="text-decoration:none;color:inherit;">
                                    <span class="ui-icon program-title-icon <?php echo htmlspecialchars(program_icon($p['slug']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" aria-hidden="true"></span><?php echo htmlspecialchars($p['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
                                </a>
                            </h3>
                            <p class="section-lead" style="margin-bottom:6px;">
                                Возраст:
                                <?php if ($p['age_from'] || $p['age_to']): ?>
                                    <?php echo htmlspecialchars((string)$p['age_from'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>–<?php echo htmlspecialchars((string)$p['age_to'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> лет
                                <?php else: ?>
                                    по согласованию
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($p['short_description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($p['short_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')); ?></p>
                            <?php endif; ?>
                            <a href="program.php?slug=<?php echo urlencode($p['slug']); ?>" class="btn btn-sm btn-outline">
                                Подробнее о программе
                            </a>
                            <a href="parents.php" class="btn btn-sm btn-full" style="margin-left:8px;">
                                Связаться
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
