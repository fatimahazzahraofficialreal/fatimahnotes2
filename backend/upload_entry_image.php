<?php
header('Content-Type: application/json; charset=utf-8');
require 'connect.php';
$id=intval($_POST['id']??0);
if(!$id){echo json_encode(['success'=>false,'error'=>'missing id']);exit;}
$dir="../uploads/images/"; if(!is_dir($dir)) mkdir($dir,0777,true);
if(!isset($_FILES['image'])){echo json_encode(['success'=>false,'error'=>'no file']);exit;}
$ext=strtolower(pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION));
if(!in_array($ext,['jpg','jpeg','png','gif','webp']))$ext='png';
$fname=time().'_'.bin2hex(random_bytes(3)).'.'.$ext; $target=$dir.$fname;
if(move_uploaded_file($_FILES['image']['tmp_name'],$target)){
  $rel="uploads/images/".$fname;
  $conn->query("UPDATE entries SET updated_at=NOW() WHERE id=$id");
  $jid=$conn->query("SELECT journal_id FROM entries WHERE id=$id")->fetch_assoc()['journal_id']??0;
  if($jid) $conn->query("UPDATE journals SET updated_at=NOW() WHERE id=$jid");
  echo json_encode(['success'=>true,'path'=>$rel]);
}else echo json_encode(['success'=>false,'error'=>'upload failed']);
