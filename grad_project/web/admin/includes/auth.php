<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// ✅ التحقق من الجلسة
if (!isset($_SESSION['user_id'])) {
  // توليد رابط login ديناميكيًا
  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $host   = $_SERVER['HTTP_HOST'];
  $path   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // مثل /admin/includes
  // نرجع خطوة وحدة للخلف للوصول إلى /admin/login.php
  $loginUrl = $scheme . '://' . $host . $path . '/../login.php';
  header("Location: $loginUrl");
  exit;
}

// ✅ التحقق من الدور الحالي
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

if ($role === 'admin' && in_array($currentPage, ['products.php'])) {
  header("Location: index.php");
  exit;
}

if ($role === 'owner' && in_array($currentPage, ['index.php', 'users.php'])) {
  header("Location: products.php");
  exit;
}
