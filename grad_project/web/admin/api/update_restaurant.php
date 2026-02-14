<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'], true)) {
  echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بالوصول.'], JSON_UNESCAPED_UNICODE);
  exit;
}

function json_out($arr)
{
  echo json_encode($arr, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  // ✅ استقبال البيانات
  $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
  $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
  $address = isset($_POST['address']) ? trim($_POST['address']) : '';
  $hours   = isset($_POST['working_hours']) ? trim($_POST['working_hours']) : '';
  $status  = isset($_POST['status']) ? trim($_POST['status']) : 'active';

  if ($id <= 0 || $name === '') {
    json_out([
      'status' => 'error',
      'message' => 'بيانات ناقصة لتحديث المطعم.',
      'debug' => [
        'id' => $id,
        'name' => $name,
        'post_keys' => array_keys($_POST)
      ]
    ]);
  }

  // ✅ تحقق صلاحية المالك
  if ($_SESSION['role'] === 'owner') {
    $check = $pdo->prepare("SELECT owner_id FROM restaurants WHERE id = ? LIMIT 1");
    $check->execute([$id]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['owner_id'] !== (int)$_SESSION['user_id']) {
      json_out(['status' => 'error', 'message' => 'غير مصرح لك بتعديل هذا المطعم.']);
    }
  }

  // ✅ منطق ساعات العمل (اختياري)
  if ($hours !== '') {
    $parts = explode('-', $hours);
    if (count($parts) !== 2) {
      json_out(['status' => 'error', 'message' => 'صيغة ساعات العمل غير صحيحة.']);
    }

    $open_time  = trim($parts[0]);
    $close_time = trim($parts[1]);

    if (strtotime($open_time) === false || strtotime($close_time) === false) {
      json_out(['status' => 'error', 'message' => 'صيغة ساعات العمل غير صحيحة.']);
    }

    if (strtotime($close_time) <= strtotime($open_time)) {
      json_out(['status' => 'error', 'message' => '⏰ وقت الإغلاق يجب أن يكون بعد وقت الافتتاح.']);
    }
  }

  // ✅ جلب الشعار القديم
  $old_logo_stmt = $pdo->prepare("SELECT logo FROM restaurants WHERE id = ? LIMIT 1");
  $old_logo_stmt->execute([$id]);
  $current_logo = (string)$old_logo_stmt->fetchColumn();

  $logo_name = $current_logo;

  /**
   * ✅ مسار رفع ثابت 100% على جهازك
   * DocumentRoot = C:\laragon\www (غالباً)
   * وبما أن مشروعك داخل grad_project:
   * نركب المسار:
   * {DocumentRoot}\grad_project\backend\public\uploads\restaurants\
   */
 

  $projectFolder = 'grad_project';
  $upload_dir = '/cohn/grad_project/web/backend/public/uploads/restaurants/';
    

  // ✅ تأكد أن المجلد موجود
  if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0777, true)) {
      json_out([
        'status' => 'error',
        'message' => 'فشل إنشاء مجلد الرفع.',
        'debug' => ['upload_dir' => $upload_dir]
      ]);
    }
  }

  // ✅ إذا المستخدم اختار ملف، لازم نعالجه
  $has_file = isset($_FILES['logo']) && is_array($_FILES['logo']) && !empty($_FILES['logo']['name']);

  if ($has_file) {
    // ✅ فحص أخطاء الرفع من PHP
    $err = (int)($_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err !== UPLOAD_ERR_OK) {
      json_out([
        'status' => 'error',
        'message' => 'فشل رفع الملف من المتصفح (Upload error).',
        'debug' => [
          'php_upload_error_code' => $err,
          'file' => $_FILES['logo'],
          'hint' => 'راجع upload_max_filesize و post_max_size في php.ini'
        ]
      ]);
    }

    // ✅ فحص الامتداد
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowed, true)) {
      json_out(['status' => 'error', 'message' => 'صيغة الصورة غير مدعومة.']);
    }

    // ✅ اسم جديد
    $logo_name = 'logo_' . time() . '.' . $ext;
    $target = $upload_dir . $logo_name;

    // ✅ تأكد أنه فعلاً ملف مرفوع
    if (!is_uploaded_file($_FILES['logo']['tmp_name'])) {
      json_out([
        'status' => 'error',
        'message' => 'الملف لم يصل كملف مرفوع (is_uploaded_file=false).',
        'debug' => [
          'tmp_name' => $_FILES['logo']['tmp_name'],
          'upload_dir' => $upload_dir
        ]
      ]);
    }

    // ✅ انقل الملف
    if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
      json_out([
        'status' => 'error',
        'message' => 'فشل نقل الملف إلى مجلد الرفع.',
        'debug' => [
          'target' => $target,
          'upload_dir' => $upload_dir,
          'tmp_name' => $_FILES['logo']['tmp_name']
        ]
      ]);
    }

    // ✅ تأكد أنه اتكتب فعلاً
    if (!file_exists($target)) {
      json_out([
        'status' => 'error',
        'message' => 'تم تنفيذ النقل لكن الملف غير موجود فعلياً (file_exists=false).',
        'debug' => ['target' => $target]
      ]);
    }

    // ✅ احذف القديم
    if ($current_logo !== '') {
      $old_path = $upload_dir . $current_logo;
      if (file_exists($old_path)) {
        @unlink($old_path);
      }
    }
  }

  // ✅ تحديث البيانات
  $stmt = $pdo->prepare("
    UPDATE restaurants
    SET name = ?, phone = ?, address = ?, working_hours = ?, logo = ?, status = ?
    WHERE id = ?
  ");
  $stmt->execute([
    $name,
    $phone,
    $address,
    $hours,
    $logo_name,
    $status,
    $id
  ]);

  json_out([
    'status' => 'success',
    'message' => 'تم تحديث بيانات المطعم بنجاح ✏️',
    'debug' => [
      'upload_dir' => $upload_dir,
      'logo_saved_in_db' => $logo_name,
      'file_uploaded' => $has_file ? 'yes' : 'no'
    ]
  ]);
} catch (Exception $e) {
  json_out(['status' => 'error', 'message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()]);
}
