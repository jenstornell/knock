<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/5.login-success.php';

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test!';

if(!knock::login()) {
  header("Location: " . $link);
  die;
}