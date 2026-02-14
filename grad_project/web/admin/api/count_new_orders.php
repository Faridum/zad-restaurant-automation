<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';


header('Content-Type: application/json; charset=utf-8');


// السماح فقط للمالك
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
  echo json_encode(['status' => 'error', 'message' => 'غير مصرح']);
  exit;
}


$user_id = (int)$_SESSION['user_id'];


$stmt = $pdo->prepare("
  SELECT COUNT(*)
  FROM orders o
  INNER JOIN restaurants r ON o.restaurant_id = r.id
  WHERE r.owner_id = ?
    AND o.status = 'pending'
");
$stmt->execute([$user_id]);


$count = (int)$stmt->fetchColumn();


echo json_encode([
  'status' => 'success',
  'count'  => $count
], JSON_UNESCAPED_UNICODE);


