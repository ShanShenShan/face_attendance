<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'];

$date = date('Y-m-d');
$time = date('H:i:s');

// Check if already logged today
$stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$user_id, $date]);
$row = $stmt->fetch();

if (!$row) {
  $stmt = $pdo->prepare("INSERT INTO attendance (user_id, date, time_in) VALUES (?, ?, ?)");
  $stmt->execute([$user_id, $date, $time]);
  echo "Time in recorded!";
} else if (!$row['time_out']) {
  $stmt = $pdo->prepare("UPDATE attendance SET time_out = ? WHERE id = ?");
  $stmt->execute([$time, $row['id']]);
  echo "Time out recorded!";
} else {
  echo "Already logged in and out today!";
}
