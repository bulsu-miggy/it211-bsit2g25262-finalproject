<?php
require_once __DIR__ . '/dbconfig.php';
require_once __DIR__ . '/../../config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: $url/loginpage.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $url/profilepage.php");
    exit();
}

$stmt = $conn->prepare('SELECT l.id, l.img_url, p.profile_image FROM login l LEFT JOIN user_profiles p ON p.user_id = l.id WHERE l.username = :username LIMIT 1');
$stmt->execute([':username' => $_SESSION['username']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: $url/loginpage.php");
    exit();
}

$userId = (int)$user['id'];
$existingLoginAvatar = trim((string)($user['img_url'] ?? ''));
$existingProfileAvatar = trim((string)($user['profile_image'] ?? ''));
$username = trim((string)($_POST['username'] ?? ''));
$fullName = trim((string)($_POST['fullname'] ?? ''));
$address = trim((string)($_POST['address'] ?? ''));
$postalCode = trim((string)($_POST['postal_code'] ?? ''));
$phone = trim((string)($_POST['phone'] ?? ''));

if ($username === '') {
    $username = $_SESSION['username'];
}

$safeUsername = strtolower((string)preg_replace('/[^a-zA-Z0-9_-]+/', '_', $username));
$safeUsername = trim($safeUsername, '_');
if ($safeUsername === '') {
    $safeUsername = 'user_' . $userId;
}

$avatarPath = null;
$hasAvatarUpload = isset($_FILES['profile_image']) && (int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

if ($hasAvatarUpload) {
    $uploadError = (int)($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($uploadError !== UPLOAD_ERR_OK) {
        header("Location: $url/edit-profile.php?error=save_failed");
        exit();
    }

    $tmpName = (string)($_FILES['profile_image']['tmp_name'] ?? '');
    $originalName = (string)($_FILES['profile_image']['name'] ?? '');
    $size = (int)($_FILES['profile_image']['size'] ?? 0);
    $ext = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if ($tmpName === '' || !is_uploaded_file($tmpName) || !in_array($ext, $allowed, true) || $size <= 0 || $size > 2097152) {
        header("Location: $url/edit-profile.php?error=save_failed");
        exit();
    }

    $uploadDir = $images_folder . '/users/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'user_' . $safeUsername . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    foreach (glob($uploadDir . 'user_' . $safeUsername . '.*') ?: [] as $oldFile) {
        if (basename($oldFile) === $filename) {
            continue;
        }
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }

    foreach ([$existingLoginAvatar, $existingProfileAvatar] as $oldRelativePath) {
        if ($oldRelativePath === '' || strpos($oldRelativePath, 'assets2/users/') !== 0) {
            continue;
        }

        $oldAbsolutePath = $images_folder . '/users/' . basename($oldRelativePath);
        if (is_file($oldAbsolutePath) && $oldAbsolutePath !== $targetPath) {
            @unlink($oldAbsolutePath);
        }
    }

    if (is_file($targetPath)) {
        @unlink($targetPath);
    }

    if (!move_uploaded_file($tmpName, $targetPath)) {
        header("Location: $url/edit-profile.php?error=save_failed");
        exit();
    }

    $avatarPath = 'assets2/users/' . $filename;
}

$nameParts = preg_split('/\s+/', $fullName, 2, PREG_SPLIT_NO_EMPTY);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';

if ($firstName === '') {
    $stmt = $conn->prepare('SELECT first_name, last_name FROM login WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $userId]);
    $existingName = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    $firstName = (string)($existingName['first_name'] ?? 'User');
    $lastName = (string)($existingName['last_name'] ?? '');
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare('UPDATE login SET first_name = :first_name, last_name = :last_name, username = :new_username' . ($avatarPath !== null ? ', img_url = :img_url' : '') . ' WHERE id = :id');
    $params = [
        ':first_name' => $firstName,
        ':last_name' => $lastName,
        ':new_username' => $username,
        ':id' => $userId,
    ];
    if ($avatarPath !== null) {
        $params[':img_url'] = $avatarPath;
    }
    $stmt->execute($params);

    $stmt = $conn->prepare('INSERT INTO user_profiles (user_id, contact_number, profile_image, address, postal_code) VALUES (:uid, :phone, :profile_image, :address, :postal) ON DUPLICATE KEY UPDATE contact_number = VALUES(contact_number), profile_image = COALESCE(VALUES(profile_image), profile_image), address = VALUES(address), postal_code = VALUES(postal_code)');
    $stmt->execute([
        ':uid' => $userId,
        ':phone' => $phone !== '' ? $phone : null,
        ':profile_image' => $avatarPath,
        ':address' => $address !== '' ? $address : null,
        ':postal' => $postalCode !== '' ? $postalCode : null,
    ]);

    $conn->commit();
    $_SESSION['username'] = $username;
    header("Location: $url/profilepage.php?updated=1");
    exit();
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    header("Location: $url/edit-profile.php?error=save_failed");
    exit();
}
