<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/done.php';

if($_GET['hash'] != $_COOKIE['knock']['hashabc'] && $_GET['expires'] != $_COOKIE['knock']['expires']) {
  header("Location: " . $link);
  die;
}