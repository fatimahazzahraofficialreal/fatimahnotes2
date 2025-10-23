<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0);

if (!$id) {
  echo json_encode(['success'=>false,'error'=>'missing id']);
  exit;
}

// Hapus semua entry di jurnal ini
$conn->query("DELETE FROM entries WHERE journal_id = $id");

// Hapus jurnal itu sendiri
$ok = $conn->query("DELETE FROM journals WHERE id = $id");

echo json_encode(['success'=>$ok, 'error'=>$ok?null:$conn->error]);
