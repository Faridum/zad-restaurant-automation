<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// ✅ السماح فقط للمالك
if ($_SESSION['role'] !== 'owner') {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// ✅ تحديث الحالة عبر AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
header('Content-Type: application/json; charset=utf-8');

$order_id = (int)$_POST['order_id'];
$status   = trim($_POST['status']);

try {
// تحقق أن الطلب يخص المالك
$checkStmt = $pdo->prepare("
SELECT orders.id
FROM orders
INNER JOIN restaurants ON orders.restaurant_id = restaurants.id
WHERE orders.id = ? AND restaurants.owner_id = ?
LIMIT 1
");
$checkStmt->execute([$order_id, $user_id]);

if (!$checkStmt->fetch()) {
echo json_encode(['status' => 'error', 'message' => '🚫 لا تملك صلاحية لتحديث هذا الطلب'], JSON_UNESCAPED_UNICODE);
exit;
}

// تحديث الحالة
$updateStmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
$updateStmt->execute([$status, $order_id]);

// سجل تحديث (إذا جدول updates موجود)
$logStmt = $pdo->prepare("INSERT INTO updates (type, order_id) VALUES (?, ?)");
$logStmt->execute(['order_status_update', $order_id]);

echo json_encode(['status' => 'success', 'message' => 'تم تحديث حالة الطلب بنجاح'], JSON_UNESCAPED_UNICODE);
exit;
} catch (PDOException $e) {
// هذا يخليك تشوف الخطأ الحقيقي بدل "حدث خطأ"
echo json_encode([
'status' => 'error',
'message' => 'SQL Error: ' . $e->getMessage()
], JSON_UNESCAPED_UNICODE);
exit;
}
}

// ✅ حساب عدد الطلبات حسب الحالة
$stats = ['pending' => 0, 'accepted' => 0, 'preparing' => 0, 'ready' => 0, 'completed' => 0];
$countStmt = $pdo->prepare("
  SELECT orders.status, COUNT(*) AS count
  FROM orders
  INNER JOIN restaurants ON orders.restaurant_id = restaurants.id
  WHERE restaurants.owner_id = ?
  GROUP BY orders.status
");

$countStmt->execute([$user_id]);
$counts = $countStmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($counts as $row) {
  $stats[$row['status']] = $row['count'];
}

// ✅ جلب الطلبات الخاصة بالمالك فقط
$stmt = $pdo->prepare("
  SELECT
    orders.id,
    orders.order_number,
    orders.total_price,
    orders.status,
    orders.created_at,
    orders.customer_name,
    orders.customer_phone,
    restaurants.name AS restaurant_name,

    GROUP_CONCAT(CONCAT(order_items.product_name, ' x', order_items.quantity) SEPARATOR ' , ') AS items_summary,
    SUM(order_items.quantity) AS total_qty

  FROM orders
  INNER JOIN restaurants ON orders.restaurant_id = restaurants.id
  LEFT JOIN order_items ON order_items.order_id = orders.id
  WHERE restaurants.owner_id = ?
  GROUP BY
    orders.id,
    orders.order_number,
    orders.total_price,
    orders.status,
    orders.created_at,
    orders.customer_name,
    orders.customer_phone,
    restaurants.name
  ORDER BY
    CASE
      WHEN orders.status = 'pending' THEN 1
      WHEN orders.status = 'ready' THEN 2
      WHEN orders.status = 'completed' THEN 3
      ELSE 4
    END,
    orders.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// ✅ إحصائيات إضافية
$total_orders = count($orders);
$total_revenue = 0;
foreach ($orders as $order) {
  if (in_array($order['status'], ['completed', 'ready'])) {
    $total_revenue += $order['total_price'];
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة الطلبات - زاد</title>
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
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ed 100%);
      min-height: 100vh;
      padding-right: 280px;
    }

    @media (max-width: 992px) {
      body {
        padding-right: 0;
      }
    }

    /* المحتوى الرئيسي */
    .main-content {
      padding: 30px;
      min-height: 100vh;
    }

    /* بطاقة المحتوى */
    .content-card {
      background: var(--white);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
    }

    /* رأس الصفحة */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 20px;
    }

    .page-title {
      color: var(--main-color);
      font-weight: 800;
      font-size: 2.2rem;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .page-title i {
      color: var(--gold);
      background: linear-gradient(135deg, rgba(198, 163, 79, 0.1) 0%, rgba(28, 51, 47, 0.1) 100%);
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
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
      border-top: 4px solid;
      position: relative;
      overflow: hidden;
    }

    .stat-card.pending {
      border-color: #ffc107;
    }

    .stat-card.ready {
      border-color: #17a2b8;
    }

    .stat-card.completed {
      border-color: #28a745;
    }

    .stat-card.canceled {
      border-color: #dc3545;
    }

    .stat-card.revenue {
      border-color: var(--gold);
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, rgba(198, 163, 79, 0.05) 0%, rgba(28, 51, 47, 0.05) 100%);
      z-index: 0;
    }

    .stat-card .card-content {
      position: relative;
      z-index: 1;
    }

    .stat-card h3 {
      font-size: 2rem;
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
      font-size: 1.5rem;
    }

    .stat-card.pending .stat-icon {
      background: rgba(255, 193, 7, 0.1);
      color: #ffc107;
    }

    .stat-card.ready .stat-icon {
      background: rgba(23, 162, 184, 0.1);
      color: #17a2b8;
    }

    .stat-card.completed .stat-icon {
      background: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }

    .stat-card.canceled .stat-icon {
      background: rgba(220, 53, 69, 0.1);
      color: #dc3545;
    }

    .stat-card.revenue .stat-icon {
      background: rgba(198, 163, 79, 0.1);
      color: var(--gold);
    }

    /* شريط البحث والتصفية */
    .search-filter-bar {
      background: var(--white);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
      margin-bottom: 25px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: center;
    }

    .search-box {
      position: relative;
      flex: 1;
      min-width: 250px;
    }

    .search-box i {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #6c757d;
    }

    .search-box input {
      padding-right: 45px;
      border-radius: 12px;
      border: 2px solid #e9ecef;
      transition: var(--transition);
    }

    .search-box input:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.25rem rgba(198, 163, 79, 0.15);
    }

    .filter-buttons {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filter-btn {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 8px 16px;
      background: white;
      transition: var(--transition);
      font-weight: 500;
    }

    .filter-btn.active,
    .filter-btn:hover {
      background: var(--main-color);
      color: white;
      border-color: var(--main-color);
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

    .table tbody tr.new-order {
      animation: pulseHighlight 2s ease-in-out;
      background: rgba(40, 167, 69, 0.05);
    }

    @keyframes pulseHighlight {
      0% {
        background: rgba(40, 167, 69, 0.2);
      }

      100% {
        background: rgba(40, 167, 69, 0.05);
      }
    }

    /* صورة المنتج */
    .product-image {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      object-fit: cover;
      border: 2px solid #e9ecef;
      transition: var(--transition);
    }

    .product-image:hover {
      transform: scale(1.1);
      border-color: var(--gold);
    }

    .image-placeholder {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6c757d;
      border: 2px dashed #dee2e6;
    }

    /* حالة الطلب */
    .status-badge {
      padding: 8px 16px;
      border-radius: 25px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: var(--transition);
      cursor: pointer;
    }

    .status-pending {
      background-color: rgba(255, 193, 7, 0.12);
      color: #ffc107;
      border: 1px solid rgba(255, 193, 7, 0.2);
    }

    .status-ready {
      background-color: rgba(23, 162, 184, 0.12);
      color: #17a2b8;
      border: 1px solid rgba(23, 162, 184, 0.2);
    }

    .status-completed {
      background-color: rgba(40, 167, 69, 0.12);
      color: #28a745;
      border: 1px solid rgba(40, 167, 69, 0.2);
    }

    .status-canceled {
      background-color: rgba(220, 53, 69, 0.12);
      color: #dc3545;
      border: 1px solid rgba(220, 53, 69, 0.2);
    }

    .status-badge:hover {
      transform: scale(1.05);
    }

    /* زر الإجراءات */
    .action-btn {
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 0.85rem;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: var(--transition);
      border: none;
      background: rgba(28, 51, 47, 0.1);
      color: var(--main-color);
    }

    .action-btn:hover {
      background: var(--main-color);
      color: white;
      transform: translateY(-2px);
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

    /* زر تبديل الشريط الجانبي */
    .sidebar-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--main-color);
      padding: 10px;
      border-radius: 10px;
      transition: var(--transition);
      z-index: 1001;
    }

    .sidebar-toggle:hover {
      background-color: rgba(28, 51, 47, 0.1);
    }

    /* طبقة overlay للشاشات الصغيرة */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      right: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }

    .sidebar-overlay.active {
      display: block;
    }

    /* Modal محسن */
    .modal-content {
      border-radius: 20px;
      border: none;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      padding: 25px 30px;
      background: linear-gradient(135deg, var(--main-color), #152622);
      color: white;
      border-radius: 20px 20px 0 0;
    }

    .modal-body {
      padding: 30px;
    }

    .modal-footer {
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      padding: 20px 30px;
    }

    /* Toast محسن */
    .custom-toast {
      border-radius: 12px;
      border: none;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
      backdrop-filter: blur(10px);
    }

    .toast-success {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
      color: white;
    }

    .toast-error {
      background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
      color: white;
    }

    .toast-warning {
      background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
      color: #212529;
    }

    .toast-info {
      background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
      color: white;
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
        width: 280px;
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .sidebar-toggle {
        display: block;
      }

      .main-content {
        padding: 20px;
      }

      .content-card {
        padding: 25px;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }

      .search-filter-bar {
        flex-direction: column;
        align-items: stretch;
      }

      .search-box {
        min-width: 100%;
      }

      .filter-buttons {
        justify-content: center;
      }
    }

    @media (max-width: 768px) {
      .content-card {
        padding: 20px;
      }

      .page-title {
        font-size: 1.8rem;
      }

      .page-title i {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
      }

      .stats-grid {
        grid-template-columns: 1fr;
      }

      .table-responsive {
        font-size: 0.9rem;
      }
    }

    @media (max-width: 576px) {
      .main-content {
        padding: 15px;
      }

      .content-card {
        padding: 15px;
      }

      .page-title {
        font-size: 1.6rem;
      }

      .table thead {
        display: none;
      }

      .table tbody tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 15px;
      }

      .table tbody td {
        display: block;
        text-align: left;
        border: none;
        padding: 8px 0;
        position: relative;
        padding-right: 50%;
      }

      .table tbody td::before {
        content: attr(data-label);
        position: absolute;
        right: 0;
        width: 45%;
        padding-left: 10px;
        font-weight: bold;
        color: var(--main-color);
      }

      .status-badge,
      .action-btn {
        width: 100%;
        justify-content: center;
        margin-bottom: 5px;
      }
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
  <!-- ✅ الشريط الجانبي -->
  <?php include __DIR__ . '/includes/sidebar_owner.php'; ?>

  <!-- طبقة overlay للشاشات الصغيرة -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- المحتوى الرئيسي -->
  <div class="main-content">
    <div class="container-fluid">
      <!-- رأس الصفحة -->
      <div class="page-header">
        <div class="d-flex align-items-center">
          <button class="sidebar-toggle me-3" id="sidebarToggle">
            <i class="fas fa-bars"></i>
          </button>
          <h1 class="page-title">
            <i class="fas fa-clipboard-list"></i>
            إدارة الطلبات
          </h1>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <span class="badge bg-success fs-6 px-3 py-2 d-flex align-items-center" id="newOrdersBadge">
            <i class="fas fa-shopping-cart me-2"></i> الطلبات الجديدة: <?= $stats['pending'] ?>
          </span>
        </div>
      </div>

      <!-- بطاقات الإحصائيات -->
      <div class="stats-grid">
        <div class="stat-card pending">
          <div class="stat-icon">
            <i class="fas fa-clock"></i>
          </div>
          <div class="card-content">
            <h3><?= $stats['pending'] ?></h3>
            <p>طلبات قيد المعالجة</p>
          </div>
        </div>

        <div class="stat-card ready">
          <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-content">
            <h3><?= $stats['ready'] ?></h3>
            <p>طلبات جاهزة</p>
          </div>
        </div>

        <div class="stat-card completed">
          <div class="stat-icon">
            <i class="fas fa-check-double"></i>
          </div>
          <div class="card-content">
            <h3><?= $stats['completed'] ?></h3>
            <p>طلبات مستلمة</p>
          </div>
        </div>

        <div class="stat-card revenue">
          <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <div class="card-content">
            <h3><?= number_format($total_revenue, 2) ?> SDG</h3>
            <p>إجمالي الإيرادات</p>
          </div>
        </div>
      </div>

      <!-- شريط البحث والتصفية -->
      <div class="search-filter-bar">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" class="form-control" placeholder="ابحث في الطلبات..." id="searchInput">
        </div>
        <div class="filter-buttons">
          <button class="filter-btn active" data-filter="all">الكل</button>
          <button class="filter-btn" data-filter="pending">قيد المعالجة</button>
          <button class="filter-btn" data-filter="ready">جاهزة</button>
          <button class="filter-btn" data-filter="completed">مستلمة</button>
          <button class="filter-btn" data-filter="canceled">ملغية</button>
        </div>
      </div>

      <!-- جدول الطلبات -->
      <div class="content-card">
        <div class="table-container">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>العميل</th>
                  <th>ملخص المنتجات</th>
                  <th>إجمالي الكمية</th>
                  <th>الإجمالي</th>
                  <th>الحالة</th>
                  <th>التاريخ</th>
                  <th>الإجراءات</th>
                </tr>
              </thead>
              <tbody id="ordersTable">
                <?php foreach ($orders as $order): ?>
                  <tr class="clickable-row"
                    data-id="<?= $order['id'] ?>"
                    data-status="<?= $order['status'] ?>"
                    data-customer="<?= htmlspecialchars(strtolower($order['customer_name'])) ?>">
                    <td class="fw-bold" data-label="رقم الطلب">#<?= $order['id'] ?></td>
                    <td data-label="العميل">
                      <div class="fw-bold"><?= htmlspecialchars($order['customer_name']) ?></div>
                      <?php if ($order['customer_phone']): ?>
                        <small class="text-muted"><?= htmlspecialchars($order['customer_phone']) ?></small>
                      <?php endif; ?>
                    </td>
                    <td data-label="ملخص المنتجات">
                      <div class="fw-bold"><?= htmlspecialchars($order['items_summary'] ?? '—') ?></div>
                      <small class="text-muted">مطعم: <?= htmlspecialchars($order['restaurant_name']) ?></small>
                    </td>

                    <td data-label="إجمالي الكمية" class="fw-bold">
                      <?= (int)($order['total_qty'] ?? 0) ?>
                    </td>

                    <td data-label="الإجمالي" class="fw-bold text-success">
                      <?= number_format($order['total_price'], 2) ?> SDG
                    </td>
                    <td data-label="الحالة">
                      <select class="form-select status-select"
                        data-order-id="<?= $order['id'] ?>"
                        data-old="<?= $order['status'] ?>"
                        style="min-width:140px;">
                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                        <option value="accepted" <?= $order['status'] == 'accepted' ? 'selected' : '' ?>>تم القبول</option>
                        <option value="preparing" <?= $order['status'] == 'preparing' ? 'selected' : '' ?>>قيد التحضير</option>
                        <option value="ready" <?= $order['status'] == 'ready' ? 'selected' : '' ?>>جاهز</option>
                        <option value="completed" <?= $order['status'] == 'completed' ? 'selected' : '' ?>>مستلم</option>
                        <option value="canceled" <?= $order['status'] == 'canceled' ? 'selected' : '' ?>>ملغي</option>
                      </select>
                    </td>
                    <td data-label="التاريخ">
                      <div class="small text-muted">
                        <?= date('Y/m/d', strtotime($order['created_at'])) ?>
                      </div>
                      <div class="small text-muted">
                        <?= date('H:i', strtotime($order['created_at'])) ?>
                      </div>
                    </td>
                    <td data-label="الإجراءات">
                      <button class="btn action-btn view-details" data-order-id="<?= $order['id'] ?>">
                        <i class="fas fa-eye"></i> عرض
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                  <tr>
                    <td colspan="8" class="no-data">
                      <i class="fas fa-clipboard-list"></i>
                      <h5 class="mt-3">لا توجد طلبات</h5>
                      <p class="text-muted">لم يتم استلام أي طلبات حتى الآن</p>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 🧾 مودال عرض التفاصيل -->
  <div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="fas fa-file-alt"></i>
            تفاصيل الطلب
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="orderDetailsContent">
          <div class="text-center text-muted py-4">
            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
            <div>جاري تحميل التفاصيل...</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔊 ملفات الصوت -->
  <audio id="soundNewOrder" src="assets/sound/notify_new.mp3" preload="auto"></audio>
  <audio id="soundUpdateOrder" src="assets/sound/notify_update.mp3" preload="auto"></audio>

  <!-- ✅ Toast محسن -->
  <div class="position-fixed bottom-0 start-0 p-4" style="z-index: 1080">
    <div id="toastContainer"></div>
  </div>

  <script>
    // عناصر DOM
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // تبديل الشريط الجانبي
    function toggleSidebar() {
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
      document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }

    // إضافة event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    sidebarOverlay.addEventListener('click', toggleSidebar);

    // البحث والتصفية
    document.getElementById('searchInput').addEventListener('input', function(e) {
      const searchTerm = e.target.value.toLowerCase();
      const rows = document.querySelectorAll('#ordersTable tr[data-customer]');

      rows.forEach(row => {
        const customerName = row.getAttribute('data-customer');
        if (customerName.includes(searchTerm)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    });

    // تصفية حسب الحالة
    document.querySelectorAll('.filter-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        const filter = this.getAttribute('data-filter');
        const rows = document.querySelectorAll('#ordersTable tr[data-status]');

        rows.forEach(row => {
          if (filter === 'all' || row.getAttribute('data-status') === filter) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    });

    document.querySelectorAll('.status-select').forEach(select => {
      select.addEventListener('change', function() {
        const selectEl = this;
        const orderId = selectEl.getAttribute('data-order-id');
        const newStatus = selectEl.value;
        const oldStatus = selectEl.getAttribute('data-old') || selectEl.value;

        selectEl.disabled = true;

        $.post('api/update_order_status.php', {
          order_id: orderId,
          status: newStatus
        }, function(response) {
          if (response.status === 'success') {
            showToast('✅ ' + response.message, 'success');

            // تحديث صف الطلب
            const row = document.querySelector(`tr[data-id="${orderId}"]`);
            if (row) row.setAttribute('data-status', newStatus);

            // حفظ الحالة الجديدة كـ old
            selectEl.setAttribute('data-old', newStatus);

            refreshStats();
          } else {
            showToast('⚠️ ' + response.message, 'warning');
            selectEl.value = oldStatus;
          }

          selectEl.disabled = false;
        }, 'json').fail(() => {
          showToast('❌ حدث خطأ أثناء التحديث', 'error');
          selectEl.value = oldStatus;
          selectEl.disabled = false;
        });
      });
    });


    // عرض تفاصيل الطلب
    document.querySelectorAll('.view-details').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const orderId = this.getAttribute('data-order-id');
        showOrderDetails(orderId);
      });
    });

    function showOrderDetails(orderId) {
      $('#orderDetailsModal').modal('show');
      $('#orderDetailsContent').html(`
        <div class="text-center text-muted py-4">
          <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
          <div>جاري تحميل التفاصيل...</div>
        </div>
      `);

      $.get('api/get_order_details.php', {
        order_id: orderId
      }, function(response) {
        if (response.status === 'success') {
          const o = response.order;
          const html = `
            <div class="row g-4">
              <div class="col-md-6">
                <div class="info-card border rounded-3 p-3">
                  <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>معلومات العميل</h6>
                  <p class="mb-1"><strong>الاسم:</strong> ${o.customer.name}</p>
                  ${o.customer.phone ? `<p class="mb-1"><strong>الهاتف:</strong> ${o.customer.phone}</p>` : ''}
                  ${o.customer.email ? `<p class="mb-0"><strong>البريد:</strong> ${o.customer.email}</p>` : ''}
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="info-card border rounded-3 p-3">
                  <h6 class="text-primary mb-3"><i class="fas fa-store me-2"></i>معلومات المطعم</h6>
                  <p class="mb-0"><strong>اسم المطعم:</strong> ${o.restaurant_name}</p>
                </div>
              </div>
              
              <div class="col-md-6">
              <div class="info-card border rounded-3 p-3">
                <h6 class="text-primary mb-3"><i class="fas fa-box me-2"></i>أصناف الطلب</h6>

                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th>الصنف</th>
                        <th class="text-center">الكمية</th>
                        <th class="text-center">سعر الوحدة</th>
                        <th class="text-center">الإجمالي</th>
                      </tr>
                    </thead>
                    <tbody>
                      ${(o.items || []).map(item => `
                        <tr>
                          <td>${item.name}</td>
                          <td class="text-center">${item.quantity}</td>
                          <td class="text-center">${Number(item.unit_price).toFixed(2)}</td>
                          <td class="text-center text-success fw-bold">${Number(item.total_price).toFixed(2)}</td>
                        </tr>
                      `).join('')}
                    </tbody>
                  </table>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                  <div class="text-muted">عدد الأصناف: <strong>${o.items_count}</strong></div>
                  <div class="text-muted">إجمالي الكمية: <strong>${o.total_qty}</strong></div>
                </div>

                <div class="mt-2 fw-bold text-success">
                  إجمالي الطلب: ${Number(o.total_price).toFixed(2)} SDG
                </div>
              </div>

              </div>
              
              <div class="col-md-6">
                <div class="info-card border rounded-3 p-3">
                  <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>معلومات الطلب</h6>
                  <p class="mb-1"><strong>الحالة:</strong> 
                    <span class="badge bg-${
                      o.status === 'completed' ? 'success' :
                      o.status === 'ready' ? 'info' :
                      o.status === 'canceled' ? 'danger' : 'warning'
                    }">${o.status}</span>
                  </p>
                  <p class="mb-1"><strong>رقم الطلب:</strong> #${o.order_number}</p>
                  <p class="mb-0"><strong>التاريخ:</strong> ${o.created_at}</p>
                </div>
              </div>
              
              ${o.note ? `
                <div class="col-12">
                  <div class="info-card border rounded-3 p-3">
                    <h6 class="text-primary mb-3"><i class="fas fa-sticky-note me-2"></i>ملاحظات الطلب</h6>
                    <p class="mb-0">${o.note}</p>
                  </div>
                </div>
              ` : ''}
            </div>
          `;
          $('#orderDetailsContent').html(html);
        } else {
          $('#orderDetailsContent').html(`
            <div class="alert alert-danger text-center">
              <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
              <h5>${response.message}</h5>
            </div>
          `);
        }
      }, 'json').fail(() => {
        $('#orderDetailsContent').html(`
          <div class="alert alert-danger text-center">
            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
            <h5>حدث خطأ أثناء تحميل البيانات</h5>
            <p class="mb-0">يرجى المحاولة مرة أخرى</p>
          </div>
        `);
      });
    }

    // إظهار Toast
    function showToast(message, type) {
      const colorClass = {
        success: 'toast-success',
        error: 'toast-error',
        warning: 'toast-warning',
        info: 'toast-info'
      } [type] || 'toast-success';

      const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
      } [type] || 'fa-info-circle';

      const toast = $(`
        <div class="toast custom-toast ${colorClass} show mb-3" role="alert">
          <div class="toast-header border-0">
            <i class="fas ${icon} me-2"></i>
            <strong class="me-auto">إشعار</strong>
            <small>الآن</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">
            ${message}
          </div>
        </div>
      `);

      $('#toastContainer').append(toast);
      setTimeout(() => toast.remove(), 5000);
    }

    // تحديث عداد الطلبات الجديدة
    function refreshNewOrdersCount() {
      $.get('api/count_new_orders.php', function(response) {
        if (response.status === 'success') {
          $('#newOrdersBadge').html(`<i class="fas fa-shopping-cart me-2"></i> الطلبات الجديدة: ${response.count}`);
        }
      }, 'json');
    }

    // تحديث الإحصائيات
    function refreshStats() {
      setTimeout(() => {
        location.reload();
      }, 2000);
    }

    // SSE للتحديثات المباشرة
    const eventSource = new EventSource('api/sse.php');
    const soundNew = document.getElementById('soundNewOrder');
    const soundUpdate = document.getElementById('soundUpdateOrder');

    eventSource.addEventListener('update', function(e) {
      try {
        const data = JSON.parse(e.data);
        console.log("📡 تحديث جديد:", data);

        if (data.type === 'new_order') {
          showToast('🛎️ تم استلام طلب جديد!', 'success');
          soundNew.play().catch(() => console.warn("🔇 المتصفح منع تشغيل الصوت التلقائي"));

          // إضافة تأثير للطلب الجديد
          refreshNewOrdersCount();
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else if (data.type === 'order_status_update') {
          showToast('🔔 تم تحديث حالة طلب', 'info');
          soundUpdate.play().catch(() => console.warn("🔇 الصوت لم يُشغل"));

          // تحديث الجدول
          setTimeout(() => {
            location.reload();
          }, 1500);
        }

      } catch (err) {
        console.error('⚠️ خطأ في قراءة بيانات SSE:', err);
      }
    });

    eventSource.addEventListener('error', function(e) {
      console.warn("⚠️ تم قطع الاتصال مع SSE مؤقتًا", e);
    });

    // تحديث دوري كل دقيقة
    setInterval(refreshNewOrdersCount, 60000);

    // إغلاق الشريط الجانبي عند النقر على رابط (للشاشات الصغيرة)
    if (window.innerWidth <= 992) {
      document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
          if (sidebar.classList.contains('active')) {
            toggleSidebar();
          }
        });
      });
    }

    // إغلاق الشريط الجانبي عند تغيير حجم النافذة
    window.addEventListener('resize', function() {
      if (window.innerWidth > 992 && sidebar.classList.contains('active')) {
        toggleSidebar();
      }
    });
  </script>
</body>

</html>