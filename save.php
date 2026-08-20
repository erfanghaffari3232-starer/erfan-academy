<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
$room = isset($_POST['room']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['room']) : '';
$data = $_POST['data'] ?? '';
if ($room === '' || $data === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'اطلاعات ناقص است']); exit; }
$stmt = $conn->prepare("INSERT INTO whiteboards (room,data) VALUES (?,?) ON DUPLICATE KEY UPDATE data=VALUES(data), updated_at=CURRENT_TIMESTAMP");
$stmt->bind_param('ss',$room,$data);
$ok=$stmt->execute(); echo json_encode(['ok'=>$ok]);
$stmt->close(); $conn->close();
?>