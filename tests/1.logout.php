<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/2.logout-check.php';

$options = include __DIR__ . '/../options.php';
$knock = new Knock($options);

if($knock->logout()) {
  header("Location: " . $link);
  die;
}