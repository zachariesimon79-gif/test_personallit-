<?php
// db.php : connexion + auto-création des tables SQLite

try {
    $db = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Active les clés étrangères
    $db->exec("PRAGMA foreign_keys = ON;");

    // ✅ Crée les tables ICI (dans le try) sinon ça sert à rien
    $db->exec("
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT NOT NULL UNIQUE,
      password_hash TEXT NOT NULL,
      role TEXT NOT NULL DEFAULT 'user',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ");

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

} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}
