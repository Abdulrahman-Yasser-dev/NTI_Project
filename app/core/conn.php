<?php

$string = "mysql:host=" . DBHOST . ";dbname=" . DBNAME;
$conn = new PDO($string, DBUSER, DBPASS);

