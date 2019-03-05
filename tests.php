<?php
include __DIR__ . '/knock.php';

$message = '';

if(isset($_GET['login'])) {
  echo 'login';
  $_POST['username'] = 'test@example.com';
  $_POST['password'] = 'test';
  echo knock::login();
  die;
}

if(isset($_GET['logout'])) {
  echo 'logout';
  echo knock::logout();
  die;
}

if(isset($_GET['loggedin'])) {
  if(knock::isLoggedIn() !== true)
  $message = 'knock::isLoggedIn() should be null';
  echo $message;
  die;
}

if(isset($_GET['notloggedin'])) {
  if(knock::isLoggedIn() !== null)
  $message = 'knock::isLoggedIn() should be true';
  echo $message;
  die;
}

// isAuthorized() - false
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test.';
if(knock::isAuthorized() !== null)
$message = 'knock::isAuthorized() should be false';

// isAuthorized() - true
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';
if(knock::isAuthorized() !== true)
$message = 'knock::isAuthorized() should be true';

// login() - false
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test.';
if(knock::login() !== 'Error login')
$message = 'knock::login() should be false';

// login() - true
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';
if(knock::login() !== 'Success login')
$message = 'knock::login() should be true';

// logout() - true
if(knock::logout() !== 'Success logout')
$message = 'knock::logout() should be true';

echo $message;