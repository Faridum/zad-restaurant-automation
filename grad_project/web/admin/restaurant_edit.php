<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

// ✅ السماح فقط للمالك
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
  header("Location: login.php");
  exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
if ($user_id <= 0) {
  header("Location: login.php");
  exit;
}

/**
 * ✅ مهم جداً:
 * أحياناً Warnings/Notices تظهر داخل HTML وتخرب قيمة input hidden
 * لذلك نخليها لا تُعرض للمستخدم (وخليها في log لو عندك)
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

// ✅ جلب بيانات المطعم (بشكل صريح ومضمون)
$stmt = $pdo->prepare("
  SELECT
    restaurants.id AS id,
    restaurants.name AS name,
    restaurants.phone AS phone,
    restaurants.address AS address,
    restaurants.working_hours AS working_hours,
    restaurants.logo AS logo,
    restaurants.status AS status,
    restaurants.owner_id AS owner_id
  FROM restaurants
  WHERE restaurants.owner_id = ?
  LIMIT 1
");
$stmt->execute([$user_id]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
  echo "<div style='text-align:center;margin-top:50px;font-family:Tajawal'>🚫 لا يوجد مطعم مرتبط بحسابك.</div>";
  exit;
}

// ✅ تأكيد id (عشان لا يكون فاضي)
$restaurant_id = (int)($restaurant['id'] ?? 0);
if ($restaurant_id <= 0) {
  echo "<div style='text-align:center;margin-top:50px;font-family:Tajawal;color:red'>❌ خطأ: لم يتم العثور على ID المطعم بشكل صحيح.</div>";
  exit;
}

// ✅ تقسيم ساعات العمل الحالية (إن وجدت)
$working_hours_raw = (string)($restaurant['working_hours'] ?? '');
$working_hours = explode(' - ', $working_hours_raw);
$open_time = trim($working_hours[0] ?? '');
$close_time = trim($working_hours[1] ?? '');

// ✅ مسار عرض الشعار (لا نعرضه إذا الملف غير موجود فعلاً)
$logo_file = (string)($restaurant['logo'] ?? '');
$logo_url = '';
if ($logo_file !== '') {
  // هذا المسار URL حسب مشروعك grad_project
  $public_url = "/grad_project/backend/public/uploads/restaurants/" . rawurlencode($logo_file);

  // وهذا مسار فعلي على الهارد للتحقق من وجود الملف
  $fs_path = realpath(__DIR__ . "/../backend/public/uploads/restaurants/") . DIRECTORY_SEPARATOR . $logo_file;

  if ($fs_path && file_exists($fs_path)) {
    $logo_url = $public_url;
  } else {
    // الملف غير موجود فعلاً (DB فيه اسم لكن المجلد فاضي عندك)
    $logo_url = '';
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تعديل بيانات المطعم - زاد</title>
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

    .main-content {
      padding: 30px;
      min-height: 100vh;
    }

    .content-card {
      background: var(--white);
      border-radius: 20px;
      padding: 40px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
      border: 1px solid rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
    }

    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
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

    .form-section {
      margin-bottom: 40px;
    }

    .section-title {
      color: var(--main-color);
      font-weight: 700;
      font-size: 1.4rem;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 2px solid rgba(198, 163, 79, 0.2);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-label {
      color: var(--main-color);
      font-weight: 600;
      margin-bottom: 10px;
      font-size: 0.95rem;
    }

    .form-control,
    .form-select {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 12px 16px;
      font-size: 0.95rem;
      transition: var(--transition);
      background: var(--white);
    }

    .form-control:focus,
    .form-select:focus {
      border-color: var(--gold);
      box-shadow: 0 0 0 0.25rem rgba(198, 163, 79, 0.15);
    }

    .input-group-text {
      background: linear-gradient(135deg, var(--main-color) 0%, #152622 100%);
      color: white;
      border: none;
      border-radius: 12px;
    }

    .image-upload-container {
      position: relative;
      border: 2px dashed #dee2e6;
      border-radius: 16px;
      padding: 30px;
      text-align: center;
      transition: var(--transition);
      background: #f8f9fa;
      cursor: pointer;
    }

    .image-upload-container:hover {
      border-color: var(--gold);
      background: rgba(198, 163, 79, 0.05);
    }

    .image-upload-container.dragover {
      border-color: var(--gold);
      background: rgba(198, 163, 79, 0.1);
    }

    .upload-icon {
      font-size: 3rem;
      color: var(--gold);
      margin-bottom: 15px;
    }

    .image-preview {
      max-width: 200px;
      max-height: 200px;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      border: 3px solid var(--white);
      display: none;
    }

    .time-inputs {
      display: flex;
      gap: 15px;
      align-items: end;
    }

    .time-input-group {
      flex: 1;
    }

    .time-separator {
      color: var(--gold);
      font-weight: bold;
      font-size: 1.2rem;
      padding-bottom: 12px;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--main-color) 0%, #152622 100%);
      border: none;
      border-radius: 12px;
      padding: 14px 30px;
      font-weight: 600;
      font-size: 1rem;
      transition: var(--transition);
      box-shadow: 0 4px 15px rgba(28, 51, 47, 0.2);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(28, 51, 47, 0.3);
    }

    .btn-outline-primary {
      border: 2px solid var(--main-color);
      color: var(--main-color);
      border-radius: 12px;
      padding: 12px 25px;
      font-weight: 600;
      transition: var(--transition);
    }

    .btn-outline-primary:hover {
      background: var(--main-color);
      color: white;
      transform: translateY(-2px);
    }

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
        padding: 30px;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .time-inputs {
        flex-direction: column;
        gap: 10px;
      }

      .time-separator {
        text-align: center;
        padding: 0;
      }
    }

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
  <?php include __DIR__ . '/includes/sidebar_owner.php'; ?>

  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <div class="main-content">
    <div class="container-fluid">

      <div class="page-header">
        <div class="d-flex align-items-center">
          <button class="sidebar-toggle me-3" id="sidebarToggle">
            <i class="fas fa-bars"></i>
          </button>
          <h1 class="page-title">
            <i class="fas fa-store"></i>
            تعديل بيانات المطعم
          </h1>
        </div>
      </div>

      <div class="content-card">
        <form id="editRestaurantForm" enctype="multipart/form-data" method="post">
          <!-- ✅ أهم شيء: ID صحيح -->
          <input type="hidden" name="id" value="<?= htmlspecialchars((string)$restaurant_id, ENT_QUOTES, 'UTF-8') ?>">

          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-info-circle text-gold"></i>
              المعلومات الأساسية
            </h3>

            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label">اسم المطعم <span class="text-danger">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-store"></i></span>
                  <input type="text" name="name"
                    value="<?= htmlspecialchars((string)($restaurant['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    class="form-control" required placeholder="أدخل اسم المطعم">
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">رقم الهاتف</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                  <input type="text" name="phone"
                    value="<?= htmlspecialchars((string)($restaurant['phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    class="form-control" placeholder="أدخل رقم الهاتف">
                </div>
              </div>

              <div class="col-12">
                <label class="form-label">العنوان</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                  <input type="text" name="address"
                    value="<?= htmlspecialchars((string)($restaurant['address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                    class="form-control" placeholder="أدخل عنوان المطعم">
                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-clock text-gold"></i>
              ساعات العمل
            </h3>

            <div class="row">
              <div class="col-md-8">
                <div class="time-inputs">
                  <div class="time-input-group">
                    <label class="form-label">وقت الفتح</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                      <input type="time" name="open_time"
                        value="<?= htmlspecialchars($open_time, ENT_QUOTES, 'UTF-8') ?>"
                        class="form-control">
                    </div>
                  </div>

                  <div class="time-separator">إلى</div>

                  <div class="time-input-group">
                    <label class="form-label">وقت الإغلاق</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="fas fa-door-closed"></i></span>
                      <input type="time" name="close_time"
                        value="<?= htmlspecialchars($close_time, ENT_QUOTES, 'UTF-8') ?>"
                        class="form-control">
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <div class="form-section">
            <h3 class="section-title">
              <i class="fas fa-image text-gold"></i>
              شعار المطعم
            </h3>

            <div class="row">
              <div class="col-md-6">
                <div class="image-upload-container" id="imageUploadContainer">
                  <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                  </div>
                  <h5>اسحب وأفلت الصورة هنا</h5>
                  <p class="text-muted">أو انقر لاختيار صورة</p>
                  <p class="small text-muted">(PNG, JPG - الحد الأقصى 5MB)</p>

                  <input type="file" name="logo" class="d-none" accept="image/*" id="logoInput">

                  <img
                    src="<?= $logo_url !== '' ? htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8') : '' ?>"
                    class="image-preview rounded shadow-sm"
                    id="imagePreview"
                    style="object-fit:cover; <?= $logo_url === '' ? 'display:none;' : 'display:block;' ?>">
                </div>
              </div>

              <div class="col-md-6">
                <div class="alert alert-info border-0 rounded-12">
                  <h6><i class="fas fa-lightbulb me-2"></i>نصائح للشعار:</h6>
                  <ul class="mb-0 mt-2 small">
                    <li>استخدم صورة ذات خلفية شفافة أو بيضاء</li>
                    <li>يفضل أن تكون الصورة مربعة الشكل</li>
                    <li>الحجم المثالي 500x500 بيكسل</li>
                    <li>تأكد من وضوح الشعار وجودته</li>
                  </ul>
                </div>

                <?php if ($logo_url !== ''): ?>
                  <div class="alert alert-warning border-0 rounded-12 mt-3">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>الشعار الحالي:</h6>
                    <p class="mb-0 small">سيتم استبدال الشعار الحالي بالشعار الجديد عند الحفظ.</p>
                  </div>
                <?php else: ?>
                  <div class="alert alert-secondary border-0 rounded-12 mt-3">
                    <h6><i class="fas fa-info-circle me-2"></i>ملاحظة:</h6>
                    <p class="mb-0 small">لا يوجد شعار محفوظ أو أن الملف غير موجود على السيرفر (عشان كذا كان يطلع 404).</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="form-section text-start mt-5">
            <div class="d-flex gap-3 flex-wrap">
              <button type="submit" class="btn btn-primary px-5">
                <i class="fas fa-save me-2"></i>حفظ التعديلات
              </button>
              <button type="button" class="btn btn-outline-primary px-4" onclick="resetForm()">
                <i class="fas fa-undo me-2"></i>إعادة تعيين
              </button>
              <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                <i class="fas fa-times me-2"></i>إلغاء
              </a>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>

  <div class="position-fixed bottom-0 start-0 p-4" style="z-index: 1080">
    <div id="toastContainer"></div>
  </div>

  <script>
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const imageUploadContainer = document.getElementById('imageUploadContainer');
    const logoInput = document.getElementById('logoInput');
    const imagePreview = document.getElementById('imagePreview');

    function toggleSidebar() {
      if (!sidebar) return;
      sidebar.classList.toggle('active');
      sidebarOverlay.classList.toggle('active');
      document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }

    if (sidebarToggle) sidebarToggle.addEventListener('click', toggleSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', toggleSidebar);

    imageUploadContainer.addEventListener('click', () => logoInput.click());

    logoInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 5 * 1024 * 1024) {
          showToast('❌ حجم الصورة كبير جداً. الحد الأقصى 5MB', 'error');
          return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
          imagePreview.src = ev.target.result;
          imagePreview.style.display = 'block';
          imageUploadContainer.querySelector('h5').textContent = 'تم اختيار الصورة';
          imageUploadContainer.querySelector('p').textContent = 'انقر لتغيير الصورة';
        };
        reader.readAsDataURL(file);
      }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      imageUploadContainer.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
      imageUploadContainer.addEventListener(eventName, () => {
        imageUploadContainer.classList.add('dragover');
      }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
      imageUploadContainer.addEventListener(eventName, () => {
        imageUploadContainer.classList.remove('dragover');
      }, false);
    });

    imageUploadContainer.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      logoInput.files = files;
      logoInput.dispatchEvent(new Event('change'));
    }

    function resetForm() {
      if (confirm('هل تريد إعادة تعيين جميع الحقول إلى القيم الأصلية؟')) {
        document.getElementById('editRestaurantForm').reset();
        showToast('🔄 تم إعادة تعيين النموذج', 'warning');
      }
    }

    $(document).ready(function() {
      $('#editRestaurantForm').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>جاري الحفظ...');

        const formData = new FormData(this);

        const openTime = $('input[name="open_time"]').val();
        const closeTime = $('input[name="close_time"]').val();
        formData.set('working_hours', `${openTime} - ${closeTime}`);
        const fileObj = $('#logoInput')[0].files[0];
        console.log("FILE =>", fileObj ? (fileObj.name + " | " + fileObj.size) : "NO FILE SELECTED");

        // ✅ Debug سريع
        console.log("ID =>", formData.get('id'));
        console.log("NAME =>", formData.get('name'));
        console.log("HOURS =>", formData.get('working_hours'));

        $.ajax({
          url: 'api/update_restaurant.php',
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          dataType: 'json',
          success: function(response) {
            if (response.status === 'success') {
              showToast('✅ ' + response.message, 'success');
              setTimeout(() => window.location.href = 'dashboard.php', 1200);
            } else {
              showToast('⚠️ ' + (response.message || 'حدث خطأ غير معروف'), 'warning');
              submitBtn.prop('disabled', false).html(originalText);
            }
          },
          error: function(xhr) {
            console.log("RAW RESPONSE =>", xhr.responseText);
            showToast('❌ فشل الحفظ، افتح Console لمعرفة السبب الحقيقي', 'error');
            submitBtn.prop('disabled', false).html(originalText);
          }
        });
      });
    });

    function showToast(message, type) {
      const colorClass = {
        success: 'toast-success',
        error: 'toast-error',
        warning: 'toast-warning'
      } [type] || 'toast-success';

      const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle'
      } [type] || 'fa-info-circle';

      const toast = $(`
        <div class="toast custom-toast ${colorClass} show mb-3" role="alert">
          <div class="toast-header border-0">
            <i class="fas ${icon} me-2"></i>
            <strong class="me-auto">إشعار</strong>
            <small>الآن</small>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
          </div>
          <div class="toast-body">${message}</div>
        </div>
      `);
      $('#toastContainer').append(toast);
      setTimeout(() => toast.remove(), 5000);
    }

    if (window.innerWidth <= 992 && sidebar) {
      document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
          if (sidebar.classList.contains('active')) toggleSidebar();
        });
      });
    }

    window.addEventListener('resize', function() {
      if (window.innerWidth > 992 && sidebar && sidebar.classList.contains('active')) {
        toggleSidebar();
      }
    });
  </script>
</body>

</html>