<?php
include __DIR__ . '/../knock.php';

$folder = dirname($_SERVER['PHP_SELF']);
$link = 'http://' . $_SERVER['HTTP_HOST'] . $folder . '/refresh.php';

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
if(isset($_GET['action'])) {
  if($_GET['action'] === 'start') {
    $_POST['postusername'] = 'test@example.com';
    $_POST['postpassword'] = 'test';
    $login = $knock->login();

    if(!$login['success']) die;

    $hash = $_COOKIE[$options['cookie_prefix']][$options['key_cookie_hash']];
    header("Location: " . $link . '?action=refresh&old=' . $hash);
  } elseif($_GET['action'] === 'refresh') {
    if(!$knock->refresh()) die;
    header("Location: " . $link . '?old=' . $_GET['old']);
  }
} else {
  $hash = $_COOKIE[$options['cookie_prefix']][$options['key_cookie_hash']];
  if($hash === $_GET['old']) die;
  echo $knock->isLoggedIn();
}