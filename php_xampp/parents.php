<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';

$errors = [];
$success = false;

$child_name   = '';
$child_age    = '';
$program_name = '';
$parent_name  = '';
$parent_phone = '';
$parent_email = '';
$comment      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_name   = trim($_POST['child_name'] ?? '');
    $child_age    = trim($_POST['child_age'] ?? '');
    $program_name = trim($_POST['program_name'] ?? '');
    $parent_name  = trim($_POST['parent_name'] ?? '');
    $parent_phone = trim($_POST['parent_phone'] ?? '');
    $parent_email = trim($_POST['parent_email'] ?? '');
    $comment      = trim($_POST['comment'] ?? '');

    if ($child_name === '' || $parent_name === '' || $parent_phone === '') {
        $errors[] = 'Пожалуйста, заполните обязательные поля: имя ребёнка, имя родителя и телефон.';
    } else {
        try {
            $ageVal = $child_age !== '' ? (int)$child_age : null;
            $stmt = $pdo->prepare('INSERT INTO applications (child_name, child_age, program_name, parent_name, parent_phone, parent_email, comment, is_new) VALUES (:child_name, :child_age, :program_name, :parent_name, :parent_phone, :parent_email, :comment, 1)');
            $stmt->execute([
                ':child_name'   => $child_name,
                ':child_age'    => $ageVal,
                ':program_name' => $program_name,
                ':parent_name'  => $parent_name,
                ':parent_phone' => $parent_phone,
                ':parent_email' => $parent_email,
                ':comment'      => $comment,
            ]);
            $success = true;
            $child_name = $child_age = $program_name = $parent_name = $parent_phone = $parent_email = $comment = '';
        } catch (PDOException $e) {
            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Родителям — «Умники и Умницы»</title>
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
            <a href="parents.php" class="is-current">Родителям</a>
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
            <div class="breadcrumbs"><a href="index.html">Главная</a> · Родителям</div>
            <h1 class="page-title">Онлайн-заявка</h1>
            <p class="section-lead">
                Заполните форму, и администратор центра свяжется с вами, чтобы подобрать программу и время занятий.
            </p>

            <?php if ($success): ?>
                <div class="form-success">
                    Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="form-errors">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="big-form">
                <div class="field">
                    <label>Имя ребёнка *</label>
                    <input type="text" name="child_name" required
                           value="<?php echo htmlspecialchars($child_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Возраст ребёнка</label>
                    <input type="number" name="child_age" min="1" max="12"
                           value="<?php echo htmlspecialchars($child_age, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Желаемая программа</label>
                    <input type="text" name="program_name" placeholder="Мини-группа, развитие речи, подготовка к школе..."
                           value="<?php echo htmlspecialchars($program_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Имя родителя *</label>
                    <input type="text" name="parent_name" required
                           value="<?php echo htmlspecialchars($parent_name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Телефон *</label>
                    <input type="text" name="parent_phone" required
                           value="<?php echo htmlspecialchars($parent_phone, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="parent_email"
                           value="<?php echo htmlspecialchars($parent_email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>">
                </div>
                <div class="field">
                    <label>Комментарий</label>
                    <textarea name="comment" placeholder="Уточните удобное время, особенности ребёнка и т.д."><?php echo htmlspecialchars($comment, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></textarea>
                </div>
                <button type="submit" class="btn btn-full">Отправить заявку</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
