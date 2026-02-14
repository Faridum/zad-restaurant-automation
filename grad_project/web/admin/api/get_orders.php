<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
  // 🧩 استقبال النوع والمعرف (العميل أو المالك)
  $type = $_GET['type'] ?? 'customer'; // 'owner' أو 'customer'
  $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;

  if ($id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'معرّف المستخدم غير صالح.'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 🧩 توليد الرابط الكامل للصور
  $baseUrl    = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
  $uploadPath = '/uploads/products/';

  // ✅ اختيار الاستعلام حسب نوع المستخدم
  if ($type === 'owner') {
    // المالك يشاهد الطلبات القادمة من الزبائن
    $sql = "
      SELECT 
        orders.id,
        orders.status,
        orders.total_price,
        orders.quantity,
        orders.created_at,
        users.name AS customer_name,
        products.name AS product_name,
        CONCAT('$baseUrl$uploadPath', products.photo) AS product_photo
      FROM orders
      INNER JOIN users ON orders.customer_id = users.id
      INNER JOIN products ON orders.product_id = products.id
      WHERE orders.owner_id = ?
      ORDER BY orders.id DESC
    ";
  } else {
    // العميل يشاهد طلباته فقط
    $sql = "
      SELECT 
        orders.id,
        orders.status,
        orders.total_price,
        orders.quantity,
        orders.created_at,
        products.name AS product_name,
        CONCAT('$baseUrl$uploadPath', products.photo) AS product_photo
      FROM orders
      INNER JOIN products ON orders.product_id = products.id
      WHERE orders.customer_id = ?
      ORDER BY orders.id DESC
    ";
  }

  $statement_orders = $pdo->prepare($sql);
  $statement_orders->execute([$id]);
  $orders = $statement_orders->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    'status' => 'success',
    'count'  => count($orders),
    'data'   => $orders
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
  echo json_encode([
    'status'  => 'error',
    'message' => 'حدث خطأ أثناء جلب الطلبات: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
