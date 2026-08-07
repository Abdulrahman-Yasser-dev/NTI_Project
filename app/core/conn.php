<?php

$string = "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8mb4";
$conn = new PDO($string, DBUSER, DBPASS);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

