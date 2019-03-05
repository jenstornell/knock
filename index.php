<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

echo knock::login();