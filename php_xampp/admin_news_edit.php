<?php
declare(strict_types=1);
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];
$success = false;

$data = [
  'title' => '',
  'slug' => '',
  'teaser' => '',
  'body' => '',
  'is_published' => 1,
];

if ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM news WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch();
  if ($row) {
    $data = [
      'title' => (string)$row['title'],
      'slug' => (string)$row['slug'],
      'teaser' => (string)($row['teaser'] ?? ''),
      'body' => (string)($row['body'] ?? ''),
      'is_published' => (int)($row['is_published'] ?? 1),
    ];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['title'] = trim((string)($_POST['title'] ?? ''));
  $data['slug'] = trim((string)($_POST['slug'] ?? ''));
  $data['teaser'] = trim((string)($_POST['teaser'] ?? ''));
  $data['body'] = trim((string)($_POST['body'] ?? ''));
  $data['is_published'] = isset($_POST['is_published']) ? 1 : 0;

  if ($data['title'] === '') {
    $errors[] = 'Введите заголовок.';
  }

  if ($data['slug'] === '') {
    $data['slug'] = slugify($data['title']);
  } else {
    $data['slug'] = slugify($data['slug']);
  }

  if (!$errors) {
    try {
      if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE news
          SET title=:title, slug=:slug, teaser=:teaser, body=:body, is_published=:is_published
          WHERE id=:id");
        $stmt->execute([
          ':title'=>$data['title'],
          ':slug'=>$data['slug'],
          ':teaser'=>$data['teaser'],
          ':body'=>$data['body'],
          ':is_published'=>$data['is_published'],
          ':id'=>$id
        ]);
      } else {
        $stmt = $pdo->prepare("INSERT INTO news (title, slug, teaser, body, is_published)
          VALUES (:title,:slug,:teaser,:body,:is_published)");
        $stmt->execute([
          ':title'=>$data['title'],
          ':slug'=>$data['slug'],
          ':teaser'=>$data['teaser'],
          ':body'=>$data['body'],
          ':is_published'=>$data['is_published']
        ]);
        $id = (int)$pdo->lastInsertId();
      }
      $success = true;
    } catch (PDOException $e) {
      $errors[] = 'Ошибка БД: ' . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Админ · <?php echo $id>0 ? 'Редактирование новости' : 'Новая новость'; ?></title>
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
      <div class="breadcrumbs"><a href="dashboard.php">Админ-панель</a> · <a href="admin_news.php">Новости</a> · <?php echo $id>0 ? 'Редактирование' : 'Создание'; ?></div>
      <h1 class="page-title"><?php echo $id>0 ? 'Редактирование новости' : 'Добавить новость'; ?></h1>

      <?php if ($success): ?>
        <div class="form-success">
          Сохранено <a href="admin_news.php">Вернуться к списку</a> или <a href="news.php">открыть новости</a>.
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="form-errors">
          <?php foreach ($errors as $err): ?>
            <div><?php echo h($err); ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="big-form">
        <div class="field">
          <label>Заголовок *</label>
          <input type="text" name="title" value="<?php echo h($data['title']); ?>" required>
        </div>

        <div class="field">
          <label>Slug (можно пустым)</label>
          <input type="text" name="slug" value="<?php echo h($data['slug']); ?>">
        </div>

        <div class="field">
          <label>Короткий анонс</label>
          <textarea name="teaser"><?php echo h($data['teaser']); ?></textarea>
        </div>

        <div class="field">
          <label>Текст новости</label>
          <textarea name="body" style="min-height: 180px;"><?php echo h($data['body']); ?></textarea>
        </div>

        <label style="display:flex; gap:8px; align-items:center; margin-bottom: 10px;">
          <input type="checkbox" name="is_published" <?php echo ((int)$data['is_published']===1) ? 'checked' : ''; ?>>
          Опубликовать
        </label>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn btn-full" type="submit">Сохранить</button>
          <a class="btn btn-outline" href="admin_news.php">Отмена</a>
        </div>
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
