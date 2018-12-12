<?php
include __DIR__ . '/lib/init.php';

if(login::isValidMatch()) {
  echo 'Inloggad';
  echo "#" . \knock\option('url') . '/login.php';
} else {
  header("Location: " . \knock\option('url') . '/login.php');
  die;
}