<?php
include "../app/core/init.php";

$url = isset($_GET['url']) ? $_GET['url'] : 'index';

$page = explode('/', $url);

$pagename = trim($page[0]);

if (empty($pagename)) {
    $pagename = 'index';
}

$location = "../app/pages/" . $pagename . ".php";

if (file_exists($location)) {
    require_once($location);
} else {
    if (file_exists("../app/pages/404.php")) {
        require_once("../app/pages/404.php");
    } else {
        echo "404 Not Found";
    }
}
?>
