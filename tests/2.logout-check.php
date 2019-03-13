<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/3.login-wrong-username.php';

if(!knock::isLoggedIn()) {
  header("Location: " . $link);
  die;
}