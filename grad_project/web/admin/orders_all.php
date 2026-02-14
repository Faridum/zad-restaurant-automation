<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';


// السماح للمدير فقط
if ($_SESSION['role'] !== 'admin') {
  header("Location: products.php");
  exit;
}


// ✅ تحديث حالة الطلب عبر AJAX
if (isset($_POST['order_id'], $_POST['status'])) {
  $order_id = (int)$_POST['order_id'];
  $status = $_POST['status'];


  $statement_update = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
  $statement_update->execute([$status, $order_id]);


  // ❌ جدول updates غير موجود في قاعدة البيانات الحالية
  // $statement_log = $pdo->prepare("INSERT INTO updates (type, product_id) VALUES ('update_order', ?)");
  // $statement_log->execute([$order_id]);


  echo json_encode(['status' => 'success']);
  exit;
}


              // ✅ جلب كل الطلبات بالنظام (بدون GROUP BY - متوافق مع ONLY_FULL_GROUP_BY)
              $sql = "
SELECT
  o.id,
  o.status,
  o.total_price,
  o.created_at,

  u_customer.name AS customer_name,
  r.name AS restaurant_name,
  u_owner.name AS owner_name,

  -- مجموع الكميات داخل الطلب
  (
    SELECT COALESCE(SUM(oi2.quantity), 0)
    FROM order_items oi2
    WHERE oi2.order_id = o.id
  ) AS quantity,

  -- أول منتج في الطلب (للعرض فقط)
  (
    SELECT p2.name
    FROM order_items oi3
    JOIN products p2 ON p2.id = oi3.product_id
    WHERE oi3.order_id = o.id
    ORDER BY oi3.id ASC
    LIMIT 1
  ) AS product_name,

  (
    SELECT p3.photo
    FROM order_items oi4
    JOIN products p3 ON p3.id = oi4.product_id
    WHERE oi4.order_id = o.id
    ORDER BY oi4.id ASC
    LIMIT 1
  ) AS product_photo

FROM orders o
INNER JOIN users u_customer ON o.customer_id = u_customer.id
INNER JOIN restaurants r ON o.restaurant_id = r.id
INNER JOIN users u_owner ON r.owner_id = u_owner.id

ORDER BY o.id DESC
";

$statement_orders = $pdo->query($sql);
$orders = $statement_orders->fetchAll(PDO::FETCH_ASSOC);


// إحصائيات الطلبات
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$ready_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'ready'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$canceled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'canceled'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة الطلبات العامة - زاد</title>
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
    .main-content { padding: 20px; min-height: 100vh; }
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
    .user-info { display: flex; align-items: center; }
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
    .stat-card p { color: #6c757d; font-weight: 500; margin-bottom: 0; }
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
    .controls-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }
    .search-container { position: relative; min-width: 350px; }
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
    .table-container {
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.06);
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .table { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
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
    .table tbody tr { transition: var(--transition); }
    .table tbody tr:hover {
      background-color: rgba(28, 51, 47, 0.03);
      transform: scale(1.002);
    }
    .product-img {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      object-fit: cover;
      border: 2px solid #f0f0f0;
      transition: var(--transition);
    }
    .product-img:hover {
      transform: scale(1.1);
      border-color: var(--gold);
    }
    .status-dropdown {
      border-radius: 10px;
      padding: 10px 12px;
      border: 1px solid #e8e8e8;
      background-color: #fafafa;
      transition: var(--transition);
      font-size: 0.9rem;
      min-width: 140px;
    }
    .status-dropdown:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.2rem rgba(198, 163, 79, 0.15);
    }
    .toast-container { z-index: 1055; }
    .toast {
      border-radius: 12px;
      border: none;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      overflow: hidden;
    }
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
    .no-data h5 { margin-bottom: 10px; color: #495057; }


    @media (max-width: 1200px) {
      body { padding-right: 0; }
    }
    @media (max-width: 992px) {
      .sidebar { transform: translateX(100%); transition: var(--transition); }
      .sidebar.active { transform: translateX(0); }
      .sidebar-toggle {
        display: block;
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--main-color);
      }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .controls-row { flex-direction: column; align-items: stretch; }
      .search-container { width: 100%; }
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
      .content-header { flex-direction: column; align-items: flex-start; }
      .stats-grid { grid-template-columns: 1fr; }
      .search-container { min-width: 100%; }
      .main-content { padding: 15px; }
      .content-card { padding: 20px; }
      .table-responsive { font-size: 0.9rem; }
    }


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


    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #b8943a; }
  </style>
</head>
<body>


  <?php include __DIR__ . '/includes/sidebar_admin.php'; ?>


  <div class="main-content">


    <div class="header-bar">
      <div class="d-flex align-items-center">
        <button class="sidebar-toggle me-3">
          <i class="fas fa-bars"></i>
        </button>
        <h1><i class="fas fa-shopping-cart text-gold"></i> إدارة الطلبات العامة</h1>
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


    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_orders ?></h3>
          <p>إجمالي الطلبات</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="card-content">
          <h3><?= $pending_orders ?></h3>
          <p>طلبات قيد الانتظار</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <div class="card-content">
          <h3><?= $completed_orders ?></h3>
          <p>طلبات مكتملة</p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-times-circle"></i>
        </div>
        <div class="card-content">
          <h3><?= $canceled_orders ?></h3>
          <p>طلبات ملغية</p>
        </div>
      </div>
    </div>


    <div class="content-card">
      <div class="content-header">
        <h2><i class="fas fa-list-alt text-gold"></i> جميع الطلبات</h2>
      </div>


      <div class="controls-row">
        <div class="search-container">
          <input type="text" id="searchBox" class="form-control search-box" placeholder="ابحث بالعميل أو المطعم ...">
          <i class="fas fa-search search-icon"></i>
        </div>
        <select id="statusFilter" class="form-select filter-select">
          <option value="">جميع الحالات</option>
          <option value="pending">قيد المعالجة</option>
          <option value="ready">جاهز</option>
          <option value="completed">مكتمل</option>
          <option value="canceled">ملغي</option>
        </select>
      </div>


      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover" id="ordersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>المنتج</th>
                <th>العميل</th>
                <th>المطعم</th>
                <th>الكمية</th>
                <th>الإجمالي</th>
                <th>الحالة</th>
                <th>التاريخ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                  <tr data-status="<?= $order['status'] ?>">
                    <td class="fw-bold"><?= $order['id'] ?></td>


                    <td>
                      <div class="d-flex align-items-center gap-3">
                        <?php if (!empty($order['product_photo'])): ?>
                          <img src="../backend/public/uploads/products/<?= htmlspecialchars($order['product_photo']) ?>" class="product-img">
                        <?php else: ?>
                          <div class="product-img bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-box text-muted"></i>
                          </div>
                        <?php endif; ?>
                        <div class="fw-bold"><?= htmlspecialchars($order['product_name'] ?? '—') ?></div>
                      </div>
                    </td>


                    <td><?= htmlspecialchars($order['customer_name']) ?></td>


                    <td>
                      <div class="fw-bold"><?= htmlspecialchars($order['restaurant_name']) ?></div>
                      <small class="text-muted"><?= htmlspecialchars($order['owner_name']) ?></small>
                    </td>


                    <td>
                      <span class="badge bg-secondary fs-6"><?= (int)($order['quantity'] ?? 0) ?></span>
                    </td>


                    <td>
                      <span class="fw-bold text-success"><?= number_format($order['total_price'], 2) ?> SDG</span>
                    </td>


                    <td>
                      <select class="status-dropdown" data-id="<?= $order['id'] ?>">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>قيد المعالجة</option>
                        <option value="ready" <?= $order['status'] === 'ready' ? 'selected' : '' ?>>جاهز</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>مكتمل</option>
                        <option value="canceled" <?= $order['status'] === 'canceled' ? 'selected' : '' ?>>ملغي</option>
                      </select>
                    </td>


                    <td>
                      <small class="text-muted"><?= date('Y-m-d', strtotime($order['created_at'])) ?></small>
                      <br>
                      <small class="text-muted"><?= date('H:i', strtotime($order['created_at'])) ?></small>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="no-data">
                    <i class="fas fa-shopping-cart"></i>
                    <h5 class="mt-3">لا توجد طلبات</h5>
                    <p class="text-muted">لم يتم تقديم أي طلبات في النظام بعد</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>


    </div>
  </div>


  <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>


  <script>
    if (window.innerWidth <= 992) {
      document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
      });
    }


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
      new bootstrap.Toast(el, { delay: 3000 }).show();
    }


    $('#searchBox').on('keyup', function() {
      const value = $(this).val().toLowerCase();
      $('#ordersTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
      });
    });


    $('#statusFilter').on('change', function() {
      const status = $(this).val();
      if (status === '') {
        $('#ordersTable tbody tr').show();
      } else {
        $('#ordersTable tbody tr').hide();
        $(`#ordersTable tbody tr[data-status="${status}"]`).show();
      }
    });


    $('.status-dropdown').change(function() {
      const id = $(this).data('id');
      const status = $(this).val();
      const dropdown = $(this);


      const originalText = dropdown.html();
      dropdown.prop('disabled', true);


      $.ajax({
        url: '',
        type: 'POST',
        data: { order_id: id, status: status },
        success: function() {
          showToast('تم تحديث حالة الطلب بنجاح', 'success');
          dropdown.closest('tr').attr('data-status', status);
        },
        error: function() {
          showToast('حدث خطأ أثناء التحديث', 'danger');
        },
        complete: function() {
          dropdown.prop('disabled', false);
        }
      });
    });


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


