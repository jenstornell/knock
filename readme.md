# Knock

PHP authorization class for logging in and logging out. No form is included.

*Version 1.7* [Changelog](changelog.md)

## In short

- Only 1 class
- Persistent cookie
- Callback support
- Whitelist of IPs
- Plenty of options
- No dependencies
- No database

## Usage

### Create a user

The folder structure may look like below where filename should be `[username].php`.

```text
└─ users
   └─ test@example.com.php
```

#### Inside the user file

- Inside the user file you return an array including the password.
- To use the password `test` you need to hash it with a tool like [SHA256 Hash Generator](https://passwordsgenerator.net/sha256-hash-generator/).
- *You can also use `hash('sha256', 'test')` as the password, but that is not recommended. Use it for testing purposes only!*

```php
<?php return [
  'password' => '9f86d081884c7d659a2feaa0c55ad015a3bf4f1b2b0b822cd15d6c15b0f00a08',
];
```

## Methods

All methods work simliar to below.

```php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

$knock = new Knock();
if($knock->login()) {
  echo 'You are logged in';
} else {
  print_r($knock->results); // ['success' => false, 'error' => 'error_message']
}
```

| Name                  | Args     | Default                                   | Description                                                                                |
| --------------------- | -------- | ----------------------------------------- | ------------------------------------------------------------------------------------------ |
| `isAuthorized()`      | -        | -                                         | Returns `true` if the user `$_POST` password matches the user file password                |
| `isLoggedIn()`        | -        | -                                         | Returns `true` if the user `$_COOKIE` hash matches the temp file hash (which is salted)    |
| `getCookieExpires()`  | -        | -                                         | Returns the cookie expires timestamp if it exists                                          |
| `keepAlive()`         | -        | -                                         | Will run `refresh`, but only if the cookie is close to its expire timestamp                |
| `login()`             | -        | -                                         | Login a user from `$_POST['username']` and `$_POST['password']` if you use these keys      |
| `logout()`            | -        | -                                         | It will delete the `$_COOKIE` and remove the temp file                                     |
| `refresh()`           | -        | -                                         | If logged in, it will create a new hash and expire timestamp                               |

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

## Security headers

Make sure to use security headers on pages where you call Knock.

```php
header("X-Frame-Options: sameorigin"); // Prevent iframe access
header("X-XSS-Protection: 1; mode=block"); // XSS protection
header("X-Content-Type-Options: nosniff"); // Require correct MIME type for CSS and JS
header("Content-Security-Policy: default-src 'none'; script-src 'self'; connect-src 'self'; img-src 'self'; style-src 'self';");
header("Referrer-Policy: no-referrer");
```

**Source:** https://zinoui.com/blog/security-http-headers

## Generate strong passwords

The probably best service out there to generate passwords is https://www.expressvpn.com/password-generator.

## Hacker challenge

Do you think you can hack this thing? I would appreciate if you tried. If you succeed, report in an issue what you did.

Your report should contain a real life case, not a theoretically one. An example of a theoretically hack would be to guess the cookie username and hash. Because even the cookie keys are unknown and different for each installation, it would take the sun to go out before your guess is correct.

I will not pay you anything for the work, but you can get a mention in the readme file and perhaps a link to your site as thanks (if it's not unhealthy).

## Requirements

- PHP 7+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](issues/new).

## Donate

Donate to [DevoneraAB](https://www.paypal.me/DevoneraAB) if you want.

## License

MIT