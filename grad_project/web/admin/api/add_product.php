<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../../../config/database.php';


try {
    $pdo = DatabaseConfig::getConnection();


    // ---------------------------
    // 🔐 تحقق من المدخلات
    // ---------------------------
    $restaurant_id = $_POST['restaurant_id'] ?? null;
    $name          = trim($_POST['name'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $price         = (float)($_POST['price'] ?? 0);
    $sale_price    = isset($_POST['sale_price']) ? (float)$_POST['sale_price'] : null;
    $quantity      = isset($_POST['quantity']) ? (int)$_POST['quantity'] : -1;


    if (!$restaurant_id || $name === '' || $price <= 0) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'بيانات المنتج غير مكتملة'
        ]);
        exit;
    }


    if ($quantity < 0) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'يرجى إدخال كمية صحيحة'
        ]);
        exit;
    }


    // ---------------------------
    // 📷 رفع الصورة (إن وجدت)
    // ---------------------------
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo = uniqid('product_') . '.' . $ext;


        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            __DIR__ . '/../../../../public/uploads/products/' . $photo
        );
    }


    // ---------------------------
    // 🧾 إدخال المنتج
    // ---------------------------
    $stmt = $pdo->prepare("
        INSERT INTO products
        (restaurant_id, name, description, price, sale_price, quantity, photo)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");


    $stmt->execute([
        $restaurant_id,
        $name,
        $description,
        $price,
        $sale_price,
        $quantity,
        $photo
    ]);


    echo json_encode([
        'status'  => 'success',
        'message' => 'تم إضافة المنتج بنجاح'
    ]);


} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'خطأ في السيرفر'
    ]);
}
