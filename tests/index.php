<?php
$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/1.logout.php';

header("Location: " . $link);
die;

// getcookieexpiores