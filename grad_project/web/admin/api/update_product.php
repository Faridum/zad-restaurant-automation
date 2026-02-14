<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';


header('Content-Type: application/json; charset=UTF-8');


// السماح للمالك أو المدير فقط
if (!in_array($_SESSION['role'], ['owner', 'admin'], true)) {
    echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بالوصول.']);
    exit;
}


try {
    $id          = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name        = trim($_POST['name'] ?? '');
    $price       = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $sale_price  = strlen($_POST['sale_price'] ?? '') ? (float)$_POST['sale_price'] : null;
    $description = trim($_POST['description'] ?? '');
    $old_photo   = trim($_POST['old_photo'] ?? '');


    // 🆕 الكمية
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;


    if ($id <= 0 || $name === '' || $price === null) {
        echo json_encode(['status' => 'error', 'message' => 'بيانات غير مكتملة.']);
        exit;
    }


    // 🆕 تحقق من الكمية
    if ($quantity === null || $quantity < 0) {
        echo json_encode(['status' => 'error', 'message' => 'يرجى إدخال كمية صحيحة.']);
        exit;
    }


    // قراءة المنتج الحالي
    $stmt = $pdo->prepare("SELECT id, restaurant_id, photo FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$current) {
        echo json_encode(['status' => 'error', 'message' => 'المنتج غير موجود.']);
        exit;
    }


    // إن كان مالكًا: يجب أن يكون المنتج ضمن مطعمه
    if ($_SESSION['role'] === 'owner') {
        $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE owner_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);


        if (!$restaurant || (int)$restaurant['id'] !== (int)$current['restaurant_id']) {
            echo json_encode(['status' => 'error', 'message' => 'غير مصرح لك بتعديل هذا المنتج.']);
            exit;
        }
    }


    // رفع صورة جديدة (اختياري)
    $photo_name = $current['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }


        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];


        if (!in_array($ext, $allowed, true)) {
            echo json_encode(['status' => 'error', 'message' => 'صيغة الصورة غير مدعومة.']);
            exit;
        }


        $new_name = 'product_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $new_name)) {
            echo json_encode(['status' => 'error', 'message' => 'فشل رفع الصورة الجديدة.']);
            exit;
        }


        // حذف القديمة
        if ($photo_name && file_exists($upload_dir . $photo_name)) {
            @unlink($upload_dir . $photo_name);
        }


        $photo_name = $new_name;
    }


    // 🧾 تحديث المنتج
    if ($_SESSION['role'] === 'owner') {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, description = ?, price = ?, sale_price = ?, quantity = ?, photo = ?
            WHERE id = ? AND restaurant_id = ?
        ");
        $stmt->execute([
            $name,
            $description,
            $price,
            $sale_price,
            $quantity,     // 🆕
            $photo_name,
            $id,
            $current['restaurant_id']
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE products
            SET name = ?, description = ?, price = ?, sale_price = ?, quantity = ?, photo = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $description,
            $price,
            $sale_price,
            $quantity,     // 🆕
            $photo_name,
            $id
        ]);
    }


    // تسجيل تحديث لـ SSE
    $pdo->prepare(
        "INSERT INTO updates (type, product_id) VALUES ('update_product', ?)"
    )->execute([$id]);


    echo json_encode([
        'status'  => 'success',
        'message' => 'تم تعديل المنتج بنجاح ✏️'
    ]);


} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => 'حدث خطأ أثناء التعديل: ' . $e->getMessage()
    ]);
}


