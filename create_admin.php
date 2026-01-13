<?php
require_once __DIR__ . '/db.php';

// ---- CONFIG : choisis tes identifiants ici
$ADMIN_USER = "admin";
$ADMIN_PASS = "admin1234"; // change si tu veux
$ROLE = "admin";

// hash
$hash = password_hash($ADMIN_PASS, PASSWORD_DEFAULT);

// 1) vérifier si le user existe déjà
$stmt = $db->prepare("SELECT id, username, role FROM users WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $ADMIN_USER]);
$exists = $stmt->fetch();

if ($exists) {
  echo "✅ Compte déjà existant : <strong>{$exists['username']}</strong> (role: <strong>{$exists['role']}</strong>)<br>";
  exit;
}

// 2) sinon on le crée
$stmt = $db->prepare("
  INSERT INTO users (username, password_hash, role)
  VALUES (:u, :p, :r)q
");
$stmt->execute([
  ':u' => $ADMIN_USER,
  ':p' => $hash,
  ':r' => $ROLE
]);

echo "✅ Admin créé !<br>";
echo "User: <strong>$ADMIN_USER</strong><br>";
echo "Pass: <strong>$ADMIN_PASS</strong><br>";
echo "➡️ Va sur <a href='login.php'>login.php</a>";
