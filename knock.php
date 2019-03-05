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
  }

  // Logout
  public function logout() {
    $success = $this->logoutUser();

    if($this->o('callback.logout')) {
      return $this->o('callback.logout')($success);
    }
  }

  // Check if user is authorized with post variables
  public function isAuthorized() {
    if(!isset($_POST['username'])) return;
    if(!isset($_POST['password'])) return;
    
    $password = include($this->o('path.users') . $_POST['username'] . '.php');
    $password_post = hash('sha256', $_POST['password']);

    if($password == $password_post) return true;
  }

  public function isLoggedIn() {
    if(!isset($_COOKIE[$this->o('prefix')]['username'])) return;
    if(!isset($_COOKIE[$this->o('prefix')]['hash'])) return;

    $hash = include($this->o('path.temp') . $_COOKIE[$this->o('prefix')]['username'] . '.php');
    $hash_cookie = $_COOKIE[$this->o('prefix')]['hash'];

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
      'path.users' => __DIR__ . '/users/',
      'path.temp' => __DIR__ . '/temp/',
      'cookie.path' => '/',
      'cookie.expires' => 2147483647,
      'prefix' => 'knock',
    ];
  }

  // Login user
  private function loginUser() {
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
    if(!setcookie($this->o('prefix') . '[username]', '', 0, $this->o('cookie.path'))) return;
    if(!setcookie($this->o('prefix') . '[hash]', '', 0, $this->o('cookie.path'))) return;

    return true;
  }

  // Write cookie on login
  private function writeCookie($hash) {
    if(!setcookie($this->o('prefix') . '[username]', $_POST['username'], $this->o('cookie.expires'), $this->o('cookie.path'))) return;
    if(!setcookie($this->o('prefix') . '[hash]', $hash, $this->o('cookie.expires'), $this->o('cookie.path'))) return;

    return true;
  }

  // Write temp file to disc
  private function writeFile($hash) {
    return file_put_contents($this->o('path.temp') . $_POST['username'] . '.php', "<?php return '" . $hash . "';");
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