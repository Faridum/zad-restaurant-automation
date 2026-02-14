<?php
session_start();

// حذف جميع بيانات الجلسة
$_SESSION = [];

// حذف الكوكيز (اختياري)
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

// تدمير الجلسة نهائيًا
session_destroy();

// توجيه المستخدم إلى صفحة تسجيل الدخول
header("Location: login.php?logout=1");
exit;
