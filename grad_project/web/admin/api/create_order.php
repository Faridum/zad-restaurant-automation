<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['customer_id'], $input['owner_id'], $input['product_id'], $input['quantity'])) {
  echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
  exit;
}

$customer_id = (int)$input['customer_id'];
$owner_id = (int)$input['owner_id'];
$product_id = (int)$input['product_id'];
$quantity = (int)$input['quantity'];
$notes = isset($input['notes']) ? trim($input['notes']) : '';

try {
  // جلب سعر المنتج
  $statement_product = $pdo->prepare("SELECT price, sale_price FROM products WHERE id = ?");
  $statement_product->execute([$product_id]);
  $product_data = $statement_product->fetch(PDO::FETCH_ASSOC);

  if (!$product_data) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit;
  }

  $unit_price = $product_data['sale_price'] ?: $product_data['price'];
  $total_price = $unit_price * $quantity;

  // إضافة الطلب
  $statement_insert = $pdo->prepare("
    INSERT INTO orders (customer_id, owner_id, product_id, quantity, total_price, notes)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $statement_insert->execute([$customer_id, $owner_id, $product_id, $quantity, $total_price, $notes]);

  $order_id = $pdo->lastInsertId();

  // تسجيل تحديث في جدول updates
  $statement_update = $pdo->prepare("INSERT INTO updates (type, product_id) VALUES ('new_order', ?)");
  $statement_update->execute([$order_id]);

  echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (Exception $error) {
  echo json_encode(['status' => 'error', 'message' => $error->getMessage()]);
}
