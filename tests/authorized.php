<?php
include __DIR__ . '/../knock.php';

$options = [
  'cookie_prefix' => 'knock',
  'setcookie_path' => '/misc/knock/',
  'setcookie_expires' => strtotime('+2 days'),
  'key_cookie_username' => 'username123',
  'key_cookie_hash' => 'hashabc',
  'key_cookie_expires' => 'expires',
  'key_post_username' => 'postusername',
  'key_post_password' => 'postpassword',
  'salt' => '1',
  'whitelist' => ['195.67.60.18', '::12*', '::*'],
  'login_delay' => 0
];

$knock = new Knock($options);
$_POST['postusername'] = 'test@example.com.';
$_POST['postpassword'] = 'test';
$authorized = $knock->isAuthorized();

if($authorized) die;

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test.';
$authorized = $knock->isAuthorized();

if($authorized) die;

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';
$knock = new Knock($options);
$authorized = $knock->isAuthorized();

if($authorized) echo true;