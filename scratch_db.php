<?php
require_once "app/core/init.php";
$sql = "CREATE TABLE IF NOT EXISTS `user_library` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `novel_id` int(11) NOT NULL,
    `status` enum('reading_now','my_list','completed') DEFAULT NULL,
    `is_favorite` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_novel` (`user_id`, `novel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$conn->exec($sql);
echo "Table user_library created successfully.";
