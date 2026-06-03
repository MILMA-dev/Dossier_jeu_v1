<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) { header('Location: ../index.php'); exit; }

$stats = $pdo->query("
    SELECT level_id, COUNT(*) as total_attempts,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as successes
    FROM game_sessions
    GROUP BY level_id
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ESCAPE - Statistiques</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div id="app-layout">
        <nav id="sidebar">
            <div id="sidebar-logo"><span class="logo-escape">ADMIN</span></div>
            <ul id="sidebar-nav">
                <li><a href="index.php">Vue globale</a></li>
                <li><a href="users.php">Utilisateurs</a></li>
                <li class="active"><a href="stats.php">Stats</a></li>
                <li><a href="../dashboard.php">Retour Jeu</a></li>
            </ul>
        </nav>
        <main id="dashboard-content">
            <h1>Statistiques des Niveaux</h1>
            <table>
                <thead>
                    <tr><th>Niveau</th><th>Tentatives</th><th>Réussites</th><th>Taux de réussite</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($stats as $s): ?>
                    <tr>
                        <td><?= $s['level_id'] ?></td>
                        <td><?= $s['total_attempts'] ?></td>
                        <td><?= $s['successes'] ?></td>
                        <td><?= $s['total_attempts'] > 0 ? round(($s['successes'] / $s['total_attempts']) * 100, 1) : 0 ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
