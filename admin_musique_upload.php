<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user = current_user();
if (($user['role'] ?? 'user') !== 'admin') {
  http_response_code(403);
  exit("Accès interdit ❌ (admin only)");
}

$error = '';
$success = '';

// Dossier d’upload
$uploadDir = __DIR__ . '/music/uploads/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (!isset($_FILES['music'])) {
    $error = "Aucun fichier reçu (name='music' manquant).";
  } elseif ($_FILES['music']['error'] !== UPLOAD_ERR_OK) {
    $error = "Upload invalide (code erreur = " . (int)$_FILES['music']['error'] . ")";
  } else {

    $originalName = $_FILES['music']['name'];
    $tmpPath = $_FILES['music']['tmp_name'];

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed = ['mp3', 'wav', 'ogg'];

    if (!in_array($ext, $allowed, true)) {
      $error = "Format non autorisé. Autorisés: mp3, wav, ogg.";
    } else {

      $storedName = uniqid('music_', true) . '.' . $ext;
      $targetPath = $uploadDir . $storedName;

      if (!is_uploaded_file($tmpPath)) {
        $error = "Le fichier temporaire n’est pas un upload valide.";
      } elseif (!move_uploaded_file($tmpPath, $targetPath)) {
        $error = "Impossible de déplacer le fichier vers music/uploads (droits / chemin).";
      } else {
        // Insert en BDD
        try {
          $stmt = $db->prepare("
            INSERT INTO tracks (original_name, stored_name, uploaded_by)
            VALUES (:o, :s, :uid)
          ");
          $stmt->execute([
            ':o' => $originalName,
            ':s' => $storedName,
            ':uid' => (int)$user['id']
          ]);
          $success = "Musique uploadée ✅";
        } catch (Throwable $e) {
          $error = "Upload OK mais insert BDD KO : " . $e->getMessage();
        }
      }
    }
  }
}

// Liste des musiques
$tracks = [];
try {
  $tracks = $db->query("
    SELECT id, original_name, stored_name, created_at
    FROM tracks
    ORDER BY id DESC
  ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $error = $error ?: ("Table tracks introuvable / erreur SQL : " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin — Upload musique</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/animations.css">
</head>
<body>

<header>
  <h1>Admin — Musiques</h1>
  <p class="sub">Upload MP3/WAV/OGG — visible par les utilisateurs.</p>
  <p class="small">Connecté : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
</header>

<div class="wrap">

  <div class="card">
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <input type="file" name="music" accept=".mp3,.wav,.ogg,audio/*" required>
      <button class="btn" type="submit">Uploader</button>
      <a class="btn" href="admin_musique.php">Retour</a>
    </form>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="result-title">Bibliothèque (<?php echo count($tracks); ?>)</div>

    <?php if (!$tracks): ?>
      <p class="small">Aucune musique pour l’instant.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Fichier</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($tracks as $t): ?>
          <tr>
            <td><?php echo (int)$t['id']; ?></td>
            <td><?php echo htmlspecialchars($t['original_name']); ?></td>
            <td><?php echo htmlspecialchars($t['stored_name']); ?></td>
            <td><?php echo htmlspecialchars($t['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

</body>
</html>
