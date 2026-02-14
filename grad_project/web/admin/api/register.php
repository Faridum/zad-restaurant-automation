<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = 'owner';

  if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "الرجاء إدخال جميع البيانات"]);
    exit;
  }

  try {
    // التحقق من وجود البريد مسبقاً
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      echo json_encode(["status" => "error", "message" => "البريد الإلكتروني مسجل مسبقاً"]);
      exit;
    }

    // إدخال المستخدم
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hashed, $role]);

    $user_id = $pdo->lastInsertId();
    echo json_encode([
      "status" => "success",
      "message" => "تم التسجيل بنجاح",
      "user" => [
        "id" => $user_id,
        "name" => $name,
        "email" => $email,
        "role" => $role
      ]
    ]);
  } catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "طريقة الطلب غير صحيحة"]);
}
