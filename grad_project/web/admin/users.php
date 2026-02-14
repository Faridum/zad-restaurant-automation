<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// ✅ السماح فقط للمدير
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit;
}

// ✅ جلب المستخدمين مع التصفية والبحث
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
  $query .= " AND (name LIKE ? OR email LIKE ?)";
  $search_term = "%$search%";
  $params[] = $search_term;
  $params[] = $search_term;
}

if (!empty($role_filter)) {
  $query .= " AND role = ?";
  $params[] = $role_filter;
}

$query .= " ORDER BY id DESC";

$statement_users = $pdo->prepare($query);
$statement_users->execute($params);
$users = $statement_users->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات المستخدمين
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$admin_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$owner_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
$customer_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة المستخدمين - زاد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    :root {
      --main-color: #1C332F;
      --gold: #C6A34F;
      --light-bg: #f8f9fa;
      --white: #ffffff;
      --transition: all 0.3s ease;
    }

    body {
      font-family: 'Tajawal', sans-serif;
      background-color: #f5f7f9;
      color: #333;
      overflow-x: hidden;
      padding-right: 260px;
    }

    /* المحتوى الرئيسي */
    .main-content {
      padding: 20px;
      min-height: 100vh;
    }

    .header-bar {
      background-color: var(--white);
      border-radius: 16px;
      padding: 25px 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      margin-bottom: 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-right: 4px solid var(--gold);
    }

    .header-bar h1 {
      color: var(--main-color);
      font-weight: 800;
      margin: 0;
      font-size: 1.9rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-info {
      display: flex;
      align-items: center;
    }

    .user-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--main-color), var(--gold));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      margin-left: 15px;
      box-shadow: 0 4px 12px rgba(28, 51, 47, 0.2);
    }

    /* بطاقات الإحصائيات */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
      transition: var(--transition);
      border-top: 4px solid var(--gold);
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card h3 {
      font-size: 2.2rem;
      font-weight: 800;
      color: var(--main-color);
      margin-bottom: 5px;
    }

    .stat-card p {
      color: #6c757d;
      font-weight: 500;
      margin-bottom: 0;
    }

    .stat-icon {
      position: absolute;
      left: 25px;
      top: 25px;
      width: 50px;
      height: 50px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(28, 51, 47, 0.1);
      color: var(--main-color);
      font-size: 1.5rem;
    }

    /* بطاقة المحتوى */
    .content-card {
      background: var(--white);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
      margin-bottom: 30px;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .content-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 20px;
    }

    .content-header h2 {
      color: var(--main-color);
      font-weight: 800;
      margin: 0;
      font-size: 1.7rem;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* أدوات التحكم */
    .controls-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .filters-container {
      display: flex;
      gap: 15px;
      align-items: center;
      flex-wrap: wrap;
    }

    /* الأزرار المحسنة */
    .btn-primary-custom {
      background: linear-gradient(135deg, var(--main-color), #152622);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 14px 28px;
      font-weight: 600;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 4px 15px rgba(28, 51, 47, 0.2);
    }

    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(28, 51, 47, 0.3);
    }

    .btn-outline-custom {
      border: 2px solid var(--main-color);
      color: var(--main-color);
      border-radius: 10px;
      padding: 10px 20px;
      font-weight: 600;
      transition: var(--transition);
      background: transparent;
    }

    .btn-outline-custom:hover {
      background: var(--main-color);
      color: white;
    }

    /* شريط البحث والتصفية */
    .search-container {
      position: relative;
      min-width: 300px;
    }

    .search-box {
      border-radius: 12px;
      padding: 14px 50px 14px 20px;
      border: 1px solid #e8e8e8;
      transition: var(--transition);
      background-color: #fafafa;
      font-size: 0.95rem;
    }

    .search-box:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.2rem rgba(198, 163, 79, 0.15);
      background-color: white;
    }

    .search-icon {
      position: absolute;
      left: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .filter-select {
      border-radius: 12px;
      padding: 14px 20px;
      border: 1px solid #e8e8e8;
      background-color: #fafafa;
      min-width: 180px;
      transition: var(--transition);
    }

    .filter-select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.2rem rgba(198, 163, 79, 0.15);
    }

    /* الجدول المحسن */
    .table-container {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .table {
      margin-bottom: 0;
      border-collapse: separate;
      border-spacing: 0;
    }

    .table thead th {
      background: linear-gradient(135deg, var(--main-color), #152622);
      color: white;
      font-weight: 600;
      padding: 18px 15px;
      border: none;
      font-size: 0.95rem;
      position: relative;
    }

    .table thead th::after {
      content: '';
      position: absolute;
      bottom: 0;
      right: 0;
      width: 100%;
      height: 2px;
      background: var(--gold);
    }

    .table tbody td {
      padding: 16px 15px;
      vertical-align: middle;
      border-bottom: 1px solid #f0f0f0;
      transition: var(--transition);
    }

    .table tbody tr {
      transition: var(--transition);
    }

    .table tbody tr:hover {
      background-color: rgba(28, 51, 47, 0.03);
      transform: scale(1.002);
    }

    /* أدوار المستخدمين */
    .role-badge {
      padding: 8px 16px;
      border-radius: 25px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .role-admin {
      background-color: rgba(13, 110, 253, 0.12);
      color: #0d6efd;
      border: 1px solid rgba(13, 110, 253, 0.2);
    }

    .role-owner {
      background-color: rgba(25, 135, 84, 0.12);
      color: #198754;
      border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .role-customer {
      background-color: rgba(108, 117, 125, 0.12);
      color: #6c757d;
      border: 1px solid rgba(108, 117, 125, 0.2);
    }

    /* أزرار الإجراءات */
    .action-buttons {
      display: flex;
      gap: 8px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .btn-action {
      padding: 10px 16px;
      border-radius: 10px;
      font-size: 0.85rem;
      font-weight: 500;
      transition: var(--transition);
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      min-width: 80px;
      justify-content: center;
    }

    .btn-edit {
      background-color: rgba(255, 193, 7, 0.12);
      color: #d4a907;
      border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .btn-edit:hover {
      background-color: #ffc107;
      color: white;
      transform: translateY(-2px);
    }

    .btn-delete {
      background-color: rgba(220, 53, 69, 0.12);
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .btn-delete:hover {
      background-color: #dc3545;
      color: white;
      transform: translateY(-2px);
    }

    /* النماذج (Modals) المحسنة */
    .modal-header {
      background: linear-gradient(135deg, var(--main-color), #152622);
      color: white;
      border-bottom: none;
      border-radius: 16px 16px 0 0;
      padding: 25px 30px;
    }

    .modal-title {
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.3rem;
    }

    .modal-content {
      border-radius: 16px;
      border: none;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
      overflow: hidden;
    }

    .modal-body {
      padding: 30px;
    }

    .modal-footer {
      border-top: 1px solid #eee;
      padding: 25px 30px;
      background: #fafafa;
    }

    .form-label {
      color: var(--main-color);
      font-weight: 600;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .form-control,
    .form-select {
      border-radius: 12px;
      padding: 14px 18px;
      border: 1px solid #e8e8e8;
      transition: var(--transition);
      background-color: #fafafa;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.2rem rgba(198, 163, 79, 0.15);
      background-color: white;
    }

    /* التوست (الإشعارات) */
    .toast-container {
      z-index: 1055;
    }

    .toast {
      border-radius: 12px;
      border: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      overflow: hidden;
    }

    /* حالة عدم وجود بيانات */
    .no-data {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }

    .no-data i {
      font-size: 4rem;
      margin-bottom: 20px;
      opacity: 0.4;
    }

    .no-data h5 {
      margin-bottom: 10px;
      color: #495057;
    }

    /* تحسينات للاستجابة */
    @media (max-width: 1200px) {
      body {
        padding-right: 0;
      }
    }

    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(100%);
        transition: var(--transition);
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .sidebar-toggle {
        display: block;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--main-color);
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .controls-row {
        flex-direction: column;
        align-items: stretch;
      }

      .filters-container {
        width: 100%;
        justify-content: space-between;
      }
    }

    @media (max-width: 768px) {
      .header-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 20px;
      }

      .user-info {
        margin-top: 15px;
        width: 100%;
        justify-content: flex-end;
      }

      .content-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .search-container {
        min-width: 100%;
      }

      .action-buttons {
        flex-direction: column;
        width: 100%;
      }

      .btn-action {
        min-width: 100%;
      }

      .main-content {
        padding: 15px;
      }

      .content-card {
        padding: 20px;
      }
    }

    @media (max-width: 576px) {
      .filters-container {
        flex-direction: column;
        gap: 10px;
      }

      .filter-select,
      .search-container {
        width: 100%;
      }

      .table-responsive {
        font-size: 0.9rem;
      }

      .modal-body {
        padding: 20px;
      }
    }

    /* زر تبديل الشريط الجانبي */
    .sidebar-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--main-color);
      padding: 8px;
      border-radius: 8px;
      transition: var(--transition);
    }

    .sidebar-toggle:hover {
      background-color: rgba(28, 51, 47, 0.1);
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
  <!-- ✅ القائمة الجانبية -->
  <?php include __DIR__ . '/includes/sidebar_admin.php'; ?>

  <!-- المحتوى الرئيسي -->
  <div class="main-content">
    <!-- شريط العنوان -->
    <div class="header-bar">
      <div class="d-flex align-items-center">
        <button class="sidebar-toggle me-3">
          <i class="fas fa-bars"></i>
        </button>
        <h1><i class="fas fa-users-cog text-gold"></i> إدارة المستخدمين</h1>
      </div>
      <div class="user-info">
        <div class="user-avatar">
          <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
        </div>
        <div class="text-start">
          <div class="fw-bold"><?= htmlspecialchars($_SESSION['name']) ?></div>
          <small class="text-muted">مدير النظام</small>
        </div>
      </div>
    </div>

    <!-- إحصائيات سريعة -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_users ?></h3>
          <p>إجمالي المستخدمين</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-user-shield"></i>
        </div>
        <div class="card-content">
          <h3><?= $admin_users ?></h3>
          <p>المديرين</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="card-content">
          <h3><?= $owner_users ?></h3>
          <p>أصحاب المطاعم</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-user"></i>
        </div>
        <div class="card-content">
          <h3><?= $customer_users ?></h3>
          <p>العملاء</p>
        </div>
      </div>
    </div>

    <!-- بطاقة المحتوى الرئيسية -->
    <div class="content-card">
      <div class="content-header">
        <h2><i class="fas fa-list-alt text-gold"></i> قائمة المستخدمين</h2>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addUserModal">
          <i class="fas fa-user-plus"></i>
          إضافة مستخدم جديد
        </button>
      </div>

      <!-- أدوات البحث والتصفية -->
      <form method="GET" action="" class="controls-row">
        <div class="filters-container">
          <div class="search-container">
            <input type="text" name="search" class="form-control search-box" placeholder="ابحث بالاسم أو البريد الإلكتروني..." value="<?= htmlspecialchars($search) ?>">
            <i class="fas fa-search search-icon"></i>
          </div>

          <select name="role" class="form-select filter-select" onchange="this.form.submit()">
            <option value="">جميع الأدوار</option>
            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>مدير</option>
            <option value="owner" <?= $role_filter === 'owner' ? 'selected' : '' ?>>صاحب مطعم</option>
            <option value="customer" <?= $role_filter === 'customer' ? 'selected' : '' ?>>عميل</option>
          </select>
        </div>

        <button type="submit" class="btn btn-outline-custom">
          <i class="fas fa-filter"></i>
          تطبيق الفلتر
        </button>
      </form>

      <!-- جدول المستخدمين -->
      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover" id="usersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>المستخدم</th>
                <th>البريد الإلكتروني</th>
                <th>رقم الجوال</th>
                <th>الدور</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                  <tr>
                    <td class="fw-bold"><?= $user['id'] ?></td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="user-avatar me-3" style="width: 40px; height: 40px; font-size: 0.9rem;">
                          <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                        <div>
                          <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
                          <small class="text-muted">مسجل في: <?= date('Y-m-d', strtotime($user['created_at'])) ?></small>
                        </div>
                      </div>
                    </td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                    <td>
                      <span class="role-badge role-<?= $user['role'] ?>">
                        <i class="fas fa-<?= $user['role'] === 'admin' ? 'shield' : ($user['role'] === 'owner' ? 'user-tie' : 'user') ?>"></i>
                        <?php
                        $role_text = '';
                        switch ($user['role']) {
                          case 'admin':
                            $role_text = 'مدير';
                            break;
                          case 'owner':
                            $role_text = 'صاحب مطعم';
                            break;
                          case 'customer':
                            $role_text = 'عميل';
                            break;
                          default:
                            $role_text = $user['role'];
                        }
                        echo $role_text;
                        ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge bg-<?= $user['last_login'] ? 'success' : 'secondary' ?>">
                        <i class="fas fa-<?= $user['last_login'] ? 'check-circle' : 'clock' ?> me-1"></i>
                        <?= $user['last_login'] ? 'نشط' : 'غير نشط' ?>
                      </span>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn-action btn-edit edit-btn"
                          data-id="<?= $user['id'] ?>"
                          data-name="<?= htmlspecialchars($user['name']) ?>"
                          data-email="<?= htmlspecialchars($user['email']) ?>"
                          data-phone="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                          data-role="<?= htmlspecialchars($user['role']) ?>">
                          <i class="fas fa-edit"></i>
                          تعديل
                        </button>
                        <button class="btn-action btn-delete delete-btn" data-id="<?= $user['id'] ?>">
                          <i class="fas fa-trash"></i>
                          حذف
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="7" class="no-data">
                    <i class="fas fa-users-slash"></i>
                    <h5 class="mt-3">لا يوجد مستخدمون</h5>
                    <p class="text-muted"><?= !empty($search) || !empty($role_filter) ? 'جرب تغيير معايير البحث' : 'ابدأ بإضافة مستخدمين جديدين إلى النظام' ?></p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ Toast Container -->
  <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

  <!-- 🟩 Modal إضافة مستخدم -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="addUserForm">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> إضافة مستخدم جديد</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-user"></i> الاسم الكامل</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-phone"></i> رقم الجوال</label>
              <input type="text" name="phone" class="form-control" placeholder="05XXXXXXXX">
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock"></i> كلمة المرور</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-user-tag"></i> الدور</label>
              <select name="role" class="form-select" required>
                <option value="owner">صاحب مطعم</option>
                <option value="customer">عميل</option>
                <option value="admin">مدير</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary-custom">
              <i class="fas fa-save me-2"></i>
              حفظ المستخدم
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 🟨 Modal تعديل مستخدم -->
  <div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="editUserForm">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i> تعديل بيانات المستخدم</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="edit-id">
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-user"></i> الاسم الكامل</label>
              <input type="text" name="name" id="edit-name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
              <input type="email" name="email" id="edit-email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-phone"></i> رقم الجوال</label>
              <input type="text" name="phone" id="edit-phone" class="form-control" placeholder="05XXXXXXXX">
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-lock"></i> كلمة مرور جديدة (اختياري)</label>
              <input type="password" name="password" class="form-control" placeholder="اتركه فارغاً للحفاظ على كلمة المرور الحالية">
            </div>
            <div class="mb-3">
              <label class="form-label"><i class="fas fa-user-tag"></i> الدور</label>
              <select name="role" id="edit-role" class="form-select" required>
                <option value="owner">صاحب مطعم</option>
                <option value="customer">عميل</option>
                <option value="admin">مدير</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary-custom">
              <i class="fas fa-sync-alt me-2"></i>
              تحديث البيانات
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    // تبديل الشريط الجانبي على الشاشات الصغيرة فقط
    if (window.innerWidth <= 992) {
      document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
      });
    }

    // ✅ دالة عرض التوست
    function showToast(message, type = 'success') {
      const toast = `
        <div class="toast align-items-center text-white bg-${type} border-0 mb-2" role="alert">
          <div class="d-flex">
            <div class="toast-body">
              <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
              ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>`;
      $('#toastContainer').append(toast);
      const el = $('#toastContainer .toast').last()[0];
      new bootstrap.Toast(el, {
        delay: 3000
      }).show();
    }

    // 🟩 إضافة مستخدم
    $('#addUserForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();

      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري الحفظ...');

      $.post('api/user_actions.php', $(this).serialize() + '&action=add', function(response) {
        if (response.status === 'success') {
          $('#addUserModal').modal('hide');
          showToast(response.message, 'success');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(response.message, 'danger');
        }
      }, 'json').always(function() {
        submitBtn.prop('disabled', false).html(originalText);
      });
    });

    // 🟨 فتح نافذة تعديل
    $('.edit-btn').click(function() {
      $('#edit-id').val($(this).data('id'));
      $('#edit-name').val($(this).data('name'));
      $('#edit-email').val($(this).data('email'));
      $('#edit-phone').val($(this).data('phone'));
      $('#edit-role').val($(this).data('role'));
      new bootstrap.Modal('#editUserModal').show();
    });

    // 🟨 تعديل مستخدم
    $('#editUserForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();

      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري التحديث...');

      $.post('api/user_actions.php', $(this).serialize() + '&action=edit', function(response) {
        if (response.status === 'success') {
          $('#editUserModal').modal('hide');
          showToast(response.message, 'success');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(response.message, 'danger');
        }
      }, 'json').always(function() {
        submitBtn.prop('disabled', false).html(originalText);
      });
    });

    // 🗑️ حذف مستخدم
    $('.delete-btn').click(function() {
      if (!confirm('هل أنت متأكد من حذف هذا المستخدم؟ لا يمكن التراجع عن هذا الإجراء.')) return;

      const id = $(this).data('id');
      const deleteBtn = $(this);
      const originalText = deleteBtn.html();

      deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري الحذف...');

      $.post('api/user_actions.php', {
        action: 'delete',
        id
      }, function(response) {
        if (response.status === 'success') {
          showToast(response.message, 'danger');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(response.message, 'danger');
          deleteBtn.prop('disabled', false).html(originalText);
        }
      }, 'json');
    });

    // إغلاق الشريط الجانبي عند النقر خارجيه على الشاشات الصغيرة
    if (window.innerWidth <= 992) {
      document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.querySelector('.sidebar-toggle');
        if (sidebar.classList.contains('active') &&
          !sidebar.contains(e.target) &&
          !toggleBtn.contains(e.target)) {
          sidebar.classList.remove('active');
        }
      });
    }
  </script>
</body>
</html>