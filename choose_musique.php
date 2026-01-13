<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';

$user = current_user();
$error = "";
$ok = "";

// Récupère les musiques
$tracks = $db->query("SELECT id, original_name, stored_name FROM tracks ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Soumission du choix
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $tid = (int)($_POST['track_id'] ?? 0);
  if ($tid <= 0) {
    $error = "Choix invalide.";
  } else {
    // vérifie que la track existe
    $stmt = $db->prepare("SELECT id FROM tracks WHERE id = :t LIMIT 1");
    $stmt->execute([':t' => $tid]);
    if (!$stmt->fetchColumn()) {
      $error = "Musique introuvable.";
    } else {
      // insère un nouveau choix (historique)
      $stmt = $db->prepare("
        INSERT INTO user_music_choice (user_id, track_id)
        VALUES (:u, :t)
      ");
      $stmt->execute([':u' => $user['id'], ':t' => $tid]);
      $ok = "Choix enregistré ✅";
    }
  }
}

// Dernier choix de l’utilisateur
$stmt = $db->prepare("
  SELECT t.original_name
  FROM user_music_choice c
  JOIN tracks t ON t.id = c.track_id
  WHERE c.user_id = :u
  ORDER BY c.id DESC
  LIMIT 1
");
$stmt->execute([':u' => $user['id']]);
$last = $stmt->fetch(PDO::FETCH_ASSOC)['original_name'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Choisir une musique</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <h1>Choisir une musique</h1>
  <p class="sub">Ta musique sera jouée sur l’accueil.</p>
</header>

<div class="card" style="max-width:720px;margin:auto;">
  <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
  <?php if ($ok): ?><div class="success"><?php echo htmlspecialchars($ok); ?></div><?php endif; ?>

  <?php if ($last): ?>
    <p class="small">Dernier choix : <strong><?php echo htmlspecialchars($last); ?></strong></p>
  <?php endif; ?>

  <?php if (!$tracks): ?>
    <p class="small">Aucune musique dispo pour le moment. (Demande à l’admin.)</p>
    <a class="btn" href="index.php">Retour</a>
  <?php else: ?>
    <form method="post">
      <label for="track_id" class="small">Sélectionne :</label>
      <select name="track_id" id="track_id" required>
        <?php foreach ($tracks as $t): ?>
          <option value="<?php echo (int)$t['id']; ?>">
            <?php echo htmlspecialchars($t['original_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button class="btn" type="submit" style="margin-left:8px;">Valider</button>
      <a class="btn" href="index.php" style="margin-left:8px;">Retour</a>
    </form>
  <?php endif; ?>
</div>

<script src="animations.js"></script>
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/animations.css">
</body>
</body>
</html>
