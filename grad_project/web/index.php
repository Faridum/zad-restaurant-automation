<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>زاد - تجربة إدارة المطاعم الذكية</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">

  <!-- Frameworks -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    :root {
      --main-color: #1C332F;
      --main-light: #2D4F48;
      --gold: #C6A34F;
      --gold-light: #E5C56F;
      --accent-white: #f8f9fa;
    }

    body {
      font-family: 'Tajawal', sans-serif;
      background-color: var(--accent-white);
      color: var(--main-color);
      overflow-x: hidden;
      cursor: default;
    }

    /* ✨ Animated Background Particles Canvas */
    #particles-js {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      z-index: 1;
    }

    /* 🌊 Moving Waves Divider */
    .wave-container {
      position: relative;
      width: 100%;
      height: 100px;
      margin-top: -100px;
      z-index: 5;
    }

    .waves {
      position: relative;
      width: 100%;
      height: 15vh;
      margin-bottom: -7px;
      min-height: 100px;
      max-height: 150px;
    }

    .parallax>use {
      animation: move-forever 25s cubic-bezier(.55, .5, .45, .5) infinite;
    }

    .parallax>use:nth-child(1) {
      animation-delay: -2s;
      animation-duration: 7s;
    }

    .parallax>use:nth-child(2) {
      animation-delay: -3s;
      animation-duration: 10s;
    }

    .parallax>use:nth-child(3) {
      animation-delay: -4s;
      animation-duration: 13s;
    }

    .parallax>use:nth-child(4) {
      animation-delay: -5s;
      animation-duration: 20s;
    }

    @keyframes move-forever {
      0% {
        transform: translate3d(-90px, 0, 0);
      }

      100% {
        transform: translate3d(85px, 0, 0);
      }
    }

    /* 📱 Navbar Enhancements */
    .navbar {
      background: rgba(28, 51, 47, 0.85);
      backdrop-filter: blur(15px);
      padding: 15px 0;
      border-bottom: 1px solid rgba(198, 163, 79, 0.2);
    }

    /* 🌟 Hero Section Animation */
    .hero {
      position: relative;
      background: linear-gradient(-45deg, #1C332F, #2D4F48, #152623, #1C332F);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
      min-height: 100vh;
      display: flex;
      align-items: center;
      padding-top: 100px;
      color: white;
    }

    @keyframes gradientBG {
      0% {
        background-position: 0% 50%;
      }

      50% {
        background-position: 100% 50%;
      }

      100% {
        background-position: 0% 50%;
      }
    }

    /* 📱 Phone Mockup Styling */
    .phone-mockup {
      position: relative;
      width: 280px;
      height: 580px;
      margin: 0 auto;
      border: 12px solid #222;
      border-radius: 40px;
      background: #000;
      box-shadow: 0 50px 100px rgba(0, 0, 0, 0.5), 0 0 20px rgba(198, 163, 79, 0.3);
      overflow: hidden;
      z-index: 10;
      transition: transform 0.5s ease;
    }

    .phone-mockup:hover {
      transform: scale(1.05) rotate(-2deg);
    }

    .phone-mockup img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .phone-notch {
      position: absolute;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 120px;
      height: 25px;
      background: #222;
      border-bottom-left-radius: 15px;
      border-bottom-right-radius: 15px;
      z-index: 11;
    }

    .floating {
      animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
      0% {
        transform: translatey(0px);
      }

      50% {
        transform: translatey(-20px);
      }

      100% {
        transform: translatey(0px);
      }
    }

    /* 📢 Animated Ad Cards */
    .pro-banner {
      position: relative;
      height: 350px;
      border-radius: 30px;
      overflow: hidden;
      transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      z-index: 1;
    }

    .pro-banner:hover {
      transform: scale(1.03) rotate(1deg);
    }

    .glass-effect {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Feature Cards Animation */
    .feature-card {
      border: none;
      background: white;
      border-radius: 20px;
      padding: 40px 20px;
      transition: all 0.4s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
    }

    .feature-card::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: var(--gold);
      transition: 0.4s ease;
      z-index: -1;
      opacity: 0.1;
    }

    .feature-card:hover::after {
      height: 100%;
    }

    .feature-card:hover .feature-icon {
      transform: rotateY(360deg);
      transition: 0.8s;
    }

    /* Gold Buttons with Shine */
    .btn-gold {
      background: linear-gradient(45deg, var(--gold), var(--gold-light));
      border: none;
      color: var(--main-color);
      font-weight: 700;
      position: relative;
      overflow: hidden;
      transition: 0.3s;
    }

    .btn-gold::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: rgba(255, 255, 255, 0.2);
      transform: rotate(45deg);
      transition: 0.5s;
      left: -100%;
    }

    .btn-gold:hover::before {
      left: 100%;
    }

    /* 📝 Enhanced Modal & Form Styling */
    .modal-content {
      border-radius: 30px;
      border: none;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
      overflow: hidden;
    }

    .modal-header {
      background: var(--main-color);
      color: white;
      border-bottom: 2px solid var(--gold);
      padding: 25px;
    }

    .form-control {
      border-radius: 12px;
      padding: 12px 15px;
      background-color: #f8f9fa;
      border: 1px solid #e0e0e0;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      background-color: #fff;
      border-color: var(--gold);
      box-shadow: 0 0 0 0.25rem rgba(198, 163, 79, 0.15);
      transform: translateY(-2px);
    }

    .upload-box {
      border: 2px dashed #ddd;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
      transition: 0.3s;
      background: #fdfdfd;
      cursor: pointer;
    }

    .upload-box:hover {
      border-color: var(--gold);
      background: rgba(198, 163, 79, 0.05);
    }

    .proof-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .proof-preview img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid var(--gold);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2.2rem;
      }

      .floating {
        animation: none;
      }
    }
  </style>
</head>

<body>

  <!-- 🔝 Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <a class="navbar-brand fw-bolder fs-3" href="#" style="color: var(--gold);">زاد <i class="fas fa-seedling ms-1"></i></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link px-3" href="#home">الرئيسية</a></li>
          <li class="nav-item"><a class="nav-link px-3" href="#ads">العروض</a></li>
          <li class="nav-item"><a class="nav-link px-3" href="#features">المميزات</a></li>
          <li class="nav-item"><a href="admin/login.php" class="nav-link btn btn-outline-light px-3 ms-2">دخول التاجر</a></li>
          <li class="nav-item ms-lg-4 mt-3 mt-lg-0">
            <button class="btn btn-gold px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#registerModal">اشترك الآن</button>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 🌟 HERO Section -->
  <section id="home" class="hero">
    <div id="particles-js"></div>
    <div class="container position-relative" style="z-index: 2;">
      <div class="row align-items-center">
        <div class="col-lg-6" data-aos="fade-left">
          <!-- <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill shadow-sm">تطبيق الإدارة الأول</span> -->
          <h1 class="display-3 fw-bold mb-4" style="line-height: 1.2;">راقب مطعمك وادر طلباتك <span style="color: var(--gold);">بسهولة</span></h1>
          <p class="lead mb-5 opacity-75">نظام "زاد" المتكامل يوفر لك لوحة تحكم ذكية لمتابعة كل صغيرة وكبيرة في منشأتك من أي مكان في العالم.</p>
          <div class="d-flex gap-3 flex-wrap">
            <button class="btn btn-gold btn-lg px-5 py-3 shadow-lg" data-bs-toggle="modal" data-bs-target="#registerModal">سجل منشأتك الآن</button>
          </div>
        </div>
        <div class="col-lg-6 mt-5 mt-lg-0">
          <div class="floating">
            <div class="phone-mockup">
              <div class="phone-notch"></div>
              <img src="assets/images/zad-app-interface.png" alt="Zad App Interface">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 🌊 Wave Divider -->
  <div class="wave-container">
    <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
      <defs>
        <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
      </defs>
      <g class="parallax">
        <use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(248, 249, 250, 0.7)" />
        <use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(248, 249, 250, 0.5)" />
        <use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(248, 249, 250, 0.3)" />
        <use xlink:href="#gentle-wave" x="48" y="7" fill="#f8f9fa" />
      </g>
    </svg>
  </div>

  <!-- 📢 Ads Section -->
  <section id="ads" class="py-5">
    <div class="container py-5">
      <div class="row g-4">
        <div class="col-md-4" data-aos="zoom-in-up">
          <div class="pro-banner shadow-lg d-flex flex-column justify-content-center p-5 text-center text-white" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://placehold.co/600x800/1C332F/FFF?text=Growth'); background-size: cover;">
            <h2 class="fw-bold mb-3">ضاعف مبيعاتك</h2>
            <p>أدوات تسويقية مدمجة للوصول لعملاء أكثر بضغطة زر.</p>
            <div class="mt-3"><span class="btn btn-gold rounded-pill px-4">اكتشف المزيد</span></div>
          </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in-up" data-aos-delay="100">
          <div class="pro-banner shadow-lg d-flex flex-column justify-content-center p-5 text-center text-white glass-effect" style="background: #1C332F;">
            <i class="fas fa-mobile-alt mb-4 display-4" style="color: var(--gold);"></i>
            <h2 class="fw-bold mb-3">تطبيق زاد</h2>
            <p> تطبيقنا اصبح متاح على آيفون وأندرويد.</p>
            <div class="d-flex justify-content-center gap-2 mt-3">
              <i class="fab fa-apple fa-2x"></i>
              <i class="fab fa-google-play fa-2x"></i>
            </div>
          </div>
        </div>
        <div class="col-md-4" data-aos="zoom-in-up" data-aos-delay="200">
          <div class="pro-banner shadow-lg d-flex flex-column justify-content-center p-5 text-center text-white" style="background: linear-gradient(45deg, #C6A34F, #1C332F);">
            <div class="display-1 fw-bold opacity-25" style="position: absolute; top: -10px; right: 10px;">%25</div>
            <h2 class="fw-bold mb-3">خصم التأسيس</h2>
            <p>اشترك الآن واحصل على شهرين مجاناً .</p>
            <div class="mt-3"><span class="btn btn-light rounded-pill px-4">احجز موعدك</span></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- 💡 Features Section -->
  <section id="features" class="py-5 bg-white position-relative">
    <div class="container py-5 text-center">
      <h6 class="text-uppercase fw-bold mb-3" style="color: var(--gold); letter-spacing: 2px;">لماذا نحن الأفضل؟</h6>
      <h2 class="display-5 fw-bold mb-5">مميزات صُممت لراحتك</h2>

      <div class="row g-4 mt-2">
        <div class="col-md-3" data-aos="fade-up">
          <div class="feature-card shadow-sm">
            <div class="feature-icon mb-4"><i class="fas fa-rocket fa-3x" style="color: var(--gold);"></i></div>
            <h4 class="fw-bold mb-3">سرعة فائقة</h4>
            <p class="text-muted small">معالجة آلاف الطلبات في الثواني دون أي تأخير تقني.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
          <div class="feature-card shadow-sm">
            <div class="feature-icon mb-4"><i class="fas fa-headset fa-3x" style="color: var(--gold);"></i></div>
            <h4 class="fw-bold mb-3">دعم 24/7</h4>
            <p class="text-muted small">فريقنا معك دائماً لحل أي تحدي يواجهك في أي وقت.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
          <div class="feature-card shadow-sm">
            <div class="feature-icon mb-4"><i class="fas fa-cloud fa-3x" style="color: var(--gold);"></i></div>
            <h4 class="fw-bold mb-3">سحابة آمنة</h4>
            <p class="text-muted small">بياناتك مشفرة ومحفوظة في أفضل الخوادم العالمية.</p>
          </div>
        </div>
        <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
          <div class="feature-card shadow-sm">
            <div class="feature-icon mb-4"><i class="fas fa-magic fa-3x" style="color: var(--gold);"></i></div>
            <h4 class="fw-bold mb-3">سهولة الاستخدام</h4>
            <p class="text-muted small">واجهات بسيطة لا تحتاج لأكثر من 5 دقائق لتدريب موظفيك.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-5 text-white" style="background: #152623;">
    <div class="container text-center">
      <div class="mb-4">
        <a href="#" class="text-white mx-2 fs-4"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-white mx-2 fs-4"><i class="fab fa-x-twitter"></i></a>
        <a href="#" class="text-white mx-2 fs-4"><i class="fab fa-whatsapp"></i></a>
      </div>
      <p class="opacity-50 small mb-0">نظام زاد المتكامل © 2025. صُنع بكل حب لدعم المطاعم العربية.</p>
    </div>
  </footer>

  <!-- ✅ Full Register Form Modal (Restored & Improved) -->
  <div class="modal fade" id="registerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class="fas fa-store me-2" style="color: var(--gold);"></i> طلب تسجيل منشأة جديدة</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4 p-md-5">
          <form id="registerForm" method="post" action="process_registration.php" enctype="multipart/form-data">
            <div class="row g-4">
              <!-- Owner Info -->
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">اسم المالك</label>
                <input type="text" name="owner_name" class="form-control" placeholder="الاسم الثلاثي" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">رقم الجوال</label>
                <input
                  type="tel"
                  name="phone"
                  class="form-control"
                  placeholder="05 / 09 / 01xxxxxxxx"
                  required
                  pattern="^(09|05|01)[0-9]{8}$"
                  maxlength="10"
                  inputmode="numeric"
                  style="direction: ltr; text-align: right;"
                  title="رقم الجوال يجب أن يبدأ بـ 05 أو 09 أو 01 ويتكون من 10 أرقام">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">البريد الإلكتروني</label>
                <input
                  type="email"
                  name="email"
                  class="form-control"
                  placeholder="name@example.com"
                  required
                  title="يرجى إدخال بريد إلكتروني صحيح">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">كلمة المرور</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
              </div>

              <div class="col-12">
                <hr class="my-2 opacity-50">
              </div>

              <!-- Restaurant Info -->
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">اسم المطعم / المقهى</label>
                <input type="text" name="restaurant_name" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted">المدينة / العنوان</label>
                <input type="text" name="address" class="form-control" required>
              </div>

              <!-- Files Upload -->
              <div class="col-12">
                <label class="form-label fw-bold small text-muted">المستندات الرسمية (سجل تجاري / رخصة / هوية)</label>
                <div class="upload-box position-relative">
                  <i class="fa-solid fa-cloud-arrow-up fs-2 text-muted mb-2"></i>
                  <p class="small text-muted mb-0">اضغط لرفع المستندات (صور أو PDF)</p>
                  <input type="file" name="proofs[]" id="proofsInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept="image/*,.pdf" multiple required>
                </div>
                <div id="proofPreview" class="proof-preview"></div>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" class="btn btn-gold w-100 py-3 rounded-pill fw-bold shadow">
                  إرسال طلب التسجيل <i class="fas fa-paper-plane ms-2"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

  <script>
    AOS.init({
      once: true
    });

    // 🎇 Particles JS
    particlesJS("particles-js", {
      "particles": {
        "number": {
          "value": 80,
          "density": {
            "enable": true,
            "value_area": 800
          }
        },
        "color": {
          "value": ["#C6A34F", "#ffffff"]
        },
        "shape": {
          "type": "circle"
        },
        "opacity": {
          "value": 0.5,
          "random": true
        },
        "size": {
          "value": 3,
          "random": true
        },
        "line_linked": {
          "enable": true,
          "distance": 150,
          "color": "#C6A34F",
          "opacity": 0.2,
          "width": 1
        },
        "move": {
          "enable": true,
          "speed": 2,
          "direction": "none",
          "random": false,
          "straight": false,
          "out_mode": "out",
          "bounce": false
        }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": {
            "enable": true,
            "mode": "grab"
          },
          "onclick": {
            "enable": true,
            "mode": "push"
          },
          "resize": true
        }
      },
      "retina_detect": true
    });

    // ✅ Image Preview Logic
    document.getElementById('proofsInput').addEventListener('change', function() {
      const preview = document.getElementById('proofPreview');
      preview.innerHTML = '';
      const files = this.files;
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
          };
          reader.readAsDataURL(file);
        } else {
          // If PDF or other
          const span = document.createElement('span');
          span.className = 'badge bg-dark p-2 rounded-3';
          span.innerHTML = `<i class="fas fa-file-pdf me-1"></i> ${file.name.substring(0, 10)}...`;
          preview.appendChild(span);
        }
      }
    });
  </script>
  <script>
    $('#registerForm').on('submit', function(e) {
      e.preventDefault(); // 🔴 مهم جدًا

      const form = document.getElementById('registerForm');
      const formData = new FormData(form);

      // تعطيل الزر لمنع التكرار
      const btn = $(form).find('button[type="submit"]');
      btn.prop('disabled', true).html('⏳ جاري الإرسال...');

      $.ajax({
        url: 'admin/api/register_store.php', // أو process_registration.php
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function(res) {
          if (res.status === 'success') {

            // إغلاق مودال التسجيل
            const modalEl = document.getElementById('registerModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // تنظيف الفورم
            form.reset();
            $('#proofPreview').html('');

            // تنبيه بسيط (أو مودال نجاح لو تحب)
            setTimeout(() => {
              alert('🎉 تم إرسال طلبك بنجاح، سيتم التواصل معك قريبًا');
            }, 400);

          } else {
            alert('❌ ' + res.message);
          }
        },
        error: function(xhr) {
          console.error(xhr.responseText);
          alert('⚠️ حدث خطأ أثناء الإرسال');
        },
        complete: function() {
          btn.prop('disabled', false).html('إرسال طلب التسجيل <i class="fas fa-paper-plane ms-2"></i>');
        }
      });
    });
  </script>
  <script>
    document.querySelector('input[name="phone"]').addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });
  </script>

</body>

</html>