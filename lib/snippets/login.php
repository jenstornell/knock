<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CONFIG</title>
  <link rel="stylesheet" href="<?= \knock\option('url'); ?>/assets/css/dist/knock.min.css?time=<?= time(); ?>">
</head>
<body>

<main id="login">
  <div class="logo">
    <figure class="text">
      CONFIG
    </figure>
  </div>
  <form method="post">
    <label for="username">Username</label>
    <input type="text" name="username" value="<?= isset($_POST['username']) ? $_POST['username'] : ''; ?>">

    <label for="password">Password</label>
    <input type="password" name="password" value="<?= isset($_POST['password']) ? $_POST['password'] : ''; ?>">

    <input type="submit">
  </form>
</main>

</body>
</html>