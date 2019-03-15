<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/7.cookie-expires.php';

$knock = new Knock();

if($knock->isLoggedIn()) {
  header("Location: " . $link);
  die;
}