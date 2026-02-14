<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
  // 🧩 القيم القادمة من التطبيق
  $restaurant_id = isset($_GET['restaurant_id']) ? (int)$_GET['restaurant_id'] : 0;

  if ($restaurant_id <= 0) {
    echo json_encode([
      'status' => 'error',
      'message' => 'معرّف المطعم غير صالح.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
  }

  // 🧩 توليد الرابط الأساسي للصور
  $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
  $uploadPath = '/uploads/products/';

  // ✅ جلب المنتجات الخاصة بالمطعم المحدد
  $stmt = $pdo->prepare("
    SELECT 
      id,
      name,
      description,
      price,
      sale_price,
      CONCAT('$baseUrl$uploadPath', photo) AS photo,
      restaurant_id
    FROM products
    WHERE restaurant_id = ?
    ORDER BY id DESC
  ");
  $stmt->execute([$restaurant_id]);
  $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // ✅ تجهيز الرد النهائي
  echo json_encode([
    'status' => 'success',
    'count' => count($products),
    'data' => $products
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
  // 🔴 في حال حصول خطأ
  echo json_encode([
    'status' => 'error',
    'message' => 'حدث خطأ أثناء جلب المنتجات: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
