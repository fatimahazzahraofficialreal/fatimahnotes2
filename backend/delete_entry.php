<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id'] ?? 0);

if (!$id) {
  echo json_encode(['success'=>false,'error'=>'missing id']);
  exit;
}

// cari journal_id biar bisa update waktu terakhir edit
$res = $conn->query("SELECT journal_id FROM entries WHERE id=$id");
$jid = $res && $res->num_rows ? intval($res->fetch_assoc()['journal_id']) : 0;

// hapus entry
$ok = $conn->query("DELETE FROM entries WHERE id=$id");

if ($ok && $jid) {
  $conn->query("UPDATE journals SET updated_at=NOW() WHERE id=$jid");
}

echo json_encode(['success'=>$ok, 'error'=>$ok?null:$conn->error]);
