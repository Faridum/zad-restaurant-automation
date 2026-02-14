<?php
file_put_contents(
  __DIR__ . '/debug_fcm.log',
  "HIT update_status.php\n",
  FILE_APPEND
);



require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => '🚫 غير مصرح'], JSON_UNESCAPED_UNICODE);
  exit;
}

$user_id = (int)$_SESSION['user_id'];

// ✅ لازم POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed'], JSON_UNESCAPED_UNICODE);
  exit;
}

// ✅ تحقق من المدخلات
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status   = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($order_id <= 0 || $status === '') {
  http_response_code(422);
  echo json_encode([
    'status' => 'error',
    'message' => 'بيانات ناقصة',
    'debug' => [
      'order_id' => $_POST['order_id'] ?? null,
      'status'   => $_POST['status'] ?? null,
    ]
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// ✅ حالات مسموحة (طابق ENUM عندك)
$allowed = ['pending', 'accepted', 'preparing', 'ready', 'completed', 'canceled']; // أضف canceled فقط إذا موجودة في enum
if (!in_array($status, $allowed, true)) {
  http_response_code(422);
  echo json_encode([
    'status' => 'error',
    'message' => 'قيمة الحالة غير صحيحة',
    'debug' => ['status' => $status, 'allowed' => $allowed]
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // ✅ تحقق أن الطلب يخص مطاعم المالك
  $checkStmt = $pdo->prepare("
    SELECT o.id
    FROM orders o
    INNER JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.id = ? AND r.owner_id = ?
    LIMIT 1
  ");
  $checkStmt->execute([$order_id, $user_id]);

  if (!$checkStmt->fetch()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => '🚫 لا تملك صلاحية'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // ✅ تحديث الحالة
  $updateStmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
  $updateStmt->execute([$status, $order_id]);

// 🔔 استدعاء API الإشعارات (بعد تحديث الحالة)
$ch = curl_init("http://localhost/grad_project/backend/public/api/v1/orders/update_status.php");


curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POSTFIELDS => json_encode([
    'order_id' => $order_id,
    'status'   => $status,
  ]),
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    // توكن داخلي ثابت
    'Authorization: Bearer INTERNAL_SERVICE_TOKEN'
  ],
]);


curl_exec($ch);
curl_close($ch);




  // ✅ سجل تحديث (إذا جدول updates موجود)
  // لو ما عندك جدول updates حالياً، علّق السطرين الجايين
  //$logStmt = $pdo->prepare("INSERT INTO updates (type, order_id) VALUES (?, ?)");
 // $logStmt->execute(['order_status_update', $order_id]);

  echo json_encode(['status' => 'success', 'message' => '✅ تم تحديث حالة الطلب'], JSON_UNESCAPED_UNICODE);
  exit;
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
  exit;
}
