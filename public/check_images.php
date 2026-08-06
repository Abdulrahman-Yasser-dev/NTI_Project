<?php
require_once __DIR__ . "/../app/core/init.php"; // same relative path index.php uses to reach init.php

$stmt = $conn->query("SELECT id, title, cover_image FROM novels WHERE cover_image IS NOT NULL AND cover_image != ''");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$imagesDir = __DIR__ . "/assets/images/"; // public/assets/images
$actualFiles = scandir($imagesDir);

echo "<pre style='direction:ltr;font-family:monospace'>";
foreach ($books as $b) {
    $exists = in_array($b['cover_image'], $actualFiles);
    $status = $exists ? "OK     " : "MISSING";
    echo "[$status] id={$b['id']}\ttitle={$b['title']}\tcover_image={$b['cover_image']}\n";
}
echo "</pre>";