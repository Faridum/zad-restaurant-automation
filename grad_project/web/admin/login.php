<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// ✅ إذا المستخدم سجل دخول مسبقاً نوجهه مباشرة للوحة التحكم
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: index.php");
  } else {
    header("Location: products.php");
  }
  exit;
}

// ✅ عند الضغط على زر الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = trim($_POST['password']);

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($password, $user['password'])) {
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
      header("Location: index.php");
    } else {
      header("Location: dashboard.php");
    }
    exit;
  } else {
    $error = "بيانات الدخول غير صحيحة. يرجى التحقق من البريد الإلكتروني وكلمة المرور.";
  }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل الدخول - نظام زاد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --main-color: #1C332F;
      --gold: #C6A34F;
      --light-bg: #f8f9fa;
      --white: #ffffff;
      --transition: all 0.3s ease;
    }

    body {
      background: linear-gradient(135deg, var(--main-color) 0%, #152622 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Tajawal', sans-serif;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwMCAxMDAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9IiMxQzMzMkYiLz48cGF0aCBkPSJNMCA1MDBMMTAwMCA1MDBMMTAwMCAwTDAgMFoiIGZpbGw9IiMxNTI2MjIiIGZpbGwtb3BhY2l0eT0iMC4zIi8+PC9zdmc+');
      opacity: 0.1;
      z-index: 0;
    }

    .login-container {
      position: relative;
      z-index: 1;
      width: 100%;
      max-width: 440px;
    }

    .login-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
      padding: 40px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: var(--transition);
    }

    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.2);
    }

    .login-header {
      text-align: center;
      margin-bottom: 35px;
    }

    .logo-container {
      margin-bottom: 20px;
    }

    .logo-img {
      height: 80px;
      width: auto;
      object-fit: contain;
      transition: var(--transition);
    }

    .logo-img:hover {
      transform: scale(1.05);
    }

    .login-header h1 {
      color: var(--main-color);
      font-weight: 800;
      font-size: 1.8rem;
      margin-bottom: 8px;
    }

    .login-header p {
      color: #6c757d;
      font-size: 1rem;
      margin: 0;
    }

    .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .form-label {
      color: var(--main-color);
      font-weight: 600;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-control {
      border-radius: 12px;
      padding: 16px 20px;
      border: 2px solid #e8e8e8;
      background-color: #fafafa;
      transition: var(--transition);
      font-size: 1rem;
    }

    .form-control:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.2rem rgba(198, 163, 79, 0.15);
      background-color: white;
    }

    .input-icon {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
      font-size: 1.1rem;
    }

    .form-control.with-icon {
      padding-left: 55px;
    }

    .btn-login {
      background: linear-gradient(135deg, var(--main-color), #152622);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 16px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: var(--transition);
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      box-shadow: 0 4px 15px rgba(28, 51, 47, 0.3);
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(28, 51, 47, 0.4);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .alert {
      border-radius: 12px;
      padding: 16px 20px;
      border: none;
      font-weight: 500;
    }

    .alert-danger {
      background-color: rgba(220, 53, 69, 0.1);
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .alert-success {
      background-color: rgba(25, 135, 84, 0.1);
      color: #198754;
      border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .alert-info {
      background-color: rgba(13, 110, 253, 0.1);
      color: #0d6efd;
      border: 1px solid rgba(13, 110, 253, 0.2);
    }

    .login-footer {
      text-align: center;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid #eee;
    }

    .login-footer p {
      color: #6c757d;
      margin: 0;
    }

    .login-footer a {
      color: var(--gold);
      font-weight: 600;
      text-decoration: none;
      transition: var(--transition);
    }

    .login-footer a:hover {
      color: var(--main-color);
    }

    /* تأثيرات إضافية */
    .floating-elements {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      right: 0;
      pointer-events: none;
      z-index: 0;
    }

    .floating-element {
      position: absolute;
      background: rgba(198, 163, 79, 0.1);
      border-radius: 50%;
      animation: float 6s ease-in-out infinite;
    }

    .floating-element:nth-child(1) {
      width: 80px;
      height: 80px;
      top: 10%;
      right: 10%;
      animation-delay: 0s;
    }

    .floating-element:nth-child(2) {
      width: 120px;
      height: 120px;
      bottom: 15%;
      left: 10%;
      animation-delay: 2s;
    }

    .floating-element:nth-child(3) {
      width: 60px;
      height: 60px;
      top: 50%;
      left: 15%;
      animation-delay: 4s;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0) rotate(0deg);
      }

      50% {
        transform: translateY(-20px) rotate(180deg);
      }
    }

    /* بديل للشعار في حالة عدم تحميله */
    .logo-fallback {
      color: var(--gold);
      font-size: 3rem;
      margin-bottom: 15px;
      display: block;
    }

    /* تحسينات للاستجابة */
    @media (max-width: 576px) {
      .login-card {
        padding: 30px 25px;
      }

      .login-header h1 {
        font-size: 1.6rem;
      }

      .logo-img {
        height: 60px;
      }

      .logo-fallback {
        font-size: 2.5rem;
      }
    }

    @media (max-width: 400px) {
      .login-card {
        padding: 25px 20px;
      }

      .form-control {
        padding: 14px 16px;
      }

      .form-control.with-icon {
        padding-left: 50px;
      }

      .input-icon {
        left: 16px;
      }

      .logo-img {
        height: 50px;
      }
    }

    /* تأثيرات التركيز المحسنة */
    .form-control:focus+.input-icon {
      color: var(--gold);
    }

    /* تخصيص شريط التمرير */
    ::-webkit-scrollbar {
      width: 8px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb {
      background: var(--gold);
      border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #b8943a;
    }
  </style>
</head>

<body>
  <!-- عناصر عائمة للخلفية -->
  <div class="floating-elements">
    <div class="floating-element"></div>
    <div class="floating-element"></div>
    <div class="floating-element"></div>
  </div>

  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="logo-container">
          <!-- اللوجو الرئيسي - استبدل المسار بمسار لوجو الخاص بك -->
          <img src="assets/images/logo.png" alt="شعار زاد" class="logo-img" onerror="this.style.display='none'; document.getElementById('logoFallback').style.display='block';">
          <!-- بديل في حالة عدم تحميل اللوجو -->
          <i class="fas fa-utensils logo-fallback" id="logoFallback" style="display: none;"></i>
        </div>
        <h1>تسجيل الدخول</h1>
        <p>مرحباً بعودتك إلى نظام زاد</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4">
          <i class="fas fa-exclamation-triangle me-2"></i>
          <?= $error ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success mb-4">
          <i class="fas fa-check-circle me-2"></i>
          تم التسجيل بنجاح، يمكنك تسجيل الدخول الآن
        </div>
      <?php endif; ?>

      <?php if (!empty($_GET['logout'])): ?>
        <div class="alert alert-info mb-4">
          <i class="fas fa-info-circle me-2"></i>
          تم تسجيل الخروج بنجاح 👋
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-envelope"></i>
            البريد الإلكتروني
          </label>
          <div class="position-relative">
            <input type="email" name="email" class="form-control with-icon" required placeholder="example@email.com">
            <i class="fas fa-envelope input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-lock"></i>
            كلمة المرور
          </label>
          <div class="position-relative">
            <input type="password" name="password" class="form-control with-icon" required placeholder="••••••">
            <i class="fas fa-lock input-icon"></i>
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="fas fa-sign-in-alt"></i>
          دخول إلى النظام
        </button>
      </form>

      <!-- <div class="login-footer">
        <p>
          ليس لديك حساب؟ 
          <a href="register.php">تسجيل جديد</a>
        </p>
      </div> -->
    </div>
  </div>

  <script>
    // إضافة تأثيرات تفاعلية بسيطة
    document.addEventListener('DOMContentLoaded', function() {
      const inputs = document.querySelectorAll('.form-control');

      inputs.forEach(input => {
        // تأثير عند التركيز
        input.addEventListener('focus', function() {
          this.parentElement.classList.add('focused');
        });

        // تأثير عند إزالة التركيز
        input.addEventListener('blur', function() {
          if (this.value === '') {
            this.parentElement.classList.remove('focused');
          }
        });

        // التحقق إذا كان الحقل يحتوي على قيمة مسبقاً
        if (input.value !== '') {
          input.parentElement.classList.add('focused');
        }
      });

      // تأثير عند تحميل الصفحة
      const loginCard = document.querySelector('.login-card');
      loginCard.style.opacity = '0';
      loginCard.style.transform = 'translateY(20px)';

      setTimeout(() => {
        loginCard.style.transition = 'all 0.6s ease';
        loginCard.style.opacity = '1';
        loginCard.style.transform = 'translateY(0)';
      }, 100);

      // التحقق من تحميل اللوجو وعرض البديل إذا لزم الأمر
      const logoImg = document.querySelector('.logo-img');
      const logoFallback = document.getElementById('logoFallback');

      // إذا لم يتم تحميل الصورة، عرض البديل
      if (logoImg && logoImg.complete && logoImg.naturalHeight === 0) {
        logoImg.style.display = 'none';
        logoFallback.style.display = 'block';
      }
    });
  </script>
</body>

</html>