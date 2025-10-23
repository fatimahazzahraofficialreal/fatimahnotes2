<?php
// C:\xampp\htdocs\fatimahnotes\backend\connect.php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0);

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'journaling2_db';

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset('utf8mb4');
