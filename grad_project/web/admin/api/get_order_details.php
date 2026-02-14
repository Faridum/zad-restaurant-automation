<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// السماح فقط للمالك أو المدير
if (!in_array($_SESSION['role'], ['owner', 'admin'])) {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => '🚫 صلاحيات غير كافية'], JSON_UNESCAPED_UNICODE);
  exit;
}

// التحقق من وجود order_id
if (empty($_GET['order_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'رقم الطلب غير موجود'], JSON_UNESCAPED_UNICODE);
  exit;
}

$order_id = (int)$_GET['order_id'];
$user_id  = (int)$_SESSION['user_id'];
$role     = $_SESSION['role'];

// ✅ جلب بيانات الطلب (Admin يشوف كل شيء / Owner طلبات مطعمه فقط)
if ($role === 'admin') {
  $stmt = $pdo->prepare("
    SELECT
      o.id,
      o.order_number,
      o.total_price,
      o.status,
      o.created_at,
      o.notes AS note,
      o.customer_name,
      o.customer_phone,
      u.email AS customer_email,
      r.name  AS restaurant_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    INNER JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.id = ?
    LIMIT 1
  ");
  $stmt->execute([$order_id]);
} else {
  $stmt = $pdo->prepare("
    SELECT
      o.id,
      o.order_number,
      o.total_price,
      o.status,
      o.created_at,
      o.notes AS note,
      o.customer_name,
      o.customer_phone,
      u.email AS customer_email,
      r.name  AS restaurant_name
    FROM orders o
    LEFT JOIN users u ON o.customer_id = u.id
    INNER JOIN restaurants r ON o.restaurant_id = r.id
    WHERE o.id = ?
      AND r.owner_id = ?
    LIMIT 1
  ");
  $stmt->execute([$order_id, $user_id]);
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
  echo json_encode(['status' => 'error', 'message' => '🚫 لا يمكن الوصول إلى هذا الطلب'], JSON_UNESCAPED_UNICODE);
  exit;
}

// ✅ جلب كل أصناف الطلب
$stmtItems = $pdo->prepare("
  SELECT
    oi.id,
    oi.product_id,
    oi.product_name,
    oi.unit_price,
    oi.quantity,
    oi.total_price,
    p.photo AS product_photo,
    p.description AS product_description
  FROM order_items oi
  LEFT JOIN products p ON oi.product_id = p.id
  WHERE oi.order_id = ?
  ORDER BY oi.id ASC
");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

$total_qty = 0;
foreach ($items as $it) {
  $total_qty += (int)$it['quantity'];
}

$response = [
  'status' => 'success',
  'order' => [
    'id' => (int)$order['id'],
    'order_number' => $order['order_number'] ?? null,
    'status' => $order['status'],
    'created_at' => $order['created_at'],
    'total_price' => (float)$order['total_price'],
    'total_qty' => (int)$total_qty,
    'items_count' => count($items),
    'note' => $order['note'],
    'restaurant_name' => $order['restaurant_name'],
    'customer' => [
      'name' => $order['customer_name'] ?? null,
      'email' => $order['customer_email'] ?? null,
      'phone' => $order['customer_phone'] ?? null
    ],
    'items' => []
  ]
];

foreach ($items as $it) {
  $response['order']['items'][] = [
    'id' => (int)$it['id'],
    'product_id' => (int)$it['product_id'],
    'name' => $it['product_name'],
    'description' => $it['product_description'],
    'photo' => $it['product_photo'],
    'unit_price' => (float)$it['unit_price'],
    'quantity' => (int)$it['quantity'],
    'total_price' => (float)$it['total_price']
  ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
