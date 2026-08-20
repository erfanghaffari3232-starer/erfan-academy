<?php
$room = isset($_GET['room']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['room']) : 'main';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>وایت‌برد آنلاین</title><link rel="stylesheet" href="style.css">
</head><body>
<div class="topbar"><div class="title">📝 وایت‌برد آنلاین</div><div class="room">اتاق: <b id="roomName"><?=htmlspecialchars($room,ENT_QUOTES,'UTF-8')?></b></div><div class="tools">
<input id="color" type="color" value="#111111"><input id="size" type="range" min="1" max="30" value="4">
<button id="pen">✏️ قلم</button><button id="eraser">🧽 پاک‌کن</button><button id="clear">🗑️ پاک کردن</button><button id="save">💾 ذخیره</button>
</div></div><div class="status" id="status">در حال اتصال...</div><div class="canvas-wrap"><canvas id="board"></canvas></div>
<script>window.ROOM_ID=<?=json_encode($room,JSON_UNESCAPED_UNICODE)?>;</script><script src="app.js"></script>
</body></html>