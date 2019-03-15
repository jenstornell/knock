<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/9.refresh-check.php';

$knock = new Knock();

if($knock->refresh()) {
  header("Location: " . $link);
  die;
}