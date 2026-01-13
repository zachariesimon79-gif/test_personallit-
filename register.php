<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$error = "";
$ok = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = trim($_POST["username"] ?? "");
  $pass1 = $_POST["password"] ?? "";
  $pass2 = $_POST["password2"] ?? "";

  if ($username === "" || $pass1 === "" || $pass2 === "") {
    $error = "Remplis tout chef.";
  } elseif (strlen($username) < 3) {
    $error = "Username trop court (min 3).";
  } elseif ($pass1 !== $pass2) {
    $error = "Les mots de passe matchent pas.";
  } elseif (strlen($pass1) < 4) {
    $error = "Mot de passe trop court (min 4).";
  } else {
    // check username déjà pris
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
      $error = "Ce username est déjà pris.";
    } else {
      $hash = password_hash($pass1, PASSWORD_DEFAULT);

      $stmt = $db->prepare("
        INSERT INTO users (username, password_hash, role)
        VALUES (:u, :p, 'user')
      ");
      $stmt->execute([':u' => $username, ':p' => $hash]);

      // auto login après inscription
      $newId = (int)$db->lastInsertId();
      login_user(['id' => $newId, 'username' => $username]);

      header("Location: index.php");
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Créer un compte</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
   <link rel="stylesheet" href="css/animations.css">

</head>
<body>

<header>
  <h1>Créer un compte</h1>
  <p class="sub">Viens on te donne un badge officiel 😎</p>
</header>

<div class="card" style="max-width:420px;margin:0 auto;">
  <?php if ($error): ?><p style="color:red;"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
  <?php if ($ok): ?><p style="color:green;"><?php echo htmlspecialchars($ok); ?></p><?php endif; ?>

  <form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <input type="password" name="password2" placeholder="Confirme le mot de passe" required>
    <button class="btn" type="submit">Créer mon compte</button>
    <a class="btn" href="login.php" style="margin-left:8px;">J’ai déjà un compte</a>
  </form>
</div>

</body>
</html>
