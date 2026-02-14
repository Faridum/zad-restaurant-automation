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
  $name = trim($_POST['name'] ?? '');
  $owner_id = $_SESSION['role'] === 'owner' ? $_SESSION['user_id'] : (int)($_POST['owner_id'] ?? 0);
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $working_hours = trim($_POST['working_hours'] ?? '');
  $status = 'active';
  $logo_name = null;

  // ✅ تحقق من الحقول الأساسية
  if (empty($name) || empty($owner_id)) {
    echo json_encode(['status' => 'error', 'message' => 'الرجاء إدخال اسم المطعم وتحديد المالك.']);
    exit;
  }

  // ✅ رفع الشعار إن وُجد
  if (!empty($_FILES['logo']['name'])) {
    $upload_dir = __DIR__ . '/../../uploads/restaurants/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed)) {
      echo json_encode(['status' => 'error', 'message' => 'صيغة الصورة غير مدعومة.']);
      exit;
    }

    $logo_name = 'logo_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $logo_name);
  }

  // ✅ إدخال المطعم مع ساعات العمل
  $stmt = $pdo->prepare("
    INSERT INTO restaurants (name, owner_id, phone, address, working_hours, logo, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$name, $owner_id, $phone, $address, $working_hours, $logo_name, $status]);

  echo json_encode(['status' => 'success', 'message' => '✅ تمت إضافة المطعم بنجاح']);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء الإضافة: ' . $e->getMessage()]);
}
