<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';


header('Content-Type: application/json; charset=UTF-8');


try {


  // ✅ السماح فقط للمالك
  if ($_SESSION['role'] !== 'owner') {
    echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بتعديل المنتجات.']);
    exit;
  }


  $owner_id = $_SESSION['user_id'];


  // ✅ جلب المطعم المرتبط بالمالك
  $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE owner_id = ?");
  $stmt->execute([$owner_id]);
  $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);


  if (!$restaurant) {
    echo json_encode(['status' => 'error', 'message' => 'لا يوجد مطعم مرتبط بحسابك.']);
    exit;
  }


  $restaurant_id = (int)$restaurant['id'];


  // ✅ تحقق من الطلب
  if (!isset($_POST['update_product'])) {
    echo json_encode(['status' => 'error', 'message' => 'طلب غير صالح.']);
    exit;
  }


  // ✅ استقبال البيانات
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;


  $sale_price = (isset($_POST['sale_price']) && $_POST['sale_price'] !== '')
    ? (float)$_POST['sale_price']
    : null;

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : -1;


if ($quantity < 0) {
  echo json_encode(['status' => 'error', 'message' => 'كمية غير صحيحة.']);
  exit;
}


  if ($id <= 0 || $name === '' || $price <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'يرجى إدخال اسم المنتج والسعر بشكل صحيح.']);
    exit;
  }


  // ✅ تحقق أن المنتج يخص مطعمه
  $stmt = $pdo->prepare("SELECT id, photo FROM products WHERE id = ? AND restaurant_id = ?");
  $stmt->execute([$id, $restaurant_id]);
  $product = $stmt->fetch(PDO::FETCH_ASSOC);


  if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'هذا المنتج غير موجود أو لا يتبع مطعمك.']);
    exit;
  }


  // ✅ معالجة الصورة (اختياري)
  $photo = $product['photo'];


  if (!empty($_FILES['photo']['name'])) {


    // ✅ مسار رفع الصور الجديد
    $targetDir = __DIR__ . '/../backend/public/uploads/products/';
    if (!is_dir($targetDir)) {
      mkdir($targetDir, 0775, true);
    }


    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];


    if (!in_array($ext, $allowed)) {
      echo json_encode(['status' => 'error', 'message' => 'نوع الصورة غير مدعوم.']);
      exit;
    }


    if ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
      echo json_encode(['status' => 'error', 'message' => 'حجم الصورة كبير (الحد الأقصى 5MB).']);
      exit;
    }


    $photoName = time() . '_' . preg_replace('/\s+/', '_', basename($_FILES['photo']['name']));
    $targetFile = $targetDir . $photoName;


    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
      $photo = $photoName;
    } else {
      echo json_encode(['status' => 'error', 'message' => 'فشل رفع الصورة.']);
      exit;
    }
  }


  // ✅ تنفيذ التحديث (بدون is_active لأنه غير موجود في DB)
$stmt = $pdo->prepare("
  UPDATE products
  SET name = ?, description = ?, price = ?, sale_price = ?, quantity = ?, photo = ?
  WHERE id = ? AND restaurant_id = ?
");


$stmt->execute([
  $name,
  $description,
  $price,
  $sale_price,
  $quantity,
  $photo,
  $id,
  $restaurant_id
]);


  // ❌ لا يوجد جدول updates في قاعدة البيانات الحالية لذلك لن نسجل أي شيء
  echo json_encode(['status' => 'success', 'message' => 'تم تحديث المنتج بنجاح ✅']);
  exit;


} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'حدث خطأ في الخادم: ' . $e->getMessage()]);
  exit;
}


