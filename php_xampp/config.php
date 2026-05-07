<?php
$DB_HOST = 'localhost';
$DB_NAME = 'umniki';
$DB_USER = 'root';
$DB_PASS = '';

// Делаем "долгую" сессию: пользователь не вылетает после закрытия браузера
// 30 дней
$cookieLifetime = 60 * 60 * 24 * 30;

// ВАЖНО: параметры cookie нужно задавать ДО session_start
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => $cookieLifetime,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // включай на HTTPS
    ]);
    ini_set('session.gc_maxlifetime', (string)$cookieLifetime);
    session_start();
}

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<h2>Ошибка подключения к базе данных</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    exit;
}

function is_admin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_admin(): void {
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function slugify(string $s): string {
    $s = trim(mb_strtolower($s, 'UTF-8'));
    // Транслитерация минимальная (можно улучшить)
    $map = [
        'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh','з'=>'z','и'=>'i','й'=>'y',
        'к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f',
        'х'=>'h','ц'=>'ts','ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya'
    ];
    $s = strtr($s, $map);
    $s = preg_replace('/[^a-z0-9]+/u', '-', $s);
    $s = trim($s, '-');
    if ($s === '') $s = 'item';
    return $s;
}
