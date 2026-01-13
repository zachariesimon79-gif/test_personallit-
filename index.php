<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/music_player.php';


$user = current_user();
$data = require __DIR__ . "/data.php";
$title = $data["meta"]["title"];
$subtitle = $data["meta"]["subtitle"];
$version = $data["meta"]["version"];

// Musique choisie (dernier choix), sinon fallback
$musicSrc = "music/ambiance.mp3";
$stmt = $db->prepare("
  SELECT t.stored_name
  FROM user_music_choice c
  JOIN tracks t ON t.id = c.track_id
  WHERE c.user_id = :u
  ORDER BY c.id DESC
  LIMIT 1
");
$stmt->execute([':u' => $user['id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ($row && $row['stored_name']) {
  $musicSrc = "music/uploads/" . rawurlencode($row['stored_name']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <h1><?php echo htmlspecialchars($title); ?></h1>
  <p class="sub"><?php echo htmlspecialchars($subtitle); ?></p>
  <p class="small">Version <?php echo htmlspecialchars($version); ?></p>

  <p class="small" style="margin-top:10px;">
    Connecté : <strong><?php echo htmlspecialchars($user['username']); ?></strong>
    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
      <span class="badge" style="margin-left:8px;">ADMIN</span>
    <?php endif; ?>
  </p>

  <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn" href="questions.php">Commencer le test</a>
    <a class="btn" href="my_stats.php">Mes stats</a>
    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
      <a class="btn" href="admin_stats.php">Stats admin</a>
      <a class="btn" href="admin_musique.php">Admin musiques</a>
    <?php endif; ?>
    <a class="btn" href="choose_musique.php">Choisir musique</a>
    <button class="btn" type="button" id="themeToggle">Thème : Auto</button>
    <a class="btn" href="logout.php">Déconnexion</a>
  </div>
</header>

<div class="card">
  <p>
    Tu vas répondre à <strong><?php echo count($data["questions"]); ?> questions</strong>.
    À la fin, tu obtiens un profil parmi <strong><?php echo count($data["profiles"]); ?></strong>.
  </p>
  <p class="small">HTML/CSS pour l’affichage, PHP pour le POST, SQLite pour stocker.</p>
</div>

<audio id="bgMusic" loop>
  <source src="<?php echo htmlspecialchars($musicSrc); ?>" type="audio/mpeg">
</audio>

<script src="animations.js"></script>
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/animations.css">
</body>

</body>
</html>
