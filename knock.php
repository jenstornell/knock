<?php
class KnockCore {

  // Set options
  public function __construct() {
    $this->options();
  }

  ## PRIVATE

  // Defaults
  private function defaults() {
    return [
      'algorithm' => 'sha256',
      'callback_login' => function($success) { return $success; },
      'callback_logout' => function($success) { return $success; },
      'cookie_prefix' => 'knock',
      'cookie_refresh' => 15, // Bad key name
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
  }

  // Set options
  private function options() {
    $path = __DIR__ . '/options.php';
    $array = (file_exists($path)) ? array_merge($this->defaults(), include($path)) : $this->defaults();

    foreach($array as $key => $value) {
      $this->{$key} = $value;
    }
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
    if(!$this->setCookie('[' . $this->key_cookie_username . ']', '', 0)) return;
    if(!$this->setCookie('[' . $this->key_cookie_hash . ']', '', 0)) return;
    if(!$this->setCookie('[' . $this->key_cookie_expires . ']', '', 0)) return;

    return true;
  }

  // Write cookie on login
  private function writeCookie($hash, $username) {
    if(!$this->setCookie('[' . $this->key_cookie_username . ']', $username)) return;
    if(!$this->setCookie('[' . $this->key_cookie_hash . ']', $hash)) return;
    if(!$this->setCookie('[' . $this->key_cookie_expires . ']', $this->setcookie_expires)) return;

    return true;
  }

  // Set cookie
  private function setCookie($key, $value, $expires = null) {
    $expires = ($expires === null) ? $this->setcookie_expires : $expires;
    return setcookie(
      $this->cookie_prefix . $key,
      $value,
      $expires,
      $this->setcookie_path,
      $this->setcookie_domain,
      $this->setcookie_secure,
      $this->setcookie_httponly
    );
  }

  // Write temp file to disc
  private function writeFile($hash, $username) {
    if(!file_exists($this->path_temp)) {
      if(!mkdir($this->path_temp)) return;
    }

    $path = $this->path_temp . $username . '.php';

    $hash = hash($this->algorithm, $hash . $this->salt);
    $content = "<?php return '" . $hash . "';";

    return file_put_contents($path, $content);
  }

  // Check if IP is allowed
  private function ipAllowed() {
    if(empty($this->whitelist)) return true;

    foreach($this->whitelist as $item) {
      if(substr($item, -1) === '*') {
        $starts_with = substr($item, 0, -1);
        if(strpos($_SERVER['REMOTE_ADDR'], $starts_with) === 0) {
          return true;
        }
      } elseif($item === $_SERVER['REMOTE_ADDR']) {
         return true;
      }
    }
  }

  ## PUBLIC

  // Login with post variables
  public function login() {
    $success = null;

    usleep($this->login_delay * 1000);
    $ip_allowed = $this->ipAllowed();
    
    if($ip_allowed && $this->isAuthorized()) {
      $success = $this->loginUser($_POST[$this->key_post_username]);
    }
    return ($this->callback_login)($success);
  }

  // Logout
  public function logout() {
    $success = $this->logoutUser();
    return ($this->callback_logout)($success);
  }

  // Check if user is authorized with post variables
  public function isAuthorized() {
    if(!isset($_POST[$this->key_post_username])) return;
    if(!isset($_POST[$this->key_post_password])) return;

    $user_filepath = $this->path_users . $_POST[$this->key_post_username] . '.php';
    if(!file_exists($user_filepath)) return;
    
    $password = include($user_filepath);
    $password_post = hash($this->algorithm, $_POST[$this->key_post_password]);

    if($password == $password_post) return true;
  }

  // Check if user is logged in with cookie
  public function isLoggedIn() {
    $cookie = $_COOKIE[$this->cookie_prefix];

    if(!isset($cookie[$this->key_cookie_username])) return;
    if(!isset($cookie[$this->key_cookie_hash])) return;

    $user_filepath = $this->path_temp . $cookie[$this->key_cookie_username] . '.php';
    if(!file_exists($user_filepath)) return;

    $hash = include($user_filepath);
    $hash_cookie = $cookie[$this->key_cookie_hash];

    $hash_cookie = hash($this->algorithm, $hash_cookie . $this->salt);

    if($hash == $hash_cookie) return true;
  }

  // Get the cookies expire timestamp
  public function getCookieExpires() {
    return $_COOKIE[$this->cookie_prefix][$this->key_cookie_expires];
  }

  // Refresh the cookies if the cookies will soon expire
  public function keepAlive() {
    $minutes = round(($this->getCookieExpires()-time())/60);
    $diff = $minutes - $this->cookie_refresh;
    return ($diff < 0) ? $this->refresh() : true;
  }

  // Refresh the cookies, creates new hash and expire timestamp
  public function refresh() {
    if($this->isLoggedIn()) {
      return $this->loginUser($_COOKIE[$this->cookie_prefix][$this->key_cookie_username]);
    }
    return true;
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