<?php
declare(strict_types=1);
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_admin();

$q = trim((string)($_GET['q'] ?? ''));
try {
    if ($q !== '') {
        $stmt = $pdo->prepare("SELECT * FROM news WHERE title LIKE :q OR slug LIKE :q ORDER BY created_at DESC, id DESC");
        $stmt->execute([':q' => '%' . $q . '%']);
    } else {
        $stmt = $pdo->query("SELECT * FROM news ORDER BY created_at DESC, id DESC");
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
  <title>Админ · Новости</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/style.css">
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
      <div class="breadcrumbs"><a href="dashboard.php">Админ-панель</a> · Новости</div>
      <h1 class="page-title">Новости</h1>
      <p class="section-lead">Добавляйте новости и публикуйте их на сайте.</p>

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin:12px 0;">
        <a href="admin_news_edit.php" class="btn btn-full">+ Добавить новость</a>
        <a href="dashboard.php" class="btn btn-outline">Назад в админку</a>
      </div>

      <form method="get" style="max-width:520px; margin:10px 0;">
        <div class="field">
          <label>Поиск</label>
          <input type="text" name="q" value="<?php echo h($q); ?>" placeholder="Заголовок или slug">
        </div>
        <button class="btn btn-outline" type="submit">Искать</button>
      </form>

      <div class="table-wrapper" style="margin-top:12px;">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Дата</th>
              <th>Заголовок</th>
              <th>Slug</th>
              <th>Опублик.</th>
              <th>Действия</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="6">Пока нет новостей.</td></tr>
            <?php else: ?>
              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?php echo (int)$r['id']; ?></td>
                  <td><?php echo h(date('d.m.Y', strtotime((string)$r['created_at']))); ?></td>
                  <td><?php echo h((string)$r['title']); ?></td>
                  <td><?php echo h((string)$r['slug']); ?></td>
                  <td><?php echo ((int)$r['is_published'] === 1) ? 'Да' : 'Нет'; ?></td>
                  <td>
                    <a class="btn btn-sm btn-outline" href="admin_news_edit.php?id=<?php echo (int)$r['id']; ?>">Редакт.</a>
                    <a class="btn btn-sm btn-outline" href="admin_news_delete.php?id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить новость?');">Удалить</a>
                    <a class="btn btn-sm btn-outline" href="news.php">Открыть</a>
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
</body>
</html>
