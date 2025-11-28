<?php
// db.php
$host = "sql100.infinityfree.com";
$user = "if0_40532839";     // default in XAMPP
$pass = "inventrix2025";         // default empty in XAMPP
$dbname = "if0_40532839_festdb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>
