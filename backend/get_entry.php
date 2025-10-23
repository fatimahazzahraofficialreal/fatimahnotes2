<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';
$id = intval($_GET['id'] ?? 0);
$rs = $conn->query("SELECT * FROM entries WHERE id=$id");
echo json_encode($rs->num_rows? $rs->fetch_assoc(): ["error"=>"not found"]);
