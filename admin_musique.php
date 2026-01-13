<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';
require_role('admin');

$user = current_user();
$msg = "";
$err = "";

$UPLOAD_DIR = __DIR__ . "/music/uploads/";
$PUBLIC_DIR = "music/uploads/";

if (!is_dir($UPLOAD_DIR)) {
  mkdir($UPLOAD_DIR, 0777, true);
}

$allowed = [
  'audio/mpeg' => 'mp3',
  'audio/ogg'  => 'ogg',
  'audio/wav'  => 'wav',
  'audio/x-wav'=> 'wav',
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  if (!isset($_FILES["track"]) || $_FILES["track"]["error"] !== UPLOAD_ERR_OK) {
    $err = "Upload foiré (ou aucun fichier).";
  } else {
    $tmp  = $_FILES["track"]["tmp_name"];
    $name = $_FILES["track"]["name"];
    $size = (int)$_FILES["track"]["size"];

    // Détecter le vrai mime
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);

    if (!isset($allowed[$mime])) {
      $err = "Format refusé. Autorisé : mp3 / ogg / wav.";
    } elseif ($size > 15 * 1024 * 1024) {
      $err = "Fichier trop lourd (max 15MB).";
    } else {
      $ext = $allowed[$mime];
      $stored = "track_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;

      if (!move_uploaded_file($tmp, $UPLOAD_DIR . $stored)) {
        $err = "Impossible de déplacer le fichier upload.";
      } else {
        $stmt = $db->prepare("
          INSERT INTO tracks (original_name, stored_name, mime, size_bytes, uploaded_by)
          VALUES (:o, :s, :m, :b, :u)
        ");
        $stmt->execute([
          ':o' => $name,
          ':s' => $stored,
          ':m' => $mime,
          ':b' => $size,
          ':u' => $user['id'],
        ]);

        $msg = "Musique upload ✅ : " . htmlspecialchars($name);
      }
    }
  }
}

// Liste des tracks
$tracks = $db->query("
  SELECT t.*, u.username
  FROM tracks t
  LEFT JOIN users u ON u.id = t.uploaded_by
  ORDER BY t.id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin - Musiques</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <h1>Admin — Musiques</h1>
  <p class="sub">Upload des musiques que les users pourront choisir</p>
</header>

<div class="wrap">
  <?php if ($err): ?><div class="alert"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="card"><?php echo $msg; ?></div><?php endif; ?>

  <div class="card">
    <div class="result-title">Uploader une musique</div>
    <form method="post" enctype="multipart/form-data">
      <input class="input" type="file" name="track" accept=".mp3,.ogg,.wav,audio/*" required>
      <button class="btn" type="submit" style="margin-top:12px;">Upload</button>
      <a class="btn" href="admin_stats.php" style="margin-left:8px;">Retour stats</a>
    </form>
    <p class="small" style="margin-top:10px;">Formats autorisés : mp3 / ogg / wav — max 15MB.</p>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="result-title">Musiques disponibles</div>

    <?php if (!$tracks): ?>
      <p class="small">Aucune musique upload pour l’instant.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Upload par</th>
            <th>Taille</th>
            <th>Pré-écoute</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tracks as $t): ?>
            <tr>
              <td><?php echo (int)$t["id"]; ?></td>
              <td><?php echo htmlspecialchars($t["original_name"]); ?></td>
              <td><?php echo htmlspecialchars($t["username"] ?? "??"); ?></td>
              <td><?php echo round(((int)$t["size_bytes"]) / 1024 / 1024, 2); ?> MB</td>
              <td>
                <audio controls preload="none">
                  <source src="<?php echo "music/uploads/" . rawurlencode($t["stored_name"]); ?>">
                </audio>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>
<link rel="stylesheet" href="css/theme.css">
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
<link rel="stylesheet" href="css/animations.css">
</body>
</html>
