<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$id      = intval($data['id'] ?? 0);
$title   = $conn->real_escape_string($data['title'] ?? 'Untitled');
$content = $conn->real_escape_string($data['content'] ?? '');
$mood    = $conn->real_escape_string($data['mood'] ?? '🙂');
$tags    = $conn->real_escape_string($data['tags'] ?? '');

$ok = $conn->query("UPDATE entries 
                    SET title='$title', content='$content', mood='$mood', tags='$tags', updated_at=NOW()
                    WHERE id=$id");

if ($ok) {
  // sentuh jurnal induknya
  $rs = $conn->query("SELECT journal_id FROM entries WHERE id=$id");
  if ($row = $rs->fetch_assoc()) {
    $jid = intval($row['journal_id']);
    $conn->query("UPDATE journals SET updated_at = NOW() WHERE id = $jid");
  }
}

echo json_encode(['success'=>$ok, 'error'=>$ok?null:$conn->error]);
