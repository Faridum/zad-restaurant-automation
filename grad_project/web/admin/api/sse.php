<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

if (function_exists('apache_setenv')) {
  @apache_setenv('no-gzip', '1');
}
ini_set('zlib.output_compression', 0);

set_time_limit(0);
@ob_end_flush();
ob_implicit_flush(true);

// ✅ اقرأ بيانات الجلسة مرة واحدة فقط
$user_id = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? '';

// 🔹 فلترة حسب المالك
$restaurant_id = null;
if ($role === 'owner' && $user_id > 0) {
  $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE owner_id = ? LIMIT 1");
  $stmt->execute([$user_id]);
  $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($restaurant) {
    $restaurant_id = (int)$restaurant['id'];
  }
}

/**
 * ✅ أهم سطر:
 * فك قفل الـ session حتى لا يتعطل أي AJAX ثاني (مثل get_order_details.php)
 */
session_write_close();

// 🔹 آخر طلب تم إرساله
$last_order_id = 0;

// (اختياري) لو تبغى يبدأ من آخر طلب موجود حتى ما يرسل القديم أول ما تفتح الصفحة
try {
  $initSql = "SELECT MAX(id) AS max_id FROM orders";
  $initParams = [];
  if ($restaurant_id) {
    $initSql .= " WHERE restaurant_id = ? ";
    $initParams[] = $restaurant_id;
  }
  $initStmt = $pdo->prepare($initSql);
  $initStmt->execute($initParams);
  $maxRow = $initStmt->fetch(PDO::FETCH_ASSOC);
  $last_order_id = (int)($maxRow['max_id'] ?? 0);
} catch (Exception $e) {
  // تجاهل
}

while (true) {
  try {
    if (connection_aborted()) {
      break;
    }

    // 🔹 جلب الطلبات الجديدة فقط
    $sql = "
      SELECT id, restaurant_id, status, created_at
      FROM orders
      WHERE id > ?
    ";
    $params = [$last_order_id];

    if ($restaurant_id) {
      $sql .= " AND restaurant_id = ? ";
      $params[] = $restaurant_id;
    }

    $sql .= " ORDER BY id ASC LIMIT 10";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($rows)) {
      foreach ($rows as $order) {
        $last_order_id = (int)$order['id'];

        echo "event: update\n";
        echo "data: " . json_encode([
          "type" => "new_order",
          "order_id" => (int)$order['id'],
          "restaurant_id" => (int)$order['restaurant_id'],
          "status" => $order['status'],
          "created_at" => $order['created_at']
        ], JSON_UNESCAPED_UNICODE) . "\n\n";

        @ob_flush();
        @flush();
      }
    }

    // Ping
    echo ":\n\n";
    @ob_flush();
    @flush();

    sleep(3);
  } catch (Exception $e) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE) . "\n\n";
    @ob_flush();
    @flush();
    sleep(5);
  }
}
