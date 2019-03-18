<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/done.php';

$options = include __DIR__ . '/../options.php';
$knock = new Knock($options);

if($knock->createUser('hello', 'world')) {
  header("Location: " . $link);
  die;
}