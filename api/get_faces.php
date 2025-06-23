<?php
require_once 'db.php';
$stmt = $pdo->query("SELECT id, name, descriptor FROM users");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>