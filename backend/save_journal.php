<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$body = json_decode(file_get_contents('php://input'), true);
$id      = intval($body['id'] ?? 0);
$title   = $conn->real_escape_string($body['title'] ?? '');
$content = $conn->real_escape_string($body['content'] ?? '');
$color   = $conn->real_escape_string($body['color'] ?? '#E6D0B8');

if ($id > 0) {
  $ok = $conn->query("UPDATE journals 
                      SET title='$title', content='$content', color='$color', updated_at=NOW()
                      WHERE id=$id");
  echo json_encode(['success'=>$ok, 'id'=>$id, 'error'=>$ok?null:$conn->error]);
} else {
  $ok = $conn->query("INSERT INTO journals (title, content, color) 
                      VALUES ('$title', '$content', '$color')");
  echo json_encode(['success'=>$ok, 'id'=>$conn->insert_id, 'error'=>$ok?null:$conn->error]);
}
