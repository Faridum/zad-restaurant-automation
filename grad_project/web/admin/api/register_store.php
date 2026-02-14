<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json; charset=utf-8');

// ✅ تأكد أن الطلب فعلاً POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'error', 'message' => 'طريقة الإرسال غير صحيحة.']);
  exit;
}

// ✅ استلام القيم بشكل آمن
$owner_name      = isset($_POST['owner_name']) ? trim($_POST['owner_name']) : '';
$phone           = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$email           = isset($_POST['email']) ? trim($_POST['email']) : '';
$password_plain  = isset($_POST['password']) ? trim($_POST['password']) : '';
$restaurant_name = isset($_POST['restaurant_name']) ? trim($_POST['restaurant_name']) : '';
$address         = isset($_POST['address']) ? trim($_POST['address']) : '';

// ✅ إثباتات الملكية (صور متعددة)
$proofs = $_FILES['proofs'] ?? null;

if ($owner_name === '' || $phone === '' || $email === '' || $password_plain === '' || $restaurant_name === '' || $address === '') {
  echo json_encode(['status' => 'error', 'message' => 'الرجاء تعبئة جميع الحقول المطلوبة.']);
  exit;
}

// ✅ لازم توجد إثباتات
if (!$proofs || !isset($proofs['name']) || !is_array($proofs['name']) || count($proofs['name']) === 0) {
  echo json_encode(['status' => 'error', 'message' => 'الرجاء رفع إثباتات ملكية المطعم (صور).']);
  exit;
}

// ✅ تشفير كلمة المرور
$password = password_hash($password_plain, PASSWORD_BCRYPT);

try {
  // ✅ إنشاء الجدول إن لم يكن موجودًا
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS restaurant_requests (
      id INT AUTO_INCREMENT PRIMARY KEY,
      owner_name VARCHAR(100),
      phone VARCHAR(50),
      email VARCHAR(100),
      password VARCHAR(255),
      restaurant_name VARCHAR(100),
      address VARCHAR(255),
      proofs JSON NULL,
      status ENUM('pending','approved','rejected') DEFAULT 'pending',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // ✅ لو الجدول موجود قديم وفيه description بدل proofs: نضيف proofs لو ناقص
  // (ALTER داخل try بسيط، يتجاهل لو موجود حسب MySQL version)
  try {
    $pdo->exec("ALTER TABLE restaurant_requests ADD COLUMN proofs JSON NULL");
  } catch (Exception $e) {
    // تجاهل: غالباً العمود موجود بالفعل
  }

  // ✅ فحص التكرار بالبريد أو الجوال
  $check = $pdo->prepare("SELECT id FROM restaurant_requests WHERE email = ? OR phone = ?");
  $check->execute([$email, $phone]);
  if ($check->fetch()) {
    echo json_encode(['status' => 'error', 'message' => 'هذا البريد الإلكتروني أو رقم الجوال مستخدم مسبقًا.']);
    exit;
  }

  // ✅ تجهيز مجلد الرفع
  $upload_dir = __DIR__ . '/../../backend/public/uploads/proofs/';
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  // ✅ إعدادات التحقق من الملفات
  $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
  $max_size = 5 * 1024 * 1024; // 5MB لكل صورة

  $saved_files = [];

  // ✅ رفع كل الصور
  $files_count = count($proofs['name']);
  for ($i = 0; $i < $files_count; $i++) {

    $name = $proofs['name'][$i] ?? '';
    $tmp  = $proofs['tmp_name'][$i] ?? '';
    $err  = $proofs['error'][$i] ?? UPLOAD_ERR_NO_FILE;
    $size = $proofs['size'][$i] ?? 0;

    // تجاهل أي عنصر فاضي
    if ($err === UPLOAD_ERR_NO_FILE || $name === '') {
      continue;
    }

    if ($err !== UPLOAD_ERR_OK) {
      echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء رفع إحدى الصور.']);
      exit;
    }

    if ($size > $max_size) {
      echo json_encode(['status' => 'error', 'message' => 'حجم إحدى الصور كبير جداً. الحد الأقصى 5MB لكل صورة.']);
      exit;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
      echo json_encode(['status' => 'error', 'message' => 'صيغة إحدى الصور غير مدعومة. المسموح: JPG, PNG, WEBP']);
      exit;
    }

    // ✅ اسم ملف آمن
    $safe_name = 'proof_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

    if (!move_uploaded_file($tmp, $upload_dir . $safe_name)) {
      echo json_encode(['status' => 'error', 'message' => 'فشل رفع إحدى الصور. تحقق من صلاحيات المجلد.']);
      exit;
    }

    $saved_files[] = $safe_name;
  }

  // ✅ لازم ينجح رفع صورة واحدة على الأقل
  if (count($saved_files) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'لم يتم رفع أي صورة. الرجاء اختيار صور صحيحة.']);
    exit;
  }

  // ✅ إدخال البيانات + حفظ أسماء الصور كـ JSON
  $stmt = $pdo->prepare("
    INSERT INTO restaurant_requests (owner_name, phone, email, password, restaurant_name, address, proofs)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([
    $owner_name,
    $phone,
    $email,
    $password,
    $restaurant_name,
    $address,
    json_encode($saved_files, JSON_UNESCAPED_UNICODE)
  ]);

  echo json_encode([
    'status' => 'success',
    'message' => '✅ تم إرسال طلب التسجيل بنجاح! سيتم مراجعة الإثباتات والتواصل معك قريبًا.',
    'proofs' => $saved_files
  ]);
  exit;
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage()]);
  exit;
}
