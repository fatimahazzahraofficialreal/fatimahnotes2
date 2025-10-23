<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';
$id = intval($_POST['id'] ?? 0);
$dir = "../uploads/videos/"; if(!is_dir($dir)) mkdir($dir,0777,true);
if(!isset($_FILES['video'])) { echo json_encode(["success"=>false,"error"=>"no file"]); exit; }
$fn = time().'_'.preg_replace('/\s+/', '_', basename($_FILES['video']['name']));
$path = $dir.$fn;
if(move_uploaded_file($_FILES['video']['tmp_name'], $path)){
  $rel = "uploads/videos/".$fn;
  // setelah UPDATE entries SET video_path=...
$conn->query("UPDATE entries SET video_path='$rel', updated_at=NOW() WHERE id=$id");
$jid = $conn->query("SELECT journal_id FROM entries WHERE id=$id")->fetch_assoc()['journal_id'] ?? 0;
if ($jid) { $conn->query("UPDATE journals SET updated_at = NOW() WHERE id = ".intval($jid)); }
}else{
  echo json_encode(["success"=>false,"error"=>"upload failed"]);
}
