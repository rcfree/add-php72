<?php
/**
 * Seed a preview admin user in the local add_user database.
 *
 * Usage (on the VM):
 *   PREVIEW_ADMIN_PASSWORD='PreviewAdmin123' php tools/seed-preview-admin.php
 *
 * This is for the local preview database only. It uses ion_auth's sha1
 * store_salt=false format, matching application/config/ion_auth.php.
 */

$host = getenv('PREVIEW_DB_HOST') ?: '127.0.0.1';
$db   = getenv('PREVIEW_DB_NAME') ?: 'add_user';
$user = getenv('PREVIEW_DB_USER') ?: 'add';
$pass = getenv('PREVIEW_DB_PASS') ?: 'add';

$username = getenv('PREVIEW_ADMIN_USER') ?: 'admin';
$password = getenv('PREVIEW_ADMIN_PASSWORD') ?: 'PreviewAdmin123';
$email    = getenv('PREVIEW_ADMIN_EMAIL') ?: 'admin@local.test';

$salt = substr(md5(uniqid('', true)), 0, 10);
$hash = $salt . substr(sha1($salt . $password), 0, -10);

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
));

$pdo->beginTransaction();

$groupIds = array();
foreach (array('admin', 'members') as $groupName) {
    $stmt = $pdo->prepare('SELECT id FROM groups WHERE name = ? LIMIT 1');
    $stmt->execute(array($groupName));
    $id = $stmt->fetchColumn();
    if (!$id) {
        $stmt = $pdo->prepare('INSERT INTO groups (name, description) VALUES (?, ?)');
        $stmt->execute(array($groupName, ucfirst($groupName)));
        $id = $pdo->lastInsertId();
    }
    $groupIds[$groupName] = (int) $id;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute(array($username));
$userId = $stmt->fetchColumn();
$now = time();

if ($userId) {
    $stmt = $pdo->prepare(
        'UPDATE users SET password = ?, active = 1, last_changed = ? WHERE id = ?'
    );
    $stmt->execute(array($hash, $now, $userId));
} else {
    $stmt = $pdo->prepare(
        'INSERT INTO users
            (ip_address, username, password, email, created_on, last_changed, active)
         VALUES (INET6_ATON(?), ?, ?, ?, ?, ?, 1)'
    );
    $stmt->execute(array('127.0.0.1', $username, $hash, $email, $now, $now));
    $userId = $pdo->lastInsertId();
}

$stmt = $pdo->prepare(
    'INSERT IGNORE INTO users_groups (user_id, group_id) VALUES (?, ?)'
);
$stmt->execute(array($userId, $groupIds['admin']));

$pdo->commit();

echo "Seeded user '$username' (id $userId) in group admin (id {$groupIds['admin']}).\n";
