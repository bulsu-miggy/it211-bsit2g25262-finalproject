<?php
require_once __DIR__ . '/dbconfig.php';
require_once __DIR__ . '/../../config.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

$defaultAvatar = $url . '/assets2/his48_c01.png';

if (!isset($_SESSION['username'])) {
    echo json_encode([
        'success' => false,
        'avatar_url' => $defaultAvatar,
        'username' => null,
        'first_name' => null,
        'last_name' => null,
        'email' => null,
    ]);
    exit();
}

$stmt = $conn->prepare('SELECT l.username, l.first_name, l.last_name, l.email, l.img_url, p.profile_image FROM login l LEFT JOIN user_profiles p ON p.user_id = l.id WHERE l.username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$avatar = trim((string)($row['profile_image'] ?? ''));
if ($avatar === '') {
    $avatar = trim((string)($row['img_url'] ?? ''));
}
if ($avatar === '') {
    $avatar = $defaultAvatar;
}

echo json_encode([
    'success' => true,
    'avatar_url' => $avatar,
    'username' => $row['username'] ?? null,
    'first_name' => $row['first_name'] ?? null,
    'last_name' => $row['last_name'] ?? null,
    'email' => $row['email'] ?? null,
]);
exit();
