<?php
// إعداد SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// تعطيل أي buffering
@ini_set('output_buffering', 'off');
@ini_set('zlib.output_compression', false);
@ob_end_flush();
@ob_implicit_flush(true);

require_once __DIR__ . '/../includes/db.php';
ignore_user_abort(true);

// قراءة آخر timestamp (أول مرة أو من GET)
$since = isset($_GET['since']) ? $_GET['since'] : null;

if (!$since) {
  $since = gmdate('Y-m-d H:i:s');
  echo "id: $since\n";
  echo "event: init\n";
  echo "data: " . json_encode(["message" => "connected"]) . "\n\n";
  flush();
}

$timeout = 15; // مدة الانتظار
$start = time();

while (!connection_aborted() && (time() - $start < $timeout)) {
  $stmt = $pdo->prepare("SELECT id, type, product_id, created_at 
                         FROM updates 
                         WHERE created_at > ? 
                         ORDER BY created_at ASC");
  $stmt->execute([$since]);
  $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!empty($updates)) {
    foreach ($updates as $u) {
      $eventId = $u['created_at'];
      echo "id: {$eventId}\n";
      echo "event: update\n";
      echo "data: " . json_encode($u, JSON_UNESCAPED_UNICODE) . "\n\n";
      flush();
      $since = $eventId;
    }
    break;
  }

  echo ": ping\n\n"; // تعليق لإبقاء الاتصال مفتوحًا
  flush();
  usleep(800000); // 0.8 ثانية
}

exit();
