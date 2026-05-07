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
  'age_from' => '',
  'age_to' => '',
  'short_description' => '',
  'full_description' => '',
  'is_active' => 1,
  'sort_order' => 0,
];

if ($id > 0) {
  $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = :id LIMIT 1");
  $stmt->execute([':id' => $id]);
  $row = $stmt->fetch();
  if ($row) {
    $data = [
      'title' => (string)$row['title'],
      'slug' => (string)$row['slug'],
      'age_from' => (string)($row['age_from'] ?? ''),
      'age_to' => (string)($row['age_to'] ?? ''),
      'short_description' => (string)($row['short_description'] ?? ''),
      'full_description' => (string)($row['full_description'] ?? ''),
      'is_active' => (int)($row['is_active'] ?? 1),
      'sort_order' => (int)($row['sort_order'] ?? 0),
    ];
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['title'] = trim((string)($_POST['title'] ?? ''));
  $data['slug'] = trim((string)($_POST['slug'] ?? ''));
  $data['age_from'] = trim((string)($_POST['age_from'] ?? ''));
  $data['age_to'] = trim((string)($_POST['age_to'] ?? ''));
  $data['short_description'] = trim((string)($_POST['short_description'] ?? ''));
  $data['full_description'] = trim((string)($_POST['full_description'] ?? ''));
  $data['is_active'] = isset($_POST['is_active']) ? 1 : 0;
  $data['sort_order'] = (int)($_POST['sort_order'] ?? 0);

  if ($data['title'] === '') {
    $errors[] = 'Введите название программы.';
  }

  if ($data['slug'] === '') {
    $data['slug'] = slugify($data['title']);
  } else {
    $data['slug'] = slugify($data['slug']);
  }

  $ageFrom = ($data['age_from'] === '') ? null : (int)$data['age_from'];
  $ageTo   = ($data['age_to'] === '') ? null : (int)$data['age_to'];

  if (!$errors) {
    try {
      if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE programs
          SET title=:title, slug=:slug, age_from=:age_from, age_to=:age_to,
              short_description=:short_description, full_description=:full_description,
              is_active=:is_active, sort_order=:sort_order
          WHERE id=:id");
        $stmt->execute([
          ':title'=>$data['title'],
          ':slug'=>$data['slug'],
          ':age_from'=>$ageFrom,
          ':age_to'=>$ageTo,
          ':short_description'=>$data['short_description'],
          ':full_description'=>$data['full_description'],
          ':is_active'=>$data['is_active'],
          ':sort_order'=>$data['sort_order'],
          ':id'=>$id
        ]);
      } else {
        $stmt = $pdo->prepare("INSERT INTO programs
          (title, slug, age_from, age_to, short_description, full_description, is_active, sort_order)
          VALUES (:title,:slug,:age_from,:age_to,:short_description,:full_description,:is_active,:sort_order)");
        $stmt->execute([
          ':title'=>$data['title'],
          ':slug'=>$data['slug'],
          ':age_from'=>$ageFrom,
          ':age_to'=>$ageTo,
          ':short_description'=>$data['short_description'],
          ':full_description'=>$data['full_description'],
          ':is_active'=>$data['is_active'],
          ':sort_order'=>$data['sort_order']
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
  <title>Админ · <?php echo $id>0 ? 'Редактирование программы' : 'Новая программа'; ?></title>
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
      <div class="breadcrumbs"><a href="dashboard.php">Админ-панель</a> · <a href="admin_programs.php">Программы</a> · <?php echo $id>0 ? 'Редактирование' : 'Создание'; ?></div>
      <h1 class="page-title"><?php echo $id>0 ? 'Редактирование программы' : 'Добавить программу'; ?></h1>

      <?php if ($success): ?>
        <div class="form-success">
          Сохранено ✅ <a href="admin_programs.php">Вернуться к списку</a> или <a href="program.php?slug=<?php echo urlencode($data['slug']); ?>">открыть на сайте</a>.
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
          <label>Название *</label>
          <input type="text" name="title" value="<?php echo h($data['title']); ?>" required>
        </div>

        <div class="field">
          <label>Slug (можно оставить пустым — создастся автоматически)</label>
          <input type="text" name="slug" value="<?php echo h($data['slug']); ?>" placeholder="например: english">
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
          <div class="field">
            <label>Возраст от</label>
            <input type="number" name="age_from" min="0" max="18" value="<?php echo h($data['age_from']); ?>">
          </div>
          <div class="field">
            <label>Возраст до</label>
            <input type="number" name="age_to" min="0" max="18" value="<?php echo h($data['age_to']); ?>">
          </div>
        </div>

        <div class="field">
          <label>Короткое описание (для карточки)</label>
          <textarea name="short_description"><?php echo h($data['short_description']); ?></textarea>
        </div>

        <div class="field">
          <label>Полное описание (для страницы программы)</label>
          <textarea name="full_description" style="min-height: 160px;"><?php echo h($data['full_description']); ?></textarea>
        </div>

        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
          <label style="display:flex; gap:8px; align-items:center;">
            <input type="checkbox" name="is_active" <?php echo ((int)$data['is_active']===1) ? 'checked' : ''; ?>>
            Активна (показывать на сайте)
          </label>

          <div class="field" style="margin:0; max-width: 160px;">
            <label>Сортировка</label>
            <input type="number" name="sort_order" value="<?php echo (int)$data['sort_order']; ?>">
          </div>
        </div>

        <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn btn-full" type="submit">Сохранить</button>
          <a class="btn btn-outline" href="admin_programs.php">Отмена</a>
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
