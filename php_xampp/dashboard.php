<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$name = $_SESSION['user_name'] ?? null;
$role = $_SESSION['user_role'] ?? null;

if ($role !== 'admin') {
    header('Location: login.php');
    exit;
}

$appCount  = 0;
$userCount = 0;
$newsCount = 0;
$applications = [];
$parents = [];
$newsList = [];

try {
    $row = $pdo->query('SELECT COUNT(*) AS c FROM applications')->fetch();
    $appCount = $row && isset($row['c']) ? (int)$row['c'] : 0;
} catch (PDOException $e) {}

try {
    $row = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'parent'")->fetch();
    $userCount = $row && isset($row['c']) ? (int)$row['c'] : 0;
} catch (PDOException $e) {}

try {
    $row = $pdo->query('SELECT COUNT(*) AS c FROM news WHERE is_published = 1')->fetch();
    $newsCount = $row && isset($row['c']) ? (int)$row['c'] : 0;
} catch (PDOException $e) {}

try {
    $stmt = $pdo->query('SELECT * FROM applications ORDER BY created_at DESC LIMIT 20');
    $applications = $stmt->fetchAll() ?: [];
} catch (PDOException $e) {}

try {
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'parent' ORDER BY created_at DESC LIMIT 20");
    $parents = $stmt->fetchAll() ?: [];
} catch (PDOException $e) {}

try {
    $stmt = $pdo->query('SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC LIMIT 10');
    $newsList = $stmt->fetchAll() ?: [];
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — «Умники и Умницы»</title>
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
            <div class="breadcrumbs">
                <a href="index.html">Главная</a> · Админ-панель
            </div>
            <h1 class="page-title">Админ-панель</h1>
            <p class="section-lead">
                Здесь администратор видит заявки с сайта, зарегистрированных родителей и новости.
            </p>

            <div class="cards-row">
                <article class="card">
                    <h3>Заявки</h3>
                    <p class="section-lead">
                        Всего: <strong><?php echo (int)$appCount; ?></strong>
                    </p>
                </article>
                <article class="card">
                    <h3>Родители</h3>
                    <p class="section-leад">
                        Аккаунтов: <strong><?php echo (int)$userCount; ?></strong>
                    </p>
                </article>
                <article class="card">
                    <h3>Новости</h3>
                    <p class="section-lead">
                        Опубликовано: <strong><?php echo (int)$newsCount; ?></strong>
                    </p>
                </article>
            
            <div class="cards-row" style="margin-top:14px;">
                <article class="card">
                    <h3>Управление программами</h3>
                    <p class="section-lead">Создание, редактирование и удаление программ.</p>
                    <a href="admin_programs.php" class="btn btn-full">Открыть</a>
                </article>
                <article class="card">
                    <h3>Управление новостями</h3>
                    <p class="section-lead">Добавляйте новости и публикуйте их на сайте.</p>
                    <a href="admin_news.php" class="btn btn-full">Открыть</a>
                </article>
            </div>
</div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="page-title">Последние заявки</h2>
            <p class="section-lead">Форма «Родителям» → попадает сюда.</p>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Создана</th>
                            <th>Ребёнок</th>
                            <th>Возраст</th>
                            <th>Программа</th>
                            <th>Родитель</th>
                            <th>Телефон</th>
                            <th>Комментарий</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$applications): ?>
                        <tr>
                            <td colspan="8">Пока нет заявок.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $row): ?>
                            <tr>
                                <td><?php echo (int)$row['id']; ?></td>
                                <td>
                                    <?php
                                    $dt = $row['created_at'] ?? '';
                                    echo $dt ? htmlspecialchars(date('d.m.Y H:i', strtotime($dt)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['child_name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars((string)($row['child_age'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['program_name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['parent_name'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['parent_phone'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($row['comment'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="section section-alt">
        <div class="container">
            <h2 class="page-title">Новости центра</h2>
            <p class="section-lead">Новости, которые уже опубликованы на сайте.</p>

            <?php if (!$newsList): ?>
                <p>Пока нет новостей.</p>
            <?php else: ?>
                <div class="cards-row">
                    <?php foreach ($newsList as $n): ?>
                        <article class="card">
                            <h3><?php echo htmlspecialchars($n['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h3>
                            <p class="news-meta">
                                <?php
                                $dt = $n['created_at'] ?? '';
                                echo $dt ? htmlspecialchars(date('d.m.Y', strtotime($dt)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                                ?>
                            </p>
                            <?php if (!empty($n['teaser'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($n['teaser'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <h2 class="page-title">Родители (аккаунты)</h2>
            <p class="section-lead">Пользователи, зарегистрированные через сайт.</p>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>E-mail</th>
                            <th>Роль</th>
                            <th>Создан</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$parents): ?>
                        <tr>
                            <td colspan="5">Пока нет зарегистрированных родителей.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($parents as $u): ?>
                            <tr>
                                <td><?php echo (int)$u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($u['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($u['role'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></td>
                                <td>
                                    <?php
                                    $dt = $u['created_at'] ?? '';
                                    echo $dt ? htmlspecialchars(date('d.m.Y H:i', strtotime($dt)), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
