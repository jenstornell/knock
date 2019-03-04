<?php
class KnockCore {
  private $userpath;
  private $cookiepath;

  // Set options
  public function __construct() {
    $this->basepath = __DIR__;
    $this->userpath = $this->basepath . '/users/';
    $this->temp_path = $this->basepath . '/temp/';
    $this->cookiepath = '/misc/knock/';
    $this->prefix = 'knock';
  }

  private function defaults() {

  }

  // Check if user is authorized with post variables
  public function isAuthorized() {
    if(!isset($_POST['username'])) return;
    if(!isset($_POST['password'])) return;
    
    $password = include($this->userpath . $_POST['username'] . '.php');

    if($password == $_POST['password']) return true;
  }

  // Login with post variables
  public function login() {
    if(!$this->isAuthorized()) return;

    $hash = bin2hex(random_bytes(16));

    if(!$this->writeCookie($hash)) return;
    if(!$this->writeFile($hash)) return;
  }

  // Logout
  public function logout() {
    $this->deleteCookies();
  }

  // Delete cookies
  private function deleteCookies() {
    if(!setcookie($this->prefix . '[username]', '', 0, $this->cookiepath)) return;
    if(!setcookie($this->prefix . '[hash]', '', 0, $this->cookiepath)) return;
    return true;
  }

  // Write cookie on login
  private function writeCookie($hash) {
    setcookie($this->prefix . '[username]', $_POST['username'], 2147483647, $this->cookiepath);
    setcookie($this->prefix . '[hash]', $hash, 2147483647, $this->cookiepath);
  }

  // Write temp file to disc
  private function writeFile($hash) {
    return file_put_contents($this->temp_path . $_POST['username'] . '.php', "<?php return '" . $hash . "';");
  }
}

// Static helper class
class knock {
  // isLoggedIn
  public static function isLoggedIn() {
    $core = new KnockCore();
    return $core->isLoggedIn();
  }

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
}

$_POST['username'] = 'test@example.com';
$_POST['password'] = 'test';

//echo knock::isLoggedIn();
//echo knock::login();
echo knock::logout();

//print_r($_POST);

print_r($_COOKIE);

// TODO
/*
cookie expire option
login hook i options
logout hook i options
knock::isLoggedIn()
Docs - Byt ut test password mot md5
Docs - options
Docs - License
Docs - Donate
options
*/