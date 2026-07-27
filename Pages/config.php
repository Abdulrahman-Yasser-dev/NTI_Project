<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "nti_sard";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
?>