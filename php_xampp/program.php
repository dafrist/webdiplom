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
            <div class="breadcrumbs">
                <a href="index.html">Главная</a> · <a href="programs.php">Программы</a>
                <?php if ($program): ?> · <?php echo htmlspecialchars($program['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?><?php endif; ?>
            </div>

            <?php if (!$program): ?>
                <h1 class="page-title">Программа не найдена</h1>
                <p class="section-lead">Возможно, ссылка устарела или программа была изменена.</p>
            <?php else: ?>
                <h1 class="page-title"><?php echo program_emoji($program['slug']) . ' ' . htmlspecialchars($program['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
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
</body>
</html>
