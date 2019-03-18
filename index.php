<?php
include __DIR__ . '/knock.php';

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';

$knock = new Knock([
  'login_delay' => 100
]);

echo $knock->login();