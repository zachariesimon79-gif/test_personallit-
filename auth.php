<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function is_logged_in(): bool {
  return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function login_user(array $userRow): void {
  $_SESSION['user'] = [
    'id' => $userRow['id'],
    'username' => $userRow['username'],
    'role' => $userRow['role'] ?? ($_SESSION['role'] ?? 'user'),
  ];
  // compat legacy
  $_SESSION['user_id'] = $_SESSION['user']['id'];
  $_SESSION['username'] = $_SESSION['user']['username'];
  $_SESSION['role'] = $_SESSION['user']['role'];
}

function logout_user(): void {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"], $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}

function require_role(string $role): void {
  $u = current_user();
  $r = $u['role'] ?? ($_SESSION['role'] ?? 'user');
  if ($r !== $role) {
    http_response_code(403);
    exit("Accès interdit ❌");
  }
}
