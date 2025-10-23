<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$jid   = intval($_POST['journal_id'] ?? 0);
$title = $conn->real_escape_string($_POST['title'] ?? 'Untitled');

$ok = $conn->query("INSERT INTO entries (journal_id,title) VALUES ($jid,'$title')");
if ($ok) {
  $conn->query("UPDATE journals SET updated_at = NOW() WHERE id = $jid"); // sentuh jurnal
}
echo json_encode(['success'=>$ok, 'id'=>$conn->insert_id, 'error'=>$ok?null:$conn->error]);
