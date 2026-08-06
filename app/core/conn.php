<?php

$port = defined("DBPORT") ? ";port=" . DBPORT : "";
$string = "mysql:host=" . DBHOST . $port . ";dbname=" . DBNAME;
$conn = new PDO($string, DBUSER, DBPASS);

