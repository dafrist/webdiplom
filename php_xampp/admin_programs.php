<?php
declare(strict_types=1);
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_admin();

$q = trim((string)($_GET['q'] ?? ''));
try {
    if ($q !== '') {
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE title LIKE :q OR slug LIKE :q ORDER BY sort_order, id");
        $stmt->execute([':q' => '%' . $q . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM programs ORDER BY sort_order, id");
    }
    $rows = $stmt->fetchAll() ?: [];
} catch (PDOException $e) {
    $rows = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админ · Программы</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/style.css">
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
      <div class="breadcrumbs"><a href="dashboard.php">Админ-панель</a> · Программы</div>
      <h1 class="page-title">Программы</h1>
      <p class="section-lead">Создавайте, редактируйте и отключайте программы.</p>

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin:12px 0;">
        <a href="admin_program_edit.php" class="btn btn-full">+ Добавить программу</a>
        <a href="dashboard.php" class="btn btn-outline">Назад в админку</a>
      </div>

      <form method="get" style="max-width:520px; margin:10px 0;">
        <div class="field">
          <label>Поиск</label>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Название или slug">
        </div>
        <button class="btn btn-outline" type="submit">Искать</button>
      </form>

      <div class="table-wrapper" style="margin-top:12px;">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Название</th>
              <th>Slug</th>
              <th>Возраст</th>
              <th>Активна</th>
              <th>Сорт.</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="7">Пока нет программ.</td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?php echo (int)$r['id']; ?></td>
                  <td><?php echo h((string)$r['title']); ?></td>
                  <td><?php echo h((string)$r['slug']); ?></td>
                  <td>
                    <?php echo h((string)($r['age_from'] ?? '')); ?>–<?php echo h((string)($r['age_to'] ?? '')); ?>
                  </td>
                  <td><?php echo ((int)$r['is_active'] === 1) ? 'Да' : 'Нет'; ?></td>
                  <td><?php echo (int)($r['sort_order'] ?? 0); ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline" href="admin_program_edit.php?id=<?php echo (int)$r['id']; ?>">Редакт.</a>
                    <a class="btn btn-sm btn-outline" href="admin_program_delete.php?id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить программу?');">Удалить</a>
                    <a class="btn btn-sm btn-outline" href="program.php?slug=<?php echo urlencode((string)$r['slug']); ?>">Открыть</a>
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
