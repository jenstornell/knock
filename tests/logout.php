<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/logout.php';

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

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';

$knock = new Knock($options);

if(isset($_GET['action']) && $_GET['action'] === 'start') {
  $login = $knock->login();

  if($login['success']) {
    header("Location: " . $link . '?action=logout');
  }
} elseif(isset($_GET['action']) && $_GET['action'] === 'logout') {
  $logout = $knock->logout();
  if($logout['success']) {
    header("Location: " . $link);
  }
} else {
  // is logged out
  if(!isset($_COOKIE[$options['cookie_prefix']])) echo true;
}