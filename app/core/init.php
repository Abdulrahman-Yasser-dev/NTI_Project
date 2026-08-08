<?php

session_start();

require_once "config.php";
require_once "conn.php";
require_once "function.php";

// Auto-refresh user session state if logged in
if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $userId = $_SESSION['user']['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $freshUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($freshUser) {
        // Keep the password hash out of the session just in case
        unset($freshUser['password']);
        $_SESSION['user'] = $freshUser;
    }
}
