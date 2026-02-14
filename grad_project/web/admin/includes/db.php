<?php
$host = "localhost"; // في Hostinger يكون اسم السيرفر غالباً: localhost
$user = "farid"; // غيّره إلى اسم المستخدم في Hostinger
$pass = "8208"; // كلمة المرور
$dbname = "final";

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
