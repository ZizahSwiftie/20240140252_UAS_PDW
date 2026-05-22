<?php
require_once __DIR__ . '/../includes/db.php';

if (PHP_SAPI !== 'cli') {
    die('Run this seed script from the command line.');
}

if (empty($argv[1])) {
    die("Usage: php database/seed_admin.php \"your_admin_password\"\n");
}

$admin_name = 'System Admin';
$admin_email = 'admin@example.com';
$admin_plain_password = $argv[1];
$admin_password = password_hash($admin_plain_password, PASSWORD_DEFAULT);
$admin_role = 'admin';

$sql = "INSERT INTO users (name, email, password, role)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            name = VALUES(name),
            password = VALUES(password),
            role = VALUES(role)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 'ssss', $admin_name, $admin_email, $admin_password, $admin_role);

if (mysqli_stmt_execute($stmt)) {
    echo 'Admin account has been created or updated successfully.';
} else {
    echo 'Admin seed failed: ' . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
