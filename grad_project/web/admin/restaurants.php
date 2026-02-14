<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// ✅ السماح فقط للمدير
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../auth/login.php");
  exit;
}

// ✅ جلب الملاك
$owners = $pdo->query("SELECT id, name FROM users WHERE role = 'owner' ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ✅ جلب جميع المطاعم مع المالك
$sql = "
SELECT restaurants.*, users.name AS owner_name
FROM restaurants
INNER JOIN users ON restaurants.owner_id = users.id
ORDER BY restaurants.id DESC
";
$restaurants = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// إحصائيات المطاعم
$total_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
$active_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'active'")->fetchColumn();
$inactive_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants WHERE status = 'inactive'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة المطاعم - زاد</title>
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

    /* شريط البحث */
    .search-container {
      position: relative;
      min-width: 350px;
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

    /* صورة الشعار */
    .logo-img {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #f0f0f0;
      transition: var(--transition);
    }

    .logo-img:hover {
      transform: scale(1.1);
      border-color: var(--gold);
    }

    /* حالة المطعم */
    .status-badge {
      padding: 8px 16px;
      border-radius: 25px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .status-active {
      background-color: rgba(25, 135, 84, 0.12);
      color: #198754;
      border: 1px solid rgba(25, 135, 84, 0.2);
    }

    .status-inactive {
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
      min-width: 90px;
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

    /* معاينة الصورة */
    .image-preview {
      width: 120px;
      height: 120px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px dashed #ddd;
      display: none;
    }

    .time-inputs {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .time-separator {
      color: #6c757d;
      font-weight: bold;
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

      .search-container {
        width: 100%;
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

      .table-responsive {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 576px) {
      .time-inputs {
        flex-direction: column;
      }

      .time-separator {
        display: none;
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
        <h1><i class="fas fa-store text-gold"></i> إدارة المطاعم</h1>
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
          <i class="fas fa-store"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_restaurants ?></h3>
          <p>إجمالي المطاعم</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-store"></i>
        </div>
        <div class="card-content">
          <h3><?= $active_restaurants ?></h3>
          <p>المطاعم النشطة</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-store-slash"></i>
        </div>
        <div class="card-content">
          <h3><?= $inactive_restaurants ?></h3>
          <p>المطاعم المعطلة</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="card-content">
          <h3><?= count($owners) ?></h3>
          <p>أصحاب المطاعم</p>
        </div>
      </div>
    </div>

    <!-- بطاقة المحتوى الرئيسية -->
    <div class="content-card">
      <div class="content-header">
        <h2><i class="fas fa-list-alt text-gold"></i> قائمة المطاعم</h2>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="fas fa-plus-circle"></i>
          إضافة مطعم جديد
        </button>
      </div>

      <!-- أدوات البحث -->
      <div class="controls-row">
        <div class="search-container">
          <input type="text" id="searchBox" class="form-control search-box" placeholder="ابحث باسم المطعم أو المالك ...">
          <i class="fas fa-search search-icon"></i>
        </div>
      </div>

      <!-- جدول المطاعم -->
      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover" id="restaurantsTable">
            <thead>
              <tr>
                <th>#</th>
                <th>الشعار</th>
                <th>المطعم</th>
                <th>المالك</th>
                <th>الهاتف</th>
                <th>ساعات العمل</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($restaurants)): ?>
                <?php foreach ($restaurants as $r): ?>
                  <tr data-id="<?= $r['id'] ?>">
                    <td class="fw-bold"><?= $r['id'] ?></td>
                    <td>
                      <?php if ($r['logo']): ?>
                        <img src="../uploads/restaurants/<?= htmlspecialchars($r['logo']) ?>" class="logo-img">
                      <?php else: ?>
                        <div class="logo-img bg-light d-flex align-items-center justify-content-center">
                          <i class="fas fa-store text-muted"></i>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($r['name']) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($r['address']) ?></small>
                    </td>
                    <td><?= htmlspecialchars($r['owner_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($r['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                      <small class="text-muted"><?= htmlspecialchars($r['working_hours'] ?? '—') ?></small>
                    </td>
                    <td>
                      <span class="status-badge status-<?= $r['status'] ?>">
                        <i class="fas fa-<?= $r['status'] === 'active' ? 'check-circle' : 'pause-circle' ?>"></i>
                        <?= $r['status'] === 'active' ? 'نشط' : 'معطل' ?>
                      </span>
                    </td>
                    <td>
                      <div class="action-buttons">
                        <button class="btn-action btn-edit edit-btn"
                          data-id="<?= $r['id'] ?>"
                          data-name="<?= htmlspecialchars($r['name']) ?>"
                          data-owner="<?= $r['owner_id'] ?>"
                          data-phone="<?= htmlspecialchars($r['phone']) ?>"
                          data-address="<?= htmlspecialchars($r['address']) ?>"
                          data-working="<?= htmlspecialchars($r['working_hours']) ?>"
                          data-status="<?= $r['status'] ?>"
                          data-logo="<?= htmlspecialchars($r['logo']) ?>">
                          <i class="fas fa-edit"></i>
                          تعديل
                        </button>
                        <button class="btn-action btn-delete delete-btn" data-id="<?= $r['id'] ?>">
                          <i class="fas fa-trash"></i>
                          حذف
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="no-data">
                    <i class="fas fa-store-slash"></i>
                    <h5 class="mt-3">لا توجد مطاعم</h5>
                    <p class="text-muted">ابدأ بإضافة مطاعم جديدة إلى النظام</p>
                    <button class="btn btn-primary-custom mt-2" data-bs-toggle="modal" data-bs-target="#addModal">
                      <i class="fas fa-plus-circle"></i>
                      إضافة أول مطعم
                    </button>
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

  <!-- 🟩 Modal إضافة مطعم -->
  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="addForm" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> إضافة مطعم جديد</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-store"></i> اسم المطعم</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-user-tie"></i> صاحب المطعم</label>
                <select name="owner_id" class="form-select" required>
                  <option value="">— اختر المالك —</option>
                  <?php foreach ($owners as $o): ?>
                    <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-phone"></i> رقم الهاتف</label>
                <input type="text" name="phone" class="form-control" placeholder="+966XXXXXXXXX">
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-map-marker-alt"></i> العنوان</label>
                <input type="text" name="address" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-clock"></i> ساعات العمل</label>
                <div class="time-inputs">
                  <input type="time" name="open_time" class="form-control" required>
                  <span class="time-separator">إلى</span>
                  <input type="time" name="close_time" class="form-control" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-image"></i> شعار المطعم</label>
                <input type="file" name="logo" class="form-control" accept="image/*" id="logoInput">
                <img id="logoPreview" class="image-preview mt-2">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
            <button type="submit" class="btn btn-primary-custom">
              <i class="fas fa-save me-2"></i>
              حفظ المطعم
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ✅ مودال التعديل -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="editForm" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i> تعديل بيانات المطعم</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="edit-id">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-store"></i> اسم المطعم</label>
                <input type="text" name="name" id="edit-name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-user-tie"></i> صاحب المطعم</label>
                <select name="owner_id" id="edit-owner" class="form-select" required>
                  <?php foreach ($owners as $o): ?>
                    <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-phone"></i> رقم الهاتف</label>
                <input type="text" name="phone" id="edit-phone" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-map-marker-alt"></i> العنوان</label>
                <input type="text" name="address" id="edit-address" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-clock"></i> ساعات العمل</label>
                <div class="time-inputs">
                  <input type="time" name="open_time" id="edit-open" class="form-control" required>
                  <span class="time-separator">إلى</span>
                  <input type="time" name="close_time" id="edit-close" class="form-control" required>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label"><i class="fas fa-toggle-on"></i> الحالة</label>
                <select name="status" id="edit-status" class="form-select">
                  <option value="active">نشط</option>
                  <option value="inactive">معطل</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label"><i class="fas fa-image"></i> شعار المطعم</label>
                <div class="d-flex align-items-center gap-3">
                  <input type="file" name="logo" id="edit-logo" class="form-control" accept="image/*">
                  <img id="edit-preview" class="image-preview">
                </div>
                <small class="text-muted">اتركه فارغاً للحفاظ على الشعار الحالي</small>
              </div>
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

    // معاينة صورة الشعار عند الإضافة
    $('#logoInput').on('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          $('#logoPreview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
      }
    });

    // معاينة صورة الشعار عند التعديل
    $('#edit-logo').on('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          $('#edit-preview').attr('src', e.target.result).show();
        }
        reader.readAsDataURL(file);
      }
    });

    // 🔍 البحث الفوري
    $('#searchBox').on('keyup', function() {
      const value = $(this).val().toLowerCase();
      $('#restaurantsTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });

    // 🟨 فتح مودال التعديل
    $('.edit-btn').on('click', function() {
      $('#edit-id').val($(this).data('id'));
      $('#edit-name').val($(this).data('name'));
      $('#edit-owner').val($(this).data('owner'));
      $('#edit-phone').val($(this).data('phone'));
      $('#edit-address').val($(this).data('address'));
      $('#edit-status').val($(this).data('status'));

      const working = $(this).data('working')?.split(' - ') || ['', ''];
      $('#edit-open').val(working[0]);
      $('#edit-close').val(working[1]);

      const logo = $(this).data('logo');
      if (logo) {
        $('#edit-preview').attr('src', '../uploads/restaurants/' + logo).show();
      } else {
        $('#edit-preview').hide();
      }

      new bootstrap.Modal('#editModal').show();
    });

    // 🟩 إضافة مطعم
    $('#addForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();

      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري الحفظ...');

      const formData = new FormData(this);
      const open = formData.get('open_time');
      const close = formData.get('close_time');
      formData.append('working_hours', open + ' - ' + close);

      $.ajax({
        url: 'api/add_restaurant.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
          if (res.status === 'success') {
            $('#addModal').modal('hide');
            showToast('تمت إضافة المطعم بنجاح', 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showToast(res.message || '❌ خطأ أثناء الإضافة', 'danger');
          }
        },
        error: function() {
          showToast('❌ خطأ أثناء الإضافة', 'danger');
        },
        complete: function() {
          submitBtn.prop('disabled', false).html(originalText);
        }
      });
    });

    // 🟧 تعديل مطعم
    $('#editForm').on('submit', function(e) {
      e.preventDefault();
      const submitBtn = $(this).find('button[type="submit"]');
      const originalText = submitBtn.html();

      submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري التحديث...');

      const formData = new FormData(this);
      const open = $('#edit-open').val();
      const close = $('#edit-close').val();
      formData.append('working_hours', open + ' - ' + close);

      $.ajax({
        url: 'api/update_restaurant.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
          if (res.status === 'success') {
            $('#editModal').modal('hide');
            showToast('تم تحديث بيانات المطعم', 'success');
            setTimeout(() => location.reload(), 1500);
          } else {
            showToast(res.message || '❌ خطأ أثناء التعديل', 'danger');
          }
        },
        error: function() {
          showToast('❌ خطأ أثناء التعديل', 'danger');
        },
        complete: function() {
          submitBtn.prop('disabled', false).html(originalText);
        }
      });
    });

    // 🗑️ حذف مطعم
    $('.delete-btn').click(function() {
      const id = $(this).data('id');
      const deleteBtn = $(this);
      const originalText = deleteBtn.html();

      if (!confirm('هل أنت متأكد من حذف هذا المطعم؟ سيتم حذف جميع المنتجات والطلبات المرتبطة به.')) return;

      deleteBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> جاري الحذف...');

      $.post('api/delete_restaurant.php', {
        id
      }, function(res) {
        if (res.status === 'success') {
          showToast('تم حذف المطعم بنجاح', 'danger');
          setTimeout(() => location.reload(), 1500);
        } else {
          showToast(res.message || '❌ خطأ أثناء الحذف', 'danger');
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