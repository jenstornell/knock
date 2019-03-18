<?php
class Knock {
  private $error = null;

  // Set options
  public function __construct($options = null) {
    $this->options($options);
  }

  ## PRIVATE

  // Defaults
  private function defaults() {
    return [
      'algorithm' => 'sha256',
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
  private function options($options) {
    $array = $this->defaults();

    if($options) {
      $array = array_merge($this->defaults(), $options);
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
    $this->deleteCookies();

    if($username) {
      $path = $this->path_temp . $username . '.php';
      return $this->deleteFile($path);
    }
    return true;
  }

  // Delete file
  private function deleteFile($path) {
    echo $path;
    if(file_exists($path)) {
      if(!unlink($path)) {
        $this->error = [1, $path];
        return;
      }
    }
    return true;
  }

  // Make directiory
  private function makeDir($path) {
    if(!file_exists($path)) {
      if(!mkdir($path)) {
        $this->error = [2, $path];
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

    $success = setcookie(
      $this->cookie_prefix . $key,
      $value,
      $expires,
      $this->setcookie_path,
      $this->setcookie_domain,
      $this->setcookie_secure,
      $this->setcookie_httponly
    );

    if(!$success) {
      $this->error = [3, $key];
    }
    return $success;
  }

  // Write temp file to disc
  private function writeFile($hash, $username) {
    if(!$this->makeDir($this->path_temp)) return;

    $content = "<?php return '" . $this->fileHash($hash) . "';";
    $success = file_put_contents($this->path_temp . $username . '.php', $content);

    if(!$success) {
      $this->error = [4, $path];
    }
    return $success;
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
      $this->error = [5];
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
      $this->error = [6, $filepath];
      return;
    } else {
      return $filepath;
    }
  }

  // Get cookie
  private function getCookie($key) {
    $key = 'key_' . $key;
    if(isset($_COOKIE[$this->cookie_prefix][$this->{$key}])) {
      return $_COOKIE[$this->cookie_prefix][$this->{$key}];
    }
  }

  // Has post
  private function hasPost($key) {
    $key = 'key_' . $key;
    if(!isset($_POST[$this->{$key}])) {
      $this->error = [8, $key];
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
      $this->error = [9];
      return;
    }
    $post_password = hash($this->algorithm, $_POST[$this->key_post_password]);

    if($data['password'] !== $post_password) {
      $this->error = [10];
      return;
    }
    return true;
  }

  // Results
  private function setResults() {
    $results['success'] = ($this->error === null) ? true : false;

    if($this->error !== null) {
      $results['error'] = $this->error;
    }
    $this->results = $results;
  }  

  ## PUBLIC

  // is authorized
  public function isAuthorized() {
    $this->userIsAuthorized();
    $this->setResults();
    return $this->results['success'];
  }

  // Check if user is logged in with cookie
  public function isLoggedIn() {
    if(!$this->hasCookies()) return;
    if(!$this->filepath('cookie')) return;

    $hash = include($this->filepath('cookie'));
    $hash_cookie = $this->getCookie('cookie_hash');
    $hash_cookie = hash($this->algorithm, $hash_cookie . $this->salt);

    if($hash != $hash_cookie) {
      $this->error = [11];
    }

    $this->setResults();
    return $this->results['success'];
  }

  // Get the cookies expire timestamp
  public function getCookieExpires() {
    return $this->getCookie('cookie_expires');
  }

  // Create user
  public function createUser($username = null, $password = null) {
    if(!$username || !$password) {
      $this->error = [12];
    } elseif(file_exists($this->path_users . $username . '.php')) {
      $this->error = [13];
    } else {
      $content = sprintf("<?php return ['password' => '%s'];", hash('sha256', $password));
      $success = file_put_contents($this->path_users . $username . '.php', $content);
      if(!$success) {
        $this->error = [14];
      }
    }
    $this->setResults();
    return $this->results['success'];
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
    return $this->results['success'];
  }

  // Logout
  public function logout() {
    $this->logoutUser($this->getCookie('cookie_username'));
    $this->setResults();
    print_r($this->results);
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