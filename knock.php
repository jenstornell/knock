<?php
class KnockCore {
  // Set options
  public function __construct() {
    $this->options();
  }

  // Login with post variables
  public function login() {
    $success = $this->loginUser();

    if($this->o('callback.login')) {
      return $this->o('callback.login')($success);
    }

    return $success;
  }

  // Logout
  public function logout() {
    $success = $this->logoutUser();

    if($this->o('callback.logout')) {
      return $this->o('callback.logout')($success);
    }

    return $success;
  }

  // Check if user is authorized with post variables
  public function isAuthorized() {
    if(!isset($_POST['username'])) return;
    if(!isset($_POST['password'])) return;

    $user_filepath = $this->o('path.users') . $_POST['username'] . '.php';
    if(!file_exists($user_filepath)) return;
    
    $password = include($user_filepath);
    $password_post = hash('sha256', $_POST['password']);

    if($password == $password_post) return true;
  }

  public function isLoggedIn() {
    $prefix = $this->o('cookie.prefix');
    $userkey = $this->o('cookie.username.key');
    $hashkey = $this->o('cookie.hash.key');

    if(!isset($_COOKIE[$prefix][$userkey])) return;
    if(!isset($_COOKIE[$prefix][$hashkey])) return;

    $user_filepath = $this->o('path.temp') . $_COOKIE[$prefix][$userkey] . '.php';
    if(!file_exists($user_filepath)) return;

    $hash = include($user_filepath);
    $hash_cookie = $_COOKIE[$prefix][$hashkey];

    $hash_cookie = hash('sha256', $hash_cookie . $this->o('salt'));

    if($hash == $hash_cookie) return true;
  }

  // Set options
  private function options() {
    $path = __DIR__ . '/options.php';
    
    if(file_exists($path)) {
      $this->options = array_merge($this->defaults(), include($path));
    } else {
      $this->options = $this->defaults();
    }
  }

  // Option helper
  private function o($key) {
    return (isset($this->options[$key])) ? $this->options[$key] : null;
  }

  // Defaults
  private function defaults() {
    return [
      'cookie.domain' => '',
      'cookie.expires' => 2147483647,
      'cookie.hash.key' => 'hash',
      'cookie.path' => '/',
      'cookie.prefix' => 'knock',
      'cookie.secure' => true,
      'cookie.username.key' => 'username',
      'delay' => rand(1000, 2000),
      'path.users' => __DIR__ . '/users/',
      'path.temp' => __DIR__ . '/temp/',
      'salt' => '',
    ];
  }

  // Login user
  private function loginUser() {
    usleep($this->o('delay') * 1000);
    if(!$this->isAuthorized()) return;

    $hash = bin2hex(random_bytes(16));

    if(!$this->writeCookie($hash)) return;
    if(!$this->writeFile($hash)) return;

    return true;
  }

  // Logout user
  private function logoutUser() {
    return $this->deleteCookies();
  }

  // Delete cookies
  private function deleteCookies() {
    if(!$this->setCookie('[' . $this->o('cookie.username.key') . ']', '', 0)) return;
    if(!$this->setCookie('[' . $this->o('cookie.hash.key') . ']', '', 0)) return;

    return true;
  }

  // Write cookie on login
  private function writeCookie($hash) {
    if(!$this->setCookie('[' . $this->o('cookie.username.key') . ']', $_POST['username'])) return;
    if(!$this->setCookie('[' . $this->o('cookie.hash.key') . ']', $hash)) return;

    return true;
  }

  private function setCookie($key, $value, $expires = null) {
    $expires = ($expires === null) ? $this->o('cookie.expires') : $expires;
    $domain = $this->o('cookie.domain');
    $secure = $this->o('cookie.secure');

    return setcookie($this->o('cookie.prefix') . $key, $value, $expires, $this->o('cookie.path'), $domain, $secure, true);
  }

  // Write temp file to disc
  private function writeFile($hash) {
    if(!file_exists($this->o('path.temp'))) {
      if(!mkdir($this->o('path.temp'))) return;
    }

    $salt = $this->o('salt');
    $path = $this->o('path.temp') . $_POST['username'] . '.php';

    $hash = hash('sha256', $hash . $salt);
    $content = "<?php return '" . $hash . "';";

    return file_put_contents($path, $content);
  }
}

// STATIC CLASS HELPER
class knock {
  // Login
  public static function login() {
    $core = new KnockCore();
    return $core->login();
  }

  // Logout
  public static function logout() {
    $core = new KnockCore();
    return $core->logout();
  }

  // isLoggedIn
  public static function isAuthorized() {
    $core = new KnockCore();
    return $core->isAuthorized();
  }

  // isLoggedIn
  public static function isLoggedIn() {
    $core = new KnockCore();
    return $core->isLoggedIn();
  }
}