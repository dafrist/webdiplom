<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';


function program_emoji(string $slug): string {
    $map = [
        'razvivaika-4plus' => '🧩',
        'doshkolyata-5plus' => '🎒',
        'reading' => '📖',
        'calligraphy' => '✍️',
        'speech-therapist' => '🗣️',
        'english' => '🇬🇧',
        'vocal' => '🎤',
        'acting' => '🎭',
        'drawing' => '🎨',
        'dance' => '💃',
        'ofp-wrestling' => '🤼',
        'school-prep' => '📚',
    ];
    return $map[$slug] ?? '✨';
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
            <div class="logo-circle">у</div>
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
        <a href="parents.php" class="btn btn-sm btn-outline">Онлайн-заявка</a>
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
                                    <?php echo program_emoji($p['slug']) . ' ' . htmlspecialchars($p['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
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
</body>
</html>
