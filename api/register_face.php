<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'];
$email = $data['email'];
$descriptor = json_encode($data['descriptor']);

$stmt = $pdo->prepare("INSERT INTO users (name, email, descriptor) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $descriptor]);

echo "User registered!";
