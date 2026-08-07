<?php
require_once "../app/core/init.php";
$tables = query($conn, "SHOW TABLES");
$schema = [];
foreach ($tables as $t) {
    $tableName = array_values($t)[0];
    $columns = query($conn, "SHOW COLUMNS FROM `$tableName`");
    $schema[$tableName] = $columns;
}
echo json_encode($schema, JSON_PRETTY_PRINT);
