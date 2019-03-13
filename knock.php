<?php
class KnockCore {
  // Set options
  public function __construct() {
    $this->options();
  }

  // Login with post variables
  public function login() {
    $success = null;
    if($this->isAuthorized()) {
      usleep($this->o('delay') * 1000);
      $userkey = $this->o('post.username.key');
      $success = $this->loginUser($_POST[$userkey]);
    }

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
    $userkey = $this->o('post.username.key');
    $passkey = $this->o('post.password.key');

    if(!isset($_POST[$userkey])) return;
    if(!isset($_POST[$passkey])) return;

    $user_filepath = $this->o('path.users') . $_POST[$userkey] . '.php';
    if(!file_exists($user_filepath)) return;
    
    $password = include($user_filepath);
    $password_post = hash('sha256', $_POST[$passkey]);

    if($password == $password_post) return true;
  }

  // Check if user is logged in with cookie
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

  // Get the cookies expire timestamp
  public function getCookieExpires() {
    return $_COOKIE[$this->o('cookie.prefix')][$this->o('cookie.expires.key')];
  }

  // Refresh the cookies if the cookies will soon expire
  public function keepAlive() {
    $minutes = round(($this->getCookieExpires()-time())/60);
    $diff = $minutes - $this->o('cookie.refresh');
    if($diff < 0) {
      return $this->refresh();
    }
    return true;
  }

  // Refresh the cookies, creates new hash and expire timestamp
  public function refresh() {
    if($this->isLoggedIn()) {
      return $this->loginUser($_COOKIE[$this->o('cookie.prefix')][$this->o('cookie.username.key')]);
    }
    return true;
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
      'cookie.expires' => strtotime('+2 days'),
      'cookie.expires.key' => 'expires',
      'cookie.hash.key' => 'hash',
      'cookie.path' => '/',
      'cookie.prefix' => 'knock',
      'cookie.refresh' => 115,
      'cookie.secure' => true,
      'cookie.username.key' => 'username',
      'delay' => rand(1000, 2000),
      'path.users' => __DIR__ . '/users/',
      'path.temp' => __DIR__ . '/temp/',
      'post.password.key' => 'password',
      'post.username.key' => 'username',
      'salt' => '',
    ];
  }

  // Login user
  private function loginUser($username) {
    $hash = bin2hex(random_bytes(16));

    if(!$this->writeCookie($hash, $username)) return;
    if(!$this->writeFile($hash, $username)) return;

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
    if(!$this->setCookie('[' . $this->o('cookie.expires.key') . ']', '', 0)) return;

    return true;
  }

  // Write cookie on login
  private function writeCookie($hash, $username) {
    $userkey = $this->o('post.username.key');
    if(!$this->setCookie('[' . $this->o('cookie.username.key') . ']', $username)) return;
    if(!$this->setCookie('[' . $this->o('cookie.hash.key') . ']', $hash)) return;
    if(!$this->setCookie('[' . $this->o('cookie.expires.key') . ']', $this->o('cookie.expires'))) return;

    return true;
  }

  // Set cookie
  private function setCookie($key, $value, $expires = null) {
    $expires = ($expires === null) ? $this->o('cookie.expires') : $expires;
    $domain = $this->o('cookie.domain');
    $secure = $this->o('cookie.secure');

    return setcookie($this->o('cookie.prefix') . $key, $value, $expires, $this->o('cookie.path'), $domain, $secure, true);
  }

  // Write temp file to disc
  private function writeFile($hash, $username) {
    if(!file_exists($this->o('path.temp'))) {
      if(!mkdir($this->o('path.temp'))) return;
    }

    $salt = $this->o('salt');
    $userkey = $this->o('post.username.key');
    $path = $this->o('path.temp') . $username . '.php';

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

  public static function getCookieExpires() {
    $core = new KnockCore();
    return $core->getCookieExpires();
  }

  public static function refresh() {
    $core = new KnockCore();
    return $core->refresh();
  }

  public static function keepAlive() {
    $core = new KnockCore();
    return $core->keepAlive();
  }
}