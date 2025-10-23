<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';

$sql = "
SELECT 
  j.id,
  j.title,
  j.color,
  j.created_at,
  j.updated_at,
  -- aktivitas terakhir: edit jurnal atau entry
  GREATEST(
    COALESCE(j.updated_at, '1970-01-01'),
    j.created_at,
    COALESCE(MAX(e.updated_at), '1970-01-01'),
    COALESCE(MAX(e.created_at), '1970-01-01')
  ) AS last_activity
FROM journals j
LEFT JOIN entries e ON e.journal_id = j.id
GROUP BY j.id, j.title, j.color, j.created_at, j.updated_at
ORDER BY last_activity DESC;
";

$rs = $conn->query($sql);
$out = [];
while ($row = $rs->fetch_assoc()) $out[] = $row;
echo json_encode($out, JSON_UNESCAPED_UNICODE);
