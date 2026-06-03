<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) { header('Location: ../index.php'); exit; }

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ESCAPE - Gestion Utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div id="app-layout">
        <nav id="sidebar">
            <div id="sidebar-logo"><span class="logo-escape">ADMIN</span></div>
            <ul id="sidebar-nav">
                <li><a href="index.php">Vue globale</a></li>
                <li class="active"><a href="users.php">Utilisateurs</a></li>
                <li><a href="stats.php">Stats</a></li>
                <li><a href="../dashboard.php">Retour Jeu</a></li>
            </ul>
        </nav>
        <main id="dashboard-content">
            <h1>Gestion des Utilisateurs</h1>
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Points</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= h($u['username']) ?></td>
                        <td><?= h($u['email']) ?></td>
                        <td><?= $u['role'] ?></td>
                        <td><?= $u['total_points'] ?></td>
                        <td>
                            <button>Editer</button>
                            <button style="background:red">Ban</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
