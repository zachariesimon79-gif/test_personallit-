<?php
require __DIR__ . '/db.php';

// USERS (avec rôle)
$db->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// RESULTS (liés au user)
$db->exec("
CREATE TABLE IF NOT EXISTS results (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  profile TEXT NOT NULL,
  scoreA INTEGER NOT NULL,
  scoreB INTEGER NOT NULL,
  scoreC INTEGER NOT NULL,
  scoreD INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
");

// TRACKS (musiques uploadées)
$db->exec("
CREATE TABLE IF NOT EXISTS tracks (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  original_name TEXT NOT NULL,
  stored_name TEXT NOT NULL,
  mime TEXT,
  size INTEGER,
  uploaded_by INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
");

// CHOIX MUSIQUE PAR USER
$db->exec("
CREATE TABLE IF NOT EXISTS user_music_choice (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL,
  track_id INTEGER NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (track_id) REFERENCES tracks(id)
);
");

// Crée un admin par défaut si aucun user
$hasUser = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($hasUser === 0) {
  $stmt = $db->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, :r)");
  $stmt->execute([
    ':u' => 'admin',
    ':p' => password_hash('admin123', PASSWORD_DEFAULT),
    ':r' => 'admin',
  ]);
  echo "BDD OK ✅ — Admin par défaut : admin / admin123";
} else {
  echo "BDD OK ✅";
}
