<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

try {
  // 🧩 توليد الرابط الأساسي (domain + المسار)
  $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
  $uploadPath = '/uploads/restaurants/';

  // ✅ جلب بيانات المطاعم النشطة فقط
  $stmt = $pdo->query("
    SELECT 
      id,
      name,
      CONCAT('$baseUrl$uploadPath', logo) AS logo,
      address,
      phone,
      working_hours,
      status
    FROM restaurants
    WHERE status = 'active'
    ORDER BY id DESC
  ");

  $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 🟢 تجهيز الرد بصيغة JSON
  echo json_encode([
    'status' => 'success',
    'count' => count($restaurants),
    'data' => $restaurants
  ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
  // 🔴 في حال الخطأ
  echo json_encode([
    'status' => 'error',
    'message' => 'حدث خطأ أثناء جلب المطاعم: ' . $e->getMessage()
  ], JSON_UNESCAPED_UNICODE);
}
