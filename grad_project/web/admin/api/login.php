<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "الرجاء إدخال البريد وكلمة المرور"]);
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
    echo json_encode([
      "status" => "success",
      "message" => "تم تسجيل الدخول بنجاح",
      "user" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email'],
        "role" => $user['role']
      ]
    ]);
  } else {
    echo json_encode(["status" => "error", "message" => "بيانات الدخول غير صحيحة"]);
  }
} else {
  echo json_encode(["status" => "error", "message" => "طلب غير صالح"]);
}
