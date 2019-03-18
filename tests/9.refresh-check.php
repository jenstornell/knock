<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/10.whitelist.php';

$options = include __DIR__ . '/../options.php';
$knock = new Knock($options);

if($knock->isLoggedIn()) {
  header("Location: " . $link);
}