<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php';
require_once __DIR__ . '/music_player.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// // AJOUT : récupérer le user connecté - START
$user = current_user(); // ['id','username','role']
// // AJOUT : récupérer le user connecté - END

// // AJOUT : si quelqu’un arrive ici sans POST, on le renvoie - START
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  header("Location: questions.php");
  exit;
}
// // AJOUT : si quelqu’un arrive ici sans POST - END

$data = require __DIR__ . "/data.php";
$questions = $data["questions"];
$profiles = $data["profiles"];
$title = $data["meta"]["title"];

// Initialisation des scores
$scores = [
  "A" => 0,
  "B" => 0,
  "C" => 0,
  "D" => 0
];

// Récupération + validation
$answers = [];
foreach ($questions as $q) {
  $qid = $q["id"];
  $val = $_POST[$qid] ?? null;

  if (!$val || !isset($scores[$val])) {
    header("Location: questions.php?err=1");
    exit;
  }

  $answers[$qid] = $val;
  $scores[$val]++;
}

// Trouver le max
$maxScore = max($scores);

// Gérer les égalités
$winners = [];
foreach ($scores as $k => $v) {
  if ($v === $maxScore) $winners[] = $k;
}

$hasTie = count($winners) > 1;

// Profil principal
$mainKey = $winners[0];
$mainProfile = $profiles[$mainKey];

// =======================
// // ENREGISTRER EN BDD (lié au user) - START
// =======================
$stmt = $db->prepare("
  INSERT INTO results (user_id, profile, scoreA, scoreB, scoreC, scoreD)
  VALUES (:uid, :profile, :a, :b, :c, :d)
");
$stmt->execute([
  ':uid' => $user['id'],
  ':profile' => $mainProfile["name"],
  ':a' => $scores['A'],
  ':b' => $scores['B'],
  ':c' => $scores['C'],
  ':d' => $scores['D'],
]);
// =======================
// // ENREGISTRER EN BDD (lié au user) - END
// =======================


// =======================
// // STATS BDD (OPTIONNEL) — version USER ONLY - START
// =======================
$statsOk = true;
$totalTests = 0;
$topProfile = "-";
$topCount = 0;
$latest = [];

try {
  $stmt = $db->prepare("SELECT COUNT(*) FROM results WHERE user_id = :uid");
  $stmt->execute([':uid' => $user['id']]);
  $totalTests = (int)$stmt->fetchColumn();

  $stmt = $db->prepare("
    SELECT profile, COUNT(*) as c
    FROM results
    WHERE user_id = :uid
    GROUP BY profile
    ORDER BY c DESC
    LIMIT 1
  ");
  $stmt->execute([':uid' => $user['id']]);
  $topRow = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($topRow) {
    $topProfile = $topRow["profile"];
    $topCount = (int)$topRow["c"];
  }

  $stmt = $db->prepare("
    SELECT id, profile, scoreA, scoreB, scoreC, scoreD, created_at
    FROM results
    WHERE user_id = :uid
    ORDER BY id DESC
    LIMIT 10
  ");
  $stmt->execute([':uid' => $user['id']]);
  $latest = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
  $statsOk = false;
}
// =======================
// // STATS BDD (OPTIONNEL) — version USER ONLY - END
// =======================


// Score total
$total = array_sum($scores);

function pct($value, $total) {
  if ($total <= 0) return 0;
  return (int) round(($value / $total) * 100);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résultat - <?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
  <link rel="stylesheet" href="css/animations.css">

</head>
<body>

<header>
  <h1>Résultat</h1>
  <p class="sub">Ok… verdict final. Respire.</p>
  <p class="small">Connecté : <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
</header>

<div class="card">
  <?php if (!$hasTie): ?>
    <div class="result-title">
      <?php echo htmlspecialchars($mainProfile["name"]); ?>
    </div>
    <p class="small"><?php echo htmlspecialchars($mainProfile["short"]); ?></p>
    <p><?php echo htmlspecialchars($mainProfile["desc"]); ?></p>
  <?php else: ?>
    <div class="result-title">Égalité !</div>
    <p>
      T’as un profil mixé entre :
      <strong><?php echo htmlspecialchars($profiles[$winners[0]]["name"]); ?></strong>
      <?php for ($i = 1; $i < count($winners); $i++): ?>
        et <strong><?php echo htmlspecialchars($profiles[$winners[$i]]["name"]); ?></strong>
      <?php endfor; ?>.
    </p>
    <p class="small">Ça arrive quand tes réponses sont ultra équilibrées.</p>
  <?php endif; ?>
</div>

<div class="grid">
  <div class="card">
    <div class="result-title">Détail des scores</div>
    <p class="small">Total réponses : <?php echo $total; ?></p>

    <ul>
      <li><strong>A</strong> : <?php echo $scores["A"]; ?> (<?php echo pct($scores["A"], $total); ?>%)</li>
      <li><strong>B</strong> : <?php echo $scores["B"]; ?> (<?php echo pct($scores["B"], $total); ?>%)</li>
      <li><strong>C</strong> : <?php echo $scores["C"]; ?> (<?php echo pct($scores["C"], $total); ?>%)</li>
      <li><strong>D</strong> : <?php echo $scores["D"]; ?> (<?php echo pct($scores["D"], $total); ?>%)</li>
    </ul>
  </div>

  <div class="card">
    <div class="result-title">Conseils</div>
    <?php
      if ($hasTie) {
        $first = $profiles[$winners[0]];
        $second = $profiles[$winners[1]];
        echo "<p><strong>" . htmlspecialchars($first["name"]) . "</strong></p><ul>";
        foreach ($first["tips"] as $t) echo "<li>" . htmlspecialchars($t) . "</li>";
        echo "</ul><p><strong>" . htmlspecialchars($second["name"]) . "</strong></p><ul>";
        foreach ($second["tips"] as $t) echo "<li>" . htmlspecialchars($t) . "</li>";
        echo "</ul>";
      } else {
        echo "<ul>";
        foreach ($mainProfile["tips"] as $t) echo "<li>" . htmlspecialchars($t) . "</li>";
        echo "</ul>";
      }
    ?>
  </div>
</div>

<div class="card">
  <div class="result-title">Explication “prof”</div>
  <p class="small">
    Les réponses sont envoyées en POST. PHP lit <code>$_POST</code>, calcule un score A/B/C/D,
    puis enregistre le résultat en BDD avec un <code>user_id</code> (relation entre tables).
  </p>

  <?php if ($statsOk): ?>
    <button class="btn" type="button" id="toggleStats" style="margin-top:10px;">
      Afficher / Masquer mes stats BDD
    </button>

    <div id="statsBox" class="card" style="display:none; margin-top:14px;">
      <div class="result-title">Mes stats (optionnel)</div>

      <p><strong>Total de mes tests :</strong> <?php echo $totalTests; ?></p>
      <p><strong>Mon profil le plus fréquent :</strong> <?php echo htmlspecialchars($topProfile); ?> (<?php echo $topCount; ?>)</p>

      <div style="margin-top:12px;">
        <strong>Mes 10 derniers résultats :</strong>
        <?php if (!$latest): ?>
          <p class="small">Aucun résultat pour l’instant.</p>
        <?php else: ?>
          <table style="width:100%; border-collapse:collapse; margin-top:8px;">
            <thead>
              <tr>
                <th style="text-align:left; padding:6px;">ID</th>
                <th style="text-align:left; padding:6px;">Profil</th>
                <th style="padding:6px;">A</th>
                <th style="padding:6px;">B</th>
                <th style="padding:6px;">C</th>
                <th style="padding:6px;">D</th>
                <th style="text-align:left; padding:6px;">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($latest as $r): ?>
                <tr>
                  <td style="padding:6px;"><?php echo (int)$r["id"]; ?></td>
                  <td style="padding:6px;"><strong><?php echo htmlspecialchars($r["profile"]); ?></strong></td>
                  <td style="padding:6px; text-align:center;"><?php echo (int)$r["scoreA"]; ?></td>
                  <td style="padding:6px; text-align:center;"><?php echo (int)$r["scoreB"]; ?></td>
                  <td style="padding:6px; text-align:center;"><?php echo (int)$r["scoreC"]; ?></td>
                  <td style="padding:6px; text-align:center;"><?php echo (int)$r["scoreD"]; ?></td>
                  <td style="padding:6px;"><?php echo htmlspecialchars($r["created_at"]); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <a class="btn" href="index.php">Recommencer</a>
  <a class="btn" href="questions.php" style="margin-left:8px;">Refaire le questionnaire</a>

  <!-- // AJOUT : liens stats propres - START -->
  <a class="btn" href="my_stats.php" style="margin-left:8px;">Mes stats</a>

<?php if (($user['role'] ?? 'user') === 'admin'): ?>
  <a class="btn" href="admin_stats.php" style="margin-left:8px;">Stats admin</a>
<?php endif; ?>

  <!-- // AJOUT : liens stats propres - END -->

</div>

<script>
(function(){
  const btn = document.getElementById("toggleStats");
  const box = document.getElementById("statsBox");
  if (!btn || !box) return;

  btn.addEventListener("click", () => {
    if (box.style.display === "none") {
      box.style.display = "block";
      btn.textContent = "Masquer mes stats BDD";
    } else {
      box.style.display = "none";
      btn.textContent = "Afficher / Masquer mes stats BDD";
    }
  });
})();
</script>

<audio id="bgMusic" loop>
  <source src="music/uploads" type="audio/mpeg">
</audio>

<script src="js/animations.js"></script>
</body>
</html>
