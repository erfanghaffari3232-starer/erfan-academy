<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
$room = isset($_GET['room']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['room']) : '';
if ($room === '') { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
$stmt=$conn->prepare("SELECT data, UNIX_TIMESTAMP(updated_at) updated_at FROM whiteboards WHERE room=? LIMIT 1");
$stmt->bind_param('s',$room); $stmt->execute(); $row=$stmt->get_result()->fetch_assoc();
echo json_encode(['ok'=>true,'data'=>$row?$row['data']:null,'updated_at'=>$row?(int)$row['updated_at']:0]);
$stmt->close(); $conn->close();
?>