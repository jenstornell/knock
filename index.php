<?php
include __DIR__ . '/knock.php';

$_POST['postusername'] = 'test@example.com';
$_POST['postpassword'] = 'test';

$knock = new Knock();

print_r($knock->isAuthorized());

#echo knock::login();