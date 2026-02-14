<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// السماح فقط للمدير
if ($_SESSION['role'] !== 'admin') {
  header("Location: products.php");
  exit;
}

// جلب الإحصائيات
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_owners = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'owner'")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_restaurants = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();

// جلب إحصائيات الطلبات حسب الحالة
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'canceled'")->fetchColumn();


// جلب آخر 10 طلبات
$sql = "
SELECT
  o.id,
  o.status,
  o.total_price,
  o.created_at,
  u.name AS customer_name,
  r.name AS restaurant_name,
  owner.name AS owner_name,
  (
    SELECT oi.product_name
    FROM order_items oi
    WHERE oi.order_id = o.id
    ORDER BY oi.id ASC
    LIMIT 1
  ) AS product_name
FROM orders o
INNER JOIN users u ON u.id = o.customer_id
INNER JOIN restaurants r ON r.id = o.restaurant_id
INNER JOIN users owner ON owner.id = r.owner_id
ORDER BY o.id DESC
LIMIT 10
";

$latest_orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// جلب أحدث المستخدمين المسجلين
$new_users = $pdo->query("SELECT name, email, role, created_at FROM users ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>لوحة تحكم المدير - زاد</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      /* تعويض عن الشريط الجانبي الثابت */
    }

    /* المحتوى الرئيسي */
    .main-content {
      padding: 20px;
      min-height: 100vh;
    }

    .header-bar {
      background-color: var(--white);
      border-radius: 12px;
      padding: 20px 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .header-bar h1 {
      color: var(--main-color);
      font-weight: 800;
      margin: 0;
      font-size: 1.8rem;
    }

    .user-info {
      display: flex;
      align-items: center;
    }

    .user-avatar {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--main-color), var(--gold));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      margin-left: 15px;
    }

    /* بطاقات الإحصائيات */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      transition: var(--transition);
      border-top: 4px solid var(--gold);
      position: relative;
      overflow: hidden;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
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

    /* المخططات */
    .charts-container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }

    .chart-card {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .chart-card h3 {
      color: var(--main-color);
      font-weight: 700;
      margin-bottom: 20px;
      font-size: 1.3rem;
    }

    /* الجداول */
    .table-card {
      background: var(--white);
      border-radius: 16px;
      padding: 25px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-bottom: 20px;
    }

    .table-card h3 {
      color: var(--main-color);
      font-weight: 700;
      margin-bottom: 20px;
      font-size: 1.3rem;
      display: flex;
      align-items: center;
    }

    .table-card h3 i {
      margin-left: 10px;
      color: var(--gold);
    }

    .table-responsive {
      border-radius: 12px;
      overflow: hidden;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background-color: var(--main-color);
      color: white;
      font-weight: 600;
      padding: 15px;
      border: none;
    }

    .table tbody td {
      padding: 15px;
      vertical-align: middle;
      border-bottom: 1px solid #eee;
    }

    .table tbody tr:hover {
      background-color: rgba(28, 51, 47, 0.03);
    }

    /* الحالات */
    .status-badge {
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
    }

    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }

    .status-completed {
      background-color: #d1edff;
      color: #0c5460;
    }

    .status-ready {
      background-color: #d4edda;
      color: #155724;
    }

    .status-canceled {
      background-color: #f8d7da;
      color: #721c24;
    }

    /* زر عرض الكل */
    .view-all {
      color: var(--gold);
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      transition: var(--transition);
      padding: 8px 15px;
      border-radius: 8px;
      background-color: rgba(198, 163, 79, 0.1);
    }

    .view-all:hover {
      color: var(--main-color);
      background-color: rgba(198, 163, 79, 0.2);
    }

    /* إخفاء زر التبديل */
    .sidebar-toggle {
      display: none;
    }

    /* تحسينات للاستجابة */
    @media (max-width: 992px) {
      body {
        padding-right: 0;
      }

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

      .charts-container {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .stats-grid {
        grid-template-columns: 1fr;
      }

      .header-bar {
        flex-direction: column;
        align-items: flex-start;
      }

      .user-info {
        margin-top: 15px;
      }

      .main-content {
        padding: 15px;
      }
    }

    /* شريط التمرير المخصص للشريط الجانبي */
    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1);
    }

    .sidebar::-webkit-scrollbar-thumb {
      background: var(--gold);
      border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
      background: #b8943a;
    }
  </style>
</head>

<body>

  <?php include __DIR__ . '/includes/sidebar_admin.php'; ?>

  <!-- المحتوى الرئيسي -->
  <div class="main-content">
    <!-- شريط العنوان -->
    <div class="header-bar">
      <button class="sidebar-toggle me-3">
        <i class="fas fa-bars"></i>
      </button>
      <h1>مرحبًا، <?= htmlspecialchars($_SESSION['name']) ?> <span class="text-gold">👋</span></h1>
      <div class="user-info">
        <div class="user-avatar">
          <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
        </div>
        <div>
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
          <i class="fas fa-user-tie"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_owners ?></h3>
          <p>ملاك المطاعم</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-user"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_customers ?></h3>
          <p>العملاء</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-store"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_restaurants ?></h3>
          <p>المطاعم</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_products ?></h3>
          <p>المنتجات</p>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="card-content">
          <h3><?= $total_orders ?></h3>
          <p>الطلبات</p>
        </div>
      </div>
    </div>

    <!-- المخططات -->
    <div class="charts-container">
      <div class="chart-card">
        <h3><i class="fas fa-chart-line"></i> توزيع الطلبات</h3>
        <canvas id="ordersChart" height="250"></canvas>
      </div>

      <div class="chart-card">
        <h3><i class="fas fa-chart-pie"></i> توزيع المستخدمين</h3>
        <canvas id="usersChart" height="250"></canvas>
      </div>
    </div>

    <!-- أحدث الطلبات -->
    <div class="table-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-shopping-cart"></i> أحدث الطلبات</h3>
        <a href="orders.php" class="view-all">عرض الكل <i class="fas fa-arrow-left ms-1"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th>العميل</th>
              <th>المطعم</th>
              <th>المنتج</th>
              <th>السعر</th>
              <th>التاريخ</th>
              <th>الحالة</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($latest_orders)): ?>
              <?php foreach ($latest_orders as $order): ?>
                <tr>
                  <td class="fw-bold"><?= $order['id'] ?></td>
                  <td><?= htmlspecialchars($order['customer_name']) ?></td>
                  <td><?= htmlspecialchars($order['restaurant_name']) ?></td>
                  <td><?= htmlspecialchars($order['product_name']) ?></td>
                  <td class="fw-bold"><?= number_format($order['total_price'], 2) ?> SDG</td>
                  <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                  <td>
                    <?php
                    $status = $order['status'];
                    $badge_class = 'status-' . $status;
                    $status_text = '';
                    switch ($status) {
                      case 'pending':
                        $status_text = 'قيد الانتظار';
                        break;
                      case 'ready':
                        $status_text = 'جاهز';
                        break;
                      case 'completed':
                        $status_text = 'مكتمل';
                        break;
                      case 'canceled':
                        $status_text = 'ملغي';
                        break;
                      default:
                        $status_text = $status;
                    }
                    ?>
                    <span class="status-badge <?= $badge_class ?>"><?= $status_text ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">لا توجد طلبات بعد</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- أحدث المستخدمين -->
    <div class="table-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><i class="fas fa-user-plus"></i> أحدث المستخدمين</h3>
        <a href="users.php" class="view-all">عرض الكل <i class="fas fa-arrow-left ms-1"></i></a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>الاسم</th>
              <th>البريد الإلكتروني</th>
              <th>الدور</th>
              <th>تاريخ التسجيل</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($new_users)): ?>
              <?php foreach ($new_users as $user): ?>
                <tr>
                  <td class="fw-bold"><?= htmlspecialchars($user['name']) ?></td>
                  <td><?= htmlspecialchars($user['email']) ?></td>
                  <td>
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
                    ?>
                    <span class="badge bg-secondary"><?= $role_text ?></span>
                  </td>
                  <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" class="text-center text-muted py-4">لا توجد مستخدمين بعد</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
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

    // مخطط توزيع الطلبات
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ordersCtx, {
      type: 'bar',
      data: {
        labels: ['قيد الانتظار', 'جاهز', 'مكتمل', 'ملغي'],
        datasets: [{
          label: 'عدد الطلبات',
          data: [<?= $pending_orders ?>, 0, <?= $completed_orders ?>, <?= $cancelled_orders ?>],
          backgroundColor: [
            'rgba(255, 193, 7, 0.7)',
            'rgba(23, 162, 184, 0.7)',
            'rgba(40, 167, 69, 0.7)',
            'rgba(220, 53, 69, 0.7)'
          ],
          borderColor: [
            'rgb(255, 193, 7)',
            'rgb(23, 162, 184)',
            'rgb(40, 167, 69)',
            'rgb(220, 53, 69)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // مخطط توزيع المستخدمين
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(usersCtx, {
      type: 'doughnut',
      data: {
        labels: ['ملاك المطاعم', 'العملاء'],
        datasets: [{
          data: [<?= $total_owners ?>, <?= $total_customers ?>],
          backgroundColor: [
            'rgba(198, 163, 79, 0.7)',
            'rgba(28, 51, 47, 0.7)'
          ],
          borderColor: [
            'rgb(198, 163, 79)',
            'rgb(28, 51, 47)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  </script>
</body>

</html>