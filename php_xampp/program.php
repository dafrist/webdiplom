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
$slug = (string)($_GET['slug'] ?? '');
$program = null;

if ($slug !== '') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $program = $stmt->fetch();
    } catch (PDOException $e) {
        $program = null;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo $program ? htmlspecialchars($program['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : 'Программа'; ?> — «Умники и Умницы»</title>
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
            <div class="breadcrumbs">
                <a href="index.html">Главная</a> · <a href="programs.php">Программы</a>
                <?php if ($program): ?> · <?php echo htmlspecialchars($program['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?><?php endif; ?>
            </div>

            <?php if (!$program): ?>
                <h1 class="page-title">Программа не найдена</h1>
                <p class="section-lead">Возможно, ссылка устарела или программа была изменена.</p>
            <?php else: ?>
                <h1 class="page-title"><span class="ui-icon program-title-icon <?php echo htmlspecialchars(program_icon($program['slug']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" aria-hidden="true"></span><?php echo htmlspecialchars($program['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
                <p class="section-lead">
                    Возраст:
                    <?php if ($program['age_from'] || $program['age_to']): ?>
                        <?php echo htmlspecialchars((string)$program['age_from'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>–<?php echo htmlspecialchars((string)$program['age_to'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> лет
                    <?php else: ?>
                        по согласованию
                    <?php endif; ?>
                </p>

                <?php if (!empty($program['full_description'])): ?>
                    <p><?php echo nl2br(htmlspecialchars($program['full_description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')); ?></p>
                <?php endif; ?>

                <div style="margin-top:18px;">
                    <a href="parents.php" class="btn btn-full">Оставить заявку на программу</a>
                    <a href="programs.php" class="btn btn-outline" style="margin-left:8px;">Назад к списку программ</a>
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
