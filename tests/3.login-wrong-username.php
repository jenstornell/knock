<?php
include __DIR__ . '/../knock.php';
$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/4.login-wrong-password.php';

$_POST['postusername'] = 'test@example.com!';
$_POST['postpassword'] = 'test';

$knock = new Knock();

if(!$knock->login()) {
  header("Location: " . $link);
  die;
}