# Knock

PHP authorization class for logging in and logging out.

## In short

- Only 1 class
- A really small class
- Persistent cookie
- Plenty of options
- Login
- Logout
- No dependencies

## Usage

### Create a user

In the folder `users` you can create users by for example an email with a filename like `test@example.com.php`. The username will then be `test@example.com`.

Inside the file you should add your password, like below.

```php
<?php return 'test';
```

PHP files are often protected to direct access. Therefor a PHP file is used instead of a plain TXT file. The password above is `test`.

### Login

```php
<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

echo knock::login();
```

## Logout

```php
<?php
include __DIR__ . '/knock.php';

echo knock::logout();
```

## isAuthorized

If you for some reason need to check if `$_POST['username]` and `$_POST['password']` matches a user, you can use `knock::isAuthorized()`. In most cases however, you probably want to use `knock::isLoggedIn()` which instead check if the cookie contain the correct user information.

## isLoggedIn

