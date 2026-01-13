<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user = trim($_POST['user'] ?? '');
  $pass = $_POST['pass'] ?? '';

  if ($user === '' || $pass === '') {
    $error = "Remplis tout chef.";
  } else {
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $user]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($pass, $row['password_hash'])) {
      login_user($row);

      if (($row['role'] ?? 'user') === 'admin') {
        header('Location: admin_stats.php');
      } else {
        header('Location: index.php');
      }
      exit;
    } else {
      $error = "Identifiants incorrects.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Connexion</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/animations.css">

</head>
<body>

<header>
  <h1>Connexion</h1>
  <p class="sub">Montre ton badge 😤</p>
</header>

<div class="card" style="max-width:420px;margin:0 auto;">
  <?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>

  <form method="post">
    <input type="text" name="user" placeholder="Username" required>
    <input type="password" name="pass" placeholder="Mot de passe" required>
    <button class="btn" type="submit">Se connecter</button>
    <a class="btn" href="register.php" style="margin-left:8px;">Créer un compte</a>
  </form>
</div>

</body>
</html>
