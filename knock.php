<?php
class Knock {
  private $error = '';
  private $success = true;

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
    $array = $this->defaults();

    if(file_exists($path)) {
      $array = array_merge($this->defaults(), include($path));
    }

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
  private function logoutUser($username) {
    $path = $this->path_temp . $username . '.php';

    $this->deleteFile($path);
    $this->deleteCookies();
  }

  // Delete file
  private function deleteFile($path) {
    if(file_exists($path)) {
      if(!unlink($path)) {
        $this->success = false;
        $this->error = 'delete_file:' . $path;
        return;
      }
    }
    return true;
  }

  // Make directiory
  private function makeDir($path) {
    if(!file_exists($path)) {
      if(!mkdir($path)) {
        $this->error = 'makedir:' . $path;
        $this->success = false;
        return;
      }
    }
    return true;
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

    $this->success = setcookie(
      $this->cookie_prefix . $key,
      $value,
      $expires,
      $this->setcookie_path,
      $this->setcookie_domain,
      $this->setcookie_secure,
      $this->setcookie_httponly
    );

    if(!$this->success) {
      $this->error = 'cookie_key:' . $key;
    }
    return $this->success;
  }

  // Write temp file to disc
  private function writeFile($hash, $username) {
    if(!$this->makeDir($this->path_temp)) return;

    $content = "<?php return '" . $this->fileHash($hash) . "';";
    $this->success = file_put_contents($this->path_temp . $username . '.php', $content);

    if(!$this->success) {
      $this->error = 'write_file:' . $path;
    }
    return $this->success;
  }

  private function fileHash($hash) {
    return hash($this->algorithm, $hash . $this->salt);
  }

  // Check if IP is allowed
  private function ipAllowed() {
    if(!empty($this->whitelist)) {
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
      $this->success = false;
      $this->error = 'ip';
      return;
    }
  }

  // Filepath
  private function filepath($type, $username = null) {
    switch($type) {
      case 'post':
        $filepath = $this->path_users . $_POST[$this->key_post_username] . '.php';
        break;
      case 'cookie':
        $filepath = $this->path_temp . $_COOKIE[$this->cookie_prefix][$this->key_cookie_username] . '.php';
        break;
      case 'user':
        $filepath = $this->path_temp . $username . '.php';
        break;
    }

    if(!file_exists($filepath)) {
      $this->success = false;
      $this->error = 'filepath:' . $filepath;
      return;
    } else {
      return $filepath;
    }
  }

  // Get cookie
  private function getCookie($key) {
    $key = 'key_' . $key;
    if(!isset($_COOKIE[$this->cookie_prefix][$this->{$key}])) {
      $this->success = false;
      $this->error = 'cookie_key:' . $key;
      return;
    }
    return $_COOKIE[$this->cookie_prefix][$this->{$key}];
  }

  // Has post
  private function hasPost($key) {
    $key = 'key_' . $key;
    if(!isset($_POST[$this->{$key}])) {
      $this->success = false;
      $this->error = 'post:' . $key;
      return;
    }
    return true;
  }

  private function hasCookies() {
    if(!$this->getCookie('cookie_expires')) return;
    if(!$this->getCookie('cookie_hash')) return;
    if(!$this->getCookie('cookie_username')) return;

    return true;
  }

  // Check if user is authorized with post variables
  private function userIsAuthorized() {
    if(!$this->hasPost('post_username')) return;
    if(!$this->hasPost('post_password')) return;
    if(!$this->filepath('post')) return;

    $data = include($this->filepath('post'));

    if(!isset($data['password'])) {
      $this->success = false;
      $this->error = 'password_missing';
      return;
    }
    $post_password = hash($this->algorithm, $_POST[$this->key_post_password]);

    if($data['password'] !== $post_password) {
      $this->success = false;
      $this->error = 'password_unmatched';
      return;
    }
    return true;
  }

  // Results
  private function setResults() {
    $results['success'] = $this->success;

    if(!$this->success) {
      $results['error'] = $this->error;
    }
    $this->results = $results;
  }  

  ## PUBLIC

  public function isAuthorized() {
    $this->userIsAuthorized();
    $this->setResults();
    return $this->success;
  }

  // Check if user is logged in with cookie
  public function isLoggedIn() {
    if(!$this->hasCookies()) return;
    if(!$this->filepath('cookie')) return;

    $hash = include($this->filepath('cookie'));
    $hash_cookie = $this->getCookie('cookie_hash');
    $hash_cookie = hash($this->algorithm, $hash_cookie . $this->salt);

    if($hash != $hash_cookie) {
      $this->success = false;
      $this->error = 'hash_unmatched';
    }

    $this->setResults();
    return $this->success;
  }

  // Get the cookies expire timestamp
  public function getCookieExpires() {
    return $this->getCookie('cookie_expires');
  }

  // Refresh the cookies if the cookies will soon expire
  public function keepAlive() {
    $minutes = round(((int)$this->getCookieExpires()-time())/60);
    $diff = $minutes - $this->cookie_refresh;
    return ($diff < 0) ? $this->refresh() : true;
  }

  // Login with post variables
  public function login() {
    usleep($this->login_delay * 1000);
    
    if($this->ipAllowed() && $this->userIsAuthorized()) {
      $this->loginUser($_POST[$this->key_post_username]);
    }
    $this->setResults();
    
    return $this->success;
  }

  // Logout
  public function logout() {
    $this->logoutUser($this->getCookie('cookie_username'));
    $this->setResults();
    return $this->results['success'];
  }

  // Refresh the cookies, creates new hash and expire timestamp
  public function refresh() {
    if($this->isLoggedIn()) {
      return $this->loginUser($this->getCookie('cookie_username'));
    }
    return true;
  }
}