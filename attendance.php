<?php
require_once 'api/db.php';

// Handle date filter
$filterDate = $_GET['date'] ?? date('Y-m-d');

// Get all users
$usersStmt = $pdo->query("SELECT id, name FROM users ORDER BY name ASC");
$users = $usersStmt->fetchAll();

// Get attendance logs
$attStmt = $pdo->prepare("SELECT * FROM attendance WHERE date = ?");
$attStmt->execute([$filterDate]);
$attendanceLogs = $attStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

function formatTime($time) {
    return $time ? date("h:i A", strtotime($time)) : '-';
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Attendance Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
    th { background-color: #f4f4f4; }
    input[type="date"] { padding: 5px; }
  </style>
</head>
<body>
  <h2>Attendance Dashboard</h2>

  <form method="GET">
    <label for="date">Filter by date:</label>
    <input type="date" name="date" value="<?= $filterDate ?>">
    <button type="submit">View</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>User</th>
        <th>Time In</th>
        <th>Time Out</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
        <?php
          $log = $attendanceLogs[$user['id']] ?? null;
          $timeIn = $log['time_in'] ?? null;
          $timeOut = $log['time_out'] ?? null;
        ?>
        <tr>
          <td><?= htmlspecialchars($user['name']) ?></td>
          <td><?= formatTime($timeIn) ?></td>
          <td><?= formatTime($timeOut) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
