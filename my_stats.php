<?php
// ===============================
// my_stats.php — stats du user connecté
// ===============================
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/music_player.php';

$user = current_user();

// 1) Total tests du user
$stmt = $db->prepare("SELECT COUNT(*) FROM results WHERE user_id = :uid");
$stmt->execute([':uid' => $user['id']]);
$totalTests = (int)$stmt->fetchColumn();

// 2) Profil le plus fréquent du user
$stmt = $db->prepare("
  SELECT profile, COUNT(*) as c
  FROM results
  WHERE user_id = :uid
  GROUP BY profile
  ORDER BY c DESC
  LIMIT 1
");
$stmt->execute([':uid' => $user['id']]);
$top = $stmt->fetch();
$topProfile = $top ? $top['profile'] : '-';
$topCount   = $top ? (int)$top['c'] : 0;

// 3) Moyennes des scores du user
$stmt = $db->prepare("
  SELECT
    AVG(scoreA) as a,
    AVG(scoreB) as b,
    AVG(scoreC) as c,
    AVG(scoreD) as d
  FROM results
  WHERE user_id = :uid
");
$stmt->execute([':uid' => $user['id']]);
$avg = $stmt->fetch();

// helpers
function f($x){ return round((float)($x ?? 0), 2); }
function pct($part,$total){ if($total<=0) return 0; return (int)round(($part/$total)*100); }

// 4) 10 derniers résultats du user
$stmt = $db->prepare("
  SELECT id, profile, scoreA, scoreB, scoreC, scoreD, created_at
  FROM results
  WHERE user_id = :uid
  ORDER BY id DESC
  LIMIT 10
");
$stmt->execute([':uid' => $user['id']]);
$latest = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Mes stats - <?php echo htmlspecialchars($user['username']); ?></title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/animations.css">

  <style>
    .wrap { max-width: 1000px; margin: 0 auto; padding: 24px; }
    .kpi { display:grid; gap:12px; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); }
    .kpi .card { margin:0; }
    .bar { height:10px; background:rgba(0,0,0,.08); border-radius:999px; overflow:hidden; }
    .bar>div { height:100%; width:0%; border-radius:999px; background:rgba(0,0,0,.45); transition:width .6s ease; }
    table { width:100%; border-collapse:collapse; }
    th,td { padding:10px; border-bottom:1px solid rgba(0,0,0,.08); text-align:left; }
  </style>
</head>
<body>

<header>
  <h1>Mes stats</h1>
  <p class="sub">Connecté en tant que <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
</header>

<div class="wrap">

  <div class="kpi">
    <div class="card">
      <div class="result-title">Total de mes tests</div>
      <p style="font-size:32px;margin:10px 0;"><strong><?php echo $totalTests; ?></strong></p>
      <p class="small">Seulement les tests liés à mon compte.</p>
    </div>

    <div class="card">
      <div class="result-title">Mon profil #1</div>
      <p style="font-size:22px;margin:10px 0;">
        <strong><?php echo htmlspecialchars($topProfile); ?></strong>
      </p>
      <p class="small"><?php echo $topCount; ?> fois (<?php echo pct($topCount,$totalTests); ?>%)</p>
      <div class="bar"><div style="width: <?php echo pct($topCount,$totalTests); ?>%;"></div></div>
    </div>

    <div class="card">
      <div class="result-title">Mes moyennes</div>
      <ul style="margin:10px 0 0;">
        <li><strong>A</strong> : <?php echo f($avg["a"] ?? 0); ?></li>
        <li><strong>B</strong> : <?php echo f($avg["b"] ?? 0); ?></li>
        <li><strong>C</strong> : <?php echo f($avg["c"] ?? 0); ?></li>
        <li><strong>D</strong> : <?php echo f($avg["d"] ?? 0); ?></li>
      </ul>
      <p class="small">CALCUL SQL via <code>AVG()</code> filtré par <code>user_id</code>.</p>
    </div>
  </div>

  <div class="grid" style="margin-top:14px;">
    <div class="card">
      <div class="result-title">Répartition de mes profils</div>

      <?php if ($totalTests === 0): ?>
        <p class="small">Aucun test pour l’instant.</p>
      <?php else: ?>
        <ul>
          <?php
          $stmt = $db->prepare("
            SELECT profile, COUNT(*) as c
            FROM results
            WHERE user_id = :uid
            GROUP BY profile
            ORDER BY c DESC
          ");
          $stmt->execute([':uid' => $user['id']]);
          foreach ($stmt->fetchAll() as $row):
            $p = $row['profile']; $c = (int)$row['c']; $percent = pct($c,$totalTests);
          ?>
            <li style="margin:10px 0;">
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
      <a class="btn" href="index.php">Retour au test</a>
      <?php if (($user['role'] ?? 'user') === 'admin'): ?>
        <a class="btn" href="admin_stats.php" style="margin-left:8px;">Stats admin</a>
      <?php endif; ?>
      <a class="btn" href="logout.php" style="margin-left:8px;">Déconnexion</a>
    </div>
  </div>

  <div class="card" style="margin-top:14px;">
    <div class="result-title">Mes 10 derniers résultats</div>

    <?php if (!$latest): ?>
      <p class="small">Rien à afficher.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Profil</th>
            <th>A</th><th>B</th><th>C</th><th>D</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($latest as $r): ?>
            <tr>
              <td><?php echo (int)$r["id"]; ?></td>
              <td><strong><?php echo htmlspecialchars($r["profile"]); ?></strong></td>
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

</div>
<script src="js/animations.js"></script>
</body>
</html>
