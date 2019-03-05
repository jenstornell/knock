# Knock

PHP authorization class for logging in and logging out. No form is included.

## In short

- Only 1 class
- A really small class
- Persistent cookie
- Plenty of options
- Hooks triggered after login/logout
- No dependencies

## Quick example

```php
<?php
include __DIR__ . '/knock.php';

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

knock::login();
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
  'path.users' => __DIR__ . '/users/',
  'path.temp' => __DIR__ . '/temp/',
  'cookie.path' => '/',
  'cookie.expires' => 2147483647,
  'prefix' => 'knock',
  'callback.login' => function($success) {},
  'callback.logout' => function($success) {},
];
```

### Explained

| Name              | Type     | Default                 | Description                                                                                |
| ----------------- | -------- | ----------------------- | ------------------------------------------------------------------------------------------ |
| `path.temp`       | string   | `__DIR__ . '/users/'`   | Path where temporary login data is stored                                                  |
| `path.users`      | string   | `__DIR__ . '/temp/'`    | Path where user files are stored                                                           |
| `cookie.path`     | string   | `'/'`                   | See [setcookie](http://php.net/manual/en/function.setcookie.php)                           |
| `cookie.expires`  | integer  | `2147483647`            | A timestamp when cookie expires. Default is about 20 years.                                |
| `cookie.prefix`   | string   | `'knock'`               | To prevent collisions with other cookies you can set your own prefix.                      |
| `callback.login`  | function | `function($success) {}` | After a login attempt, this hook is triggered if it exists                                 |
| `callback.logout` | function | `function($success) {}` | After logging out, this hook is triggered if it exists                                     |

### Callbacks

The callbacks are just options. The differece is that they work like functions, triggered by login or logout. In the `options.php` file you can do like below.

```php
return [
  'callback.login' => function($success) {
    if($success) {
      header('Location: https://example.com/admin');
    } else {
      header('Location: https://example.com/error');
    }
    die;
  }
];
```

## Requirements

- PHP 7+

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](issues/new).

## Donate

Donate to [DevoneraAB](https://www.paypal.me/DevoneraAB) if you want.

## License

MIT