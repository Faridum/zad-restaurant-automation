<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

// ✅ تأكد أن المستخدم مدير فعلاً
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بتنفيذ هذا الإجراء']);
  exit;
}

$action = $_POST['action'] ?? '';

try {
  if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'owner';
    $phone = trim($_POST['phone'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
      echo json_encode(['status' => 'error', 'message' => 'جميع الحقول مطلوبة']);
      exit;
    }

    // ✅ تحقق من أن البريد الإلكتروني غير مستخدم
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      echo json_encode(['status' => 'error', 'message' => 'البريد الإلكتروني مستخدم مسبقًا']);
      exit;
    }

    // ✅ تشفير كلمة المرور
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // ✅ إدخال المستخدم
    $stmt = $pdo->prepare("
      INSERT INTO users (name, email, password, phone, role, created_at)
      VALUES (?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$name, $email, $hashed, $phone, $role]);

    echo json_encode(['status' => 'success', 'message' => 'تمت إضافة المستخدم بنجاح']);
    exit;
  }

  if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'owner';
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($id) || empty($name) || empty($email)) {
      echo json_encode(['status' => 'error', 'message' => 'جميع الحقول مطلوبة']);
      exit;
    }

    // ✅ تحقق من تكرار البريد
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);
    if ($check->fetch()) {
      echo json_encode(['status' => 'error', 'message' => 'هذا البريد مستخدم من مستخدم آخر']);
      exit;
    }

    // ✅ تحديث البيانات
    if (!empty($password)) {
      $hashed = password_hash($password, PASSWORD_BCRYPT);
      $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, role=?, password=? WHERE id=?");
      $stmt->execute([$name, $email, $phone, $role, $hashed, $id]);
    } else {
      $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, role=? WHERE id=?");
      $stmt->execute([$name, $email, $phone, $role, $id]);
    }

    echo json_encode(['status' => 'success', 'message' => 'تم تحديث بيانات المستخدم بنجاح']);
    exit;
  }

  if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);

    if (empty($id)) {
      echo json_encode(['status' => 'error', 'message' => 'معرّف المستخدم غير صالح']);
      exit;
    }

    // ✅ منع حذف المدير الحالي
    if ($id == $_SESSION['user_id']) {
      echo json_encode(['status' => 'error', 'message' => 'لا يمكنك حذف حسابك الشخصي']);
      exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['status' => 'success', 'message' => 'تم حذف المستخدم بنجاح']);
    exit;
  }

  echo json_encode(['status' => 'error', 'message' => 'طلب غير صالح']);
} catch (Exception $e) {
  echo json_encode(['status' => 'error', 'message' => 'حدث خطأ أثناء تنفيذ العملية: ' . $e->getMessage()]);
}
