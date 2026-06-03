<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if (!$user['is_active']) {
            json_response(['success' => false, 'message' => 'Votre compte est désactivé'], 403);
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['avatar'] = $user['avatar'];

        json_response(['success' => true, 'role' => $user['role']]);
    } else {
        json_response(['success' => false, 'message' => 'Identifiants incorrects'], 401);
    }
}

if ($action === 'register') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $avatar = $_POST['avatar'] ?? '🧙';

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        json_response(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, avatar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash, $avatar]);

        json_response(['success' => true, 'message' => 'Compte créé avec succès']);
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            json_response(['success' => false, 'message' => 'Nom d\'utilisateur ou email déjà utilisé'], 409);
        }
        json_response(['success' => false, 'message' => 'Erreur lors de l\'inscription'], 500);
    }
}

json_response(['success' => false, 'message' => 'Action non reconnue'], 400);
