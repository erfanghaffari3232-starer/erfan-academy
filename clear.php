<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/config.php';
$room=isset($_POST['room'])?preg_replace('/[^a-zA-Z0-9_-]/','',$_POST['room']):'';
if($room===''){http_response_code(400);echo json_encode(['ok'=>false]);exit;}
$stmt=$conn->prepare("INSERT INTO whiteboards (room,data) VALUES (?,'') ON DUPLICATE KEY UPDATE data='', updated_at=CURRENT_TIMESTAMP");
$stmt->bind_param('s',$room); echo json_encode(['ok'=>$stmt->execute()]);
$stmt->close();$conn->close();
?>