<?php
include 'connect.php';

$id = intval($_POST['id']);
$targetDir = "../uploads/videos/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (isset($_FILES["video"])) {
    $fileName = time() . "_" . basename($_FILES["video"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
        $videoPath = "uploads/videos/" . $fileName;
        $sql = "UPDATE journals SET video_path='$videoPath' WHERE id=$id";
        $conn->query($sql);
        echo json_encode(["success" => true, "path" => $videoPath]);
    } else {
        echo json_encode(["success" => false, "error" => "Upload failed"]);
    }
}
?>
