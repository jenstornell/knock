<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/6.login-success-check.php';

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';

if(knock::login()) {
  header("Location: " . $link);
  die;
}