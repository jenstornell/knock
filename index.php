<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

$knock = new Knock([
  'login_delay' => 100
]);

print_r($knock->login());