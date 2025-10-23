<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';
$jid = intval($_GET['journal_id'] ?? 0);
$rs = $conn->query("SELECT * FROM entries WHERE journal_id=$jid ORDER BY created_at DESC");
$out=[]; while($r=$rs->fetch_assoc()) $out[]=$r;
echo json_encode($out);
