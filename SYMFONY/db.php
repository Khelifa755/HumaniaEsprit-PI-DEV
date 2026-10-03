<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=humania_full', 'root', '');
$rows = $pdo->query('SELECT id, titre, dateHeureDebut, dateHeureFin, emailOrganisateur FROM reunion LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) print_r($r);
echo 'Total rows: ' . $pdo->query('SELECT COUNT(*) FROM reunion')->fetchColumn() . "\n";
