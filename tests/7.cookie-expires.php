<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/8.refresh.php';

$knock = new Knock();

if($knock->getCookieExpires() > 0) {
  header("Location: " . $link);
  die;
}