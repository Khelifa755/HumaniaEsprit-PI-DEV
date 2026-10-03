<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=humania_full', 'root', '');
$result = $pdo->query('SELECT id, firstName, lastName, role FROM users WHERE id IN (1, 3, 5) ORDER BY id');
$users = $result->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo sprintf("User ID %d: %s %s (Role: %s)\n", $user['id'], $user['firstName'], $user['lastName'], $user['role']);
}
