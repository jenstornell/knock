<?php
include __DIR__ . '/../knock.php';
$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/4.login-wrong-password.php';

$_POST['postusername'] = 'test@example.com!';
$_POST['postpassword'] = 'test';

$options = include __DIR__ . '/../options.php';
$knock = new Knock($options);

if(!$knock->login() && !$knock->isAuthorized()) {
  header("Location: " . $link);
  die;
}