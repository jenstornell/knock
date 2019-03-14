# Knock

PHP authorization class for logging in and logging out. No form is included.

*Version 1.6* [Changelog](changelog.md)

## In short

- Only 1 class
- A really small class
- Persistent cookie
- Plenty of options
- Hooks triggered after login/logout
- No dependencies
- No database

## Quick example

### Form page

```php
<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

knock::login();
```

### Destination page

```php
<?php
if(!knock::isLoggedIn()) die('You are not allowed to view this page.');

echo 'Welcome user!';
```

## Usage

### Create a user

The folder structure may look like below where filename should be `[username].php`.

```text
└─ users
   └─ test@example.com.php
```

#### Inside the user file

Inside the user file you return the password. To use the password `test` you need to hash it with a tool like [SHA256 Hash Generator](https://passwordsgenerator.net/sha256-hash-generator/).

```php
<?php return '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08';
```

*You can also use `<?php return hash('sha256', 'test');`, but it's not recommended. Use it for testing purpose only!*

### Initialize class

To be able to call any method, you first need to load the class.

```php
include __DIR__ . '/knock.php';
```

## Methods

For any method to work, you need to initialize the class like above.

### knock::login()

You can use this function to login a user. It will check if the user `$_POST` password matches the user file password. If it matches it will write a hash to a temp file and set a `$_COOKIE`.

```php
<?php
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

knock::login();
```

### knock::logout()

You can use this function to logout a user. It will delte the `$_COOKIE` as long as the current options has not changed.

```php
<?php
knock::logout();
```

### knock::isAuthorized()

You can use this function to see if the user that is trying to login is authorized. It will check if the user `$_POST` password matches the user file password. It will return `true` or `false`.

```php
<?php
$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

if(knock::isAuthorized()) {
  echo 'You are authorized';
}
```

### knock::isLoggedIn()

You can use this function to see if the user is logged in. It will check if the user `$_COOKIE` hash matches the temp file hash. It will return `true` or `false`.

***Be aware: This function does not work until you refresh the page, or load another page after logging in. That's how `$_COOKIE` works.***

### knock::refresh()

You can use this function to refresh the cookie. It will then create a new hash and expire timestamp. To be able to refresh, it's required to be logged in.

```php
knock::refresh();
```

### knock::keepAlive()

This function is similar to `knock::refresh()`. The difference is that this function will only refresh if the cookie is close to the expire time. How close depends on what you set in the option `cookie.refresh`. The default is 15 minutes before the cookie expires.

```php
knock::refresh();
```

### knock::getCookieExpires()

Get the cookie expires timestamp.

```php
echo knock::getCookieExpires();
```

```php
<?php
if(!knock::isLoggedIn()) die('You are not allowed to view this page.');

echo 'Welcome user!';
```

## Options (optional)

To use the options you need to place a `options.php` file in the root.

**Defaults**

```php
return [
  'callback_login' => function($success) { return $success; },
  'callback_logout' => function($success) { return $success; },
  'cookie_prefix' => 'knock',
  'cookie_refresh' => 15,
  'login_delay' => 500,
  'login_attempts' => 5,
  'key_cookie_expires' => 'expires',
  'key_cookie_hash' => 'hash',
  'key_cookie_username' => 'username',
  'key_post_password' => 'password',
  'key_post_username' => 'username',
  'path_temp' => __DIR__ . '/temp/',
  'path_users' => __DIR__ . '/users/',
  'salt' => '',
  'setcookie_domain' => '',
  'setcookie_expires' => 0,
  'setcookie_httponly' => false,
  'setcookie_path' => '',
  'setcookie_secure' => false,
  'whitelist' => [],
];
```

### Explained

<!--| `login_attempts`      | integer  | `5`                                       | Not yet implemented                                                                        |-->

| Name                  | Type     | Default                                   | Description                                                                                |
| --------------------- | -------- | ----------------------------------------- | ------------------------------------------------------------------------------------------ |
| `algorithm`           | string   | `'sha256'`                                | The algorithm used to create hashes                                                        |
| `callback_login`      | function | `function($success) { return $success; }` | After a login attempt, this hook is triggered if it exists                                 |
| `callback_logout`     | function | `function($success) { return $success; }` | After logging out, this hook is triggered if it exists                                     |
| `cookie_prefix`       | string   | `'knock'`                                 | To prevent collisions with other cookies you can set your own prefix.                      |
| `cookie_refresh`      | string   | `15`                                      | When using `knock::keepAlive()` this value is used to decide when to refresh the cookie.   |
| `login_delay`         | integer  | `500`                                     | A millisecond number to delay the authorization. It will prevent bruce force attacks       |
| `key_cookie_expires`  | string   | `'expires'`                               | Change this to make the cookie a bit more cryptic.                                         |
| `key_cookie_hash`     | string   | `'hash'`                                  | Change this to make the cookie a bit more cryptic.                                         |
| `key_cookie_username` | string   | `'username'`                              | Change this to make the cookie a bit more cryptic.                                         |
| `key_post_password`   | string   | `'password'`                              | Change this to make the post a bit more cryptic.                                           |
| `key_post_username`   | string   | `'username'`                              | Change this to make the post a bit more cryptic.                                           |
| `path_temp`           | string   | `__DIR__ . '/users/'`                     | Path where temporary login data is stored                                                  |
| `path_users`          | string   | `__DIR__ . '/temp/'`                      | Path where user files are stored                                                           |
| `salt`                | string   | `''`                                      | A random string that will be added to the temp file. It will make it a bit harder to hack  |
| `setcookie_domain`    | string   | `''`                                      | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `setcookie_expires`   | integer  | `0`                                       | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `setcookie_httponly`  | boolean  | `false`                                   | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `setcookie_path`      | string   | `''`                                      | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `setcookie_secure`    | string   | `false`                                   | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `whitelist`           | array    | `[]`                                      | Allwed IP numbers. If not set, all are allowed. Ending wildcard `*` supported.             |                                                        |

## Generate strong passwords

The probably best service out there to generate passwords is https://www.expressvpn.com/password-generator.

## Requirements

- PHP 7+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](issues/new).

## Donate

Donate to [DevoneraAB](https://www.paypal.me/DevoneraAB) if you want.

## License

MIT