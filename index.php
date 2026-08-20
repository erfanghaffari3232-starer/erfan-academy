<?php
$room = isset($_GET['room']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['room']) : 'main';
if ($room === '') $room = 'main';
header('Location: board.php?room=' . urlencode($room));
exit;
?>