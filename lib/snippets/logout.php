<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>CONFIG</title>
  <link rel="stylesheet" href="<?= config::get('url'); ?>/assets/css/dist/style.min.css?time=<?= time(); ?>">
</head>
<body>

<div id="logout">
  <p>You have been logged out!</p>
  <a href="<?= config::get('url') . '/login.php'; ?>">Login again</a>
</div>
</body>
</html>