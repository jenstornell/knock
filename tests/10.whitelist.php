<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/11.create-user.php';

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';

$options = include __DIR__ . '/../options.php';
$options['whitelist'] = ['ipnummer'];
$knock = new Knock($options);

if(!$knock->login()) {
  header("Location: " . $link);
  die;
}