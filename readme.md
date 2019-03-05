# Knock

PHP authorization class for logging in and logging out.

## In short

- Only 1 class
- A really small class
- Persistent cookie
- Plenty of options
- Hooks triggered after login/logout
- No dependencies

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

*You can also to it like below, but it's not very good to make the password visible like that. For testing purpose only!*

```php
<?php return hash('sha256', 'test');
```

### knock::login()

You can use this function to login a user. It will check if the user `$_POST` password matches the user file password. If it matches it will write a hash to a temp file and set a `$_COOKIE`.

```php
<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

echo knock::login();
```

### knock::logout()

You can use this function to logout a user. It will delte the `$_COOKIE` as long as the current options has not changed.

```php
<?php
include __DIR__ . '/knock.php';

echo knock::logout();
```

### knock::isAuthorized()

You can use this function to see if the user that is trying to login is authorized. It will check if the user `$_POST` password matches the user file password. It will return `true` or `false`.

```php
<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

echo knock::isAuthorized();
```

### knock::isLoggedIn()

You can use this function to see if the user is logged in. It will check if the user `$_COOKIE` hash matches the temp file hash. It will return `true` or `false`.

***Be aware: This function does not work until you refresh the page. That's how `$_COOKIE` works.***

```php
<?php
include __DIR__ . '/knock.php';

echo knock::isLoggedIn();
```

## Options (optional)

To use the options you need to place a `options.php` file in the root.

**Defaults**

```php
return [
  'path.users' => __DIR__ . '/users/',
  'path.temp' => __DIR__ . '/temp/',
  'cookie.path' => '/',
  'cookie.expires' => 2147483647,
  'prefix' => 'knock',
  'callback.login' => function() {},
  'callback.logout' => function() {},
];
```

### Explained

| Name              | Type     | Default                 | Description                                                                                |
| ----------------- | -------- | ----------------------- | ------------------------------------------------------------------------------------------ |
| `path.temp`       | string   | `__DIR__ . '/users/'`   | Path where temporary login data is stored                                                  |
| `path.users`      | string   | `__DIR__ . '/temp/'`    | Path where user files are stored                                                           |
| `cookie.path`     | string   | `'/'`                   | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `cookie.expires`  | integer  | `2147483647`       | A timestamp when cookie expires. Default is about 20 years.                                |
| `cookie.prefix`   | string   | `'knock'`               | To prevent collisions with other cookies you can set your own prefix.                      |
| `callback.login`  | function | `function($success) {}` | After a login attempt, this hook is triggered if it exists                                 |
| `callback.logout` | function | `function($success) {}` | After logging out, this hook is triggered if it exists                                     |

## Requirements

- PHP 7+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](issues/new).

## Donate

Donate to [DevoneraAB](https://www.paypal.me/DevoneraAB) if you want.

## License

MIT