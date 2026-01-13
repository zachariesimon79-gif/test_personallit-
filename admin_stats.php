<?php
// admin_stats.php — ADMIN ONLY (stats globales)

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/music_player.php';

// ✅ admin only
require_role('admin');

$user = current_user();

// --- helper: vérifier si une table existe (SQLite)
function table_exists(PDO $db, string $name): bool {
  $stmt = $db->prepare("SELECT 1 FROM sqlite_master WHERE type='table' AND name=:n LIMIT 1");
  $stmt->execute([':n' => $name]);
  return (bool)$stmt->fetchColumn();
}

function f($x) { return round((float)($x ?? 0), 2); }
function pct($part, $total) {
  if ($total <= 0) return 0;
  return (int)round(($part / $total) * 100);
}

// --- Total tests (global)
$total = (int)$db->query("SELECT COUNT(*) FROM results")->fetchColumn();

// --- Top profil global
$topRow = $db->query("
  SELECT profile, COUNT(*) as c
  FROM results
  GROUP BY profile
  ORDER BY c DESC
  LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$topProfile = $topRow ? $topRow["profile"] : "-";
$topCount   = $topRow ? (int)$topRow["c"] : 0;

// --- Répartition globale
$dist = $db->query("
  SELECT profile, COUNT(*) as c
  FROM results
  GROUP BY profile
  ORDER BY c DESC
")->fetchAll(PDO::FETCH_ASSOC);

// --- Moyennes globales
$avg = $db->query("
  SELECT
    AVG(scoreA) as a,
    AVG(scoreB) as b,
    AVG(scoreC) as c,
    AVG(scoreD) as d
  FROM results
")->fetch(PDO::FETCH_ASSOC);

// --- Derniers résultats + username
$latest = $db->query("
  SELECT r.id, u.username, r.profile, r.scoreA, r.scoreB, r.scoreC, r.scoreD, r.created_at
  FROM results r
  JOIN users u ON u.id = r.user_id
  ORDER BY r.id DESC
  LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

// --- Derniers choix musique (si tables existent)
$musicChoices = [];
$musicOk = table_exists($db, 'user_music_choice') && table_exists($db, 'tracks');

if ($musicOk) {
  $musicChoices = $db->query("
    SELECT c.id, u.username, t.original_name, t.stored_name, c.created_at
    FROM user_music_choice c
    JOIN users u ON u.id = c.user_id
    JOIN tracks t ON t.id = c.track_id
    ORDER BY c.id DESC
    LIMIT 20
  ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Admin - Stats globales</title>

  <!-- ✅ si t’es en CSS splitté -->
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/animations.css">

  <!-- (Si tu veux garder style.css en +, tu peux, mais évite de doubler les règles) -->
  <!-- <link rel="stylesheet" href="style.css"> -->

  <style>
    .admin-wrap { max-width: 1000px; margin: 0 auto; padding: 24px; }
    .kpi { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
    .kpi .card { margin: 0; }
    .bar { height: 10px; background: rgba(0,0,0,.08); border-radius: 999px; overflow: hidden; }
    .bar > div { height: 100%; border-radius: 999px; background: rgba(0,0,0,.45); transition: width .6s ease; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid rgba(0,0,0,.08); text-align: left; }
  </style>
</head>
<body>

<header>
  <h1>Admin — Stats globales</h1>
  <p class="sub">Connecté en admin : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>

  <div style="margin-top:10px;">
    <a class="btn" href="index.php">Retour au test</a>
    <a class="btn" href="admin_musique_upload.php" style="margin-left:8px;">Admin musiques</a>
    <a class="btn" href="logout.php" style="margin-left:8px;">Déconnexion</a>
  </div>
</header>

<div class="admin-wrap">

  <div class="kpi">
    <div class="card">
      <div class="result-title">Total de tests (global)</div>
      <p style="font-size:32px; margin:10px 0;"><strong><?php echo $total; ?></strong></p>
      <p class="small">Tous les utilisateurs confondus.</p>
    </div>

    <div class="card">
      <div class="result-title">Profil #1 (global)</div>
      <p style="font-size:22px; margin:10px 0;">
        <strong><?php echo htmlspecialchars($topProfile); ?></strong>
      </p>
      <p class="small"><?php echo $topCount; ?> fois (<?php echo pct($topCount, $total); ?>%)</p>
      <div class="bar"><div style="width: <?php echo pct($topCount, $total); ?>%;"></div></div>
    </div>

    <div class="card">
      <div class="result-title">Moyennes (global)</div>
      <ul style="margin: 10px 0 0;">
        <li><strong>A</strong> : <?php echo f($avg["a"] ?? 0); ?></li>
        <li><strong>B</strong> : <?php echo f($avg["b"] ?? 0); ?></li>
        <li><strong>C</strong> : <?php echo f($avg["c"] ?? 0); ?></li>
        <li><strong>D</strong> : <?php echo f($avg["d"] ?? 0); ?></li>
      </ul>
    </div>
  </div>

  <div class="grid" style="margin-top:14px;">
    <div class="card">
      <div class="result-title">Répartition globale</div>

      <?php if ($total === 0): ?>
        <p class="small">Aucun test en BDD.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($dist as $row): ?>
            <?php
              $p = $row["profile"];
              $c = (int)$row["c"];
              $percent = pct($c, $total);
            ?>
            <li style="margin: 10px 0;">
              <strong><?php echo htmlspecialchars($p); ?></strong> :
              <?php echo $c; ?> (<?php echo $percent; ?>%)
              <div class="bar" style="margin-top:6px;">
                <div style="width: <?php echo $percent; ?>%;"></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="result-title">Actions</div>
      <a class="btn" href="my_stats.php">Mes stats</a>
      <a class="btn" href="choose_musique.php" style="margin-left:8px;">Choisir une musique</a>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="result-title">Derniers résultats (global)</div>

    <?php if (!$latest): ?>
      <p class="small">Aucun résultat.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Profil</th>
            <th>A</th><th>B</th><th>C</th><th>D</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latest as $r): ?>
            <tr>
              <td><?php echo (int)$r["id"]; ?></td>
              <td><strong><?php echo htmlspecialchars($r["username"]); ?></strong></td>
              <td><?php echo htmlspecialchars($r["profile"]); ?></td>
              <td><?php echo (int)$r["scoreA"]; ?></td>
              <td><?php echo (int)$r["scoreB"]; ?></td>
              <td><?php echo (int)$r["scoreC"]; ?></td>
              <td><?php echo (int)$r["scoreD"]; ?></td>
              <td><?php echo htmlspecialchars($r["created_at"]); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="result-title">Derniers choix de musique</div>

    <?php if (!$musicOk): ?>
      <p class="small">
        Tables musique pas prêtes. Crée <code>tracks</code> + <code>user_music_choice</code> et ça apparaîtra ici.
      </p>
    <?php elseif (!$musicChoices): ?>
      <p class="small">Aucun choix enregistré.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Musique</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($musicChoices as $m): ?>
            <tr>
              <td><?php echo (int)$m["id"]; ?></td>
              <td><strong><?php echo htmlspecialchars($m["username"]); ?></strong></td>
              <td><?php echo htmlspecialchars($m["original_name"]); ?></td>
              <td><?php echo htmlspecialchars($m["created_at"]); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

</div>

<script src="js/animations.js"></script>
</body>
</html>
