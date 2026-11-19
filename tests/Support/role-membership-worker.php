<?php
// Only the allow-listed disposable database is accepted. No app environment is loaded.
$config = json_decode(file_get_contents($argv[1]), true);
if ($config['driver'] !== 'mysql' || $config['host'] !== '127.0.0.1' || (int) $config['port'] !== 33328
    || $config['database'] !== 'mazadat_notifications_test' || ! empty($config['url']) || ! empty($config['unix_socket'])) { exit(2); }
$pdo = new PDO('mysql:host=127.0.0.1;port=33328;dbname=mazadat_notifications_test', $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->beginTransaction();
$query = $pdo->prepare('SELECT id FROM users WHERE id = ? FOR UPDATE');
$query->execute([$argv[2]]);
$query = $pdo->prepare('SELECT id FROM user_access_roles WHERE user_id = ? AND role = ?');
$query->execute([$argv[2], 'seller']);
if (! $query->fetch()) {
    $insert = $pdo->prepare('INSERT INTO user_access_roles (user_id, role) VALUES (?, ?)');
    $insert->execute([$argv[2], 'seller']);
}
$pdo->commit();
