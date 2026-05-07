<?php
declare(strict_types=1);
ini_set('display_errors','1'); ini_set('display_startup_errors','1'); error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = :id");
    $stmt->execute([':id' => $id]);
}
header('Location: admin_news.php');
exit;
