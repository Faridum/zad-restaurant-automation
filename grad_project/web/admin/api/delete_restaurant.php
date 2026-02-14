<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=UTF-8');

// ✅ السماح فقط للمدير أو المالك
if (!in_array($_SESSION['role'], ['admin', 'owner'], true)) {
  echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بالوصول.']);
  exit;
}

try {
  $id = (int)($_POST['id'] ?? 0);
  if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'معرّف المطعم مفقود.']);
    exit;
  }

  // ✅ تحقق من أن المالك لا يحذف مطعم غيره
  if ($_SESSION['role'] === 'owner') {
    $stmt = $pdo->prepare("SELECT owner_id FROM restaurants WHERE id = ?");
    $stmt->execute([$id]);
    $restaurant = $stmt->fetch();
    if (!$restaurant || $restaurant['owner_id'] != $_SESSION['user_id']) {
      echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بحذف هذا المطعم.']);
      exit;
    }
  }

  // حذف الشعار من السيرفر
  $stmt_logo = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ?");
  $stmt_logo->execute([$id]);
  $logo = $stmt_logo->fetchColumn();

  if ($logo) {
    $path = __DIR__ . '/../../uploads/restaurants/' . $logo;
    if (file_exists($path)) unlink($path);
  }

  // حذف المطعم من قاعدة البيانات
  $stmt = $pdo->prepare("DELETE FROM restaurants WHERE id = ?");
  $stmt->execute([$id]);

  echo json_encode(['status' => 'success', 'message' => 'تم حذف المطعم بنجاح 🗑️']);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
}
