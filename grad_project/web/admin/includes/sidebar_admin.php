<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// منع الوصول المباشر من المتصفح
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit;
}

// تحديد الصفحة الحالية لتفعيل الرابط النشط
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- ✅ الشريط الجانبي للمدير -->
<aside class="sidebar">
  <div class="sidebar-header">
    <h3>زاد <i class="fas fa-utensils"></i></h3>
    <small>نظام إدارة المطاعم</small>
  </div>

  <nav class="sidebar-menu">
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="index.php">
          <i class="fas fa-tachometer-alt"></i>
          <span>لوحة التحكم</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="users.php">
          <i class="fas fa-users"></i>
          <span>إدارة المستخدمين</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="restaurants.php">
          <i class="fas fa-store"></i>
          <span>المطاعم</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="orders_all.php">
          <i class="fas fa-shopping-cart"></i>
          <span>الطلبات العامة</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="requests.php">
          <i class="fas fa-chart-bar"></i>
          <span>طلبات التسجيل</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="fas fa-sign-out"></i>
          <span>تسجيل خروج</span>
        </a>
      </li>
    </ul>
  </nav>

  <div class="sidebar-footer">
    <div class="d-flex align-items-center">
      <div class="user-avatar">
        <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
      </div>
      <div>
        <div class="text-white fw-bold"><?= htmlspecialchars($_SESSION['name']) ?></div>
        <small class="text-light">مدير النظام</small>
      </div>
    </div>
  </div>
</aside>

<style>
  :root {
    --main-color: #1C332F;
    --gold: #C6A34F;
    --light-bg: #f8f9fa;
    --white: #ffffff;
    --transition: all 0.3s ease;
  }

  .sidebar {
    width: 260px;
    background: linear-gradient(180deg, var(--main-color) 0%, #152622 100%);
    color: white;
    position: fixed;
    top: 0;
    right: 0;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
  }

  .sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
    background-color: rgba(0, 0, 0, 0.1);
  }

  .sidebar-header h3 {
    color: var(--gold);
    font-weight: 800;
    margin: 0;
    font-size: 1.5rem;
  }

  .sidebar-header small {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
  }

  .sidebar-menu {
    padding: 20px 0;
  }

  .sidebar-menu .nav-link {
    color: rgba(255, 255, 255, 0.85);
    padding: 14px 20px;
    margin: 2px 0;
    border-radius: 0;
    transition: var(--transition);
    font-weight: 500;
    display: flex;
    align-items: center;
    border-right: 3px solid transparent;
  }

  .sidebar-menu .nav-link i {
    margin-left: 12px;
    font-size: 1.1rem;
    width: 24px;
    text-align: center;
  }

  .sidebar-menu .nav-link:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.08);
    border-right-color: var(--gold);
  }

  .sidebar-menu .nav-link.active {
    color: white;
    background-color: rgba(255, 255, 255, 0.12);
    border-right-color: var(--gold);
  }

  .sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    position: absolute;
    bottom: 0;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.1);
  }
</style>