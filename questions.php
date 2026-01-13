<?php
require __DIR__ . '/auth.php';
require __DIR__ . '/require_login.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/require_login.php'; // 🔒 oblige la connexion
$user = current_user();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$data = require __DIR__ . "/data.php";
$questions = $data["questions"];
$title = $data["meta"]["title"];

$total = count($questions);

// Si on revient sur la page avec ?err=1
$showError = isset($_GET["err"]) && $_GET["err"] === "1";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Questions - <?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/components.css">
   <link rel="stylesheet" href="css/animations.css">

</head>
<body>

<header>
  <h1>Questions</h1>
  <p class="sub">Réponds à tout, sinon le résultat te mettra un carton rouge.</p>

  <!-- // AJOUT : connecté en tant que + actions - START -->
  <p class="small" style="margin-top:10px;">
    Connecté en tant que <strong><?php echo htmlspecialchars($user['username']); ?></strong>
    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
      <span class="badge" style="margin-left:8px;">ADMIN</span>
    <?php endif; ?>
  </p>

  <div style="margin-top:10px;">
    <a class="btn" href="my_stats.php">Mes stats</a>
    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
      <a class="btn" href="admin_stats.php" style="margin-left:8px;">Stats admin</a>
    <?php endif; ?>
    <a class="btn" href="logout.php" style="margin-left:8px;">Déconnexion</a>
  </div>
  <!-- // AJOUT : connecté en tant que + actions - END -->
</header>

<?php if ($showError): ?>
  <div class="alert">
    Il manque des réponses 😅 Reviens en arrière et répond à toutes les questions.
  </div>
<?php endif; ?>

<form class="card" action="resultat.php" method="post" id="quizForm">

  <div class="progress-wrap">
    <div class="small">Progression : <span id="progressText">0</span> / <?php echo $total; ?></div>
    <div class="progress"><div id="progressBar"></div></div>
  </div>

  <?php foreach ($questions as $index => $q): ?>
    <?php
      $qid = $q["id"];
      $num = $index + 1;
    ?>
    <div class="card">
      <div class="q-title"><?php echo $num . ". " . htmlspecialchars($q["title"]); ?></div>

      <?php foreach ($q["choices"] as $key => $label): ?>
        <label class="choice">
          <input type="radio"
                 name="<?php echo htmlspecialchars($qid); ?>"
                 value="<?php echo htmlspecialchars($key); ?>"
                 required>
          <strong><?php echo htmlspecialchars($key); ?></strong> — <?php echo htmlspecialchars($label); ?>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>

  <button class="btn" type="submit">Voir mon résultat</button>
  <a class="btn" href="index.php" style="margin-left:8px;">Retour</a>
</form>

<script>
(function(){
  const total = <?php echo (int)$total; ?>;
  const form = document.getElementById("quizForm");
  const progressText = document.getElementById("progressText");
  const progressBar = document.getElementById("progressBar");

  function updateProgress(){
    const names = new Set();
    form.querySelectorAll('input[type="radio"]').forEach(i => names.add(i.name));

    let answered = 0;
    names.forEach(name => {
      if (form.querySelector('input[name="'+name+'"]:checked')) answered++;
    });

    progressText.textContent = answered;
    const pct = total ? Math.round((answered / total) * 100) : 0;
    progressBar.style.width = pct + "%";
  }

  form.addEventListener("change", updateProgress);
  updateProgress();
})();
</script>

<audio id="bgMusic" loop>
  <source src="music/uploads" type="audio/mpeg">
</audio>

<script src="js/animations.js"></script>
</body>  
</html>
