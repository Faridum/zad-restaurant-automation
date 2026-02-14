<?php
require_once __DIR__ . '/includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
  $role = 'owner';

  $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $check->execute([$email]);
  if ($check->fetch()) {
    $error = "البريد الإلكتروني مسجل مسبقاً.";
  } else {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    header("Location: login.php?registered=1");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <title>تسجيل حساب مالك</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #245953, #408E91);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Tajawal', sans-serif;
    }

    .register-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      width: 400px;
      padding: 30px;
    }

    .register-card h3 {
      color: #245953;
    }
  </style>
</head>

<body>
  <div class="register-card">
    <h3 class="text-center mb-3">تسجيل حساب مالك</h3>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger text-center"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">الاسم</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">كلمة المرور</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-success w-100">تسجيل</button>
    </form>

    <p class="text-center mt-3 mb-0">
      لديك حساب؟ <a href="login.php">تسجيل الدخول</a>
    </p>
  </div>
</body>

</html>