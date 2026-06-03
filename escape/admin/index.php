<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

// Fetch some KPIs
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$gameCount = $pdo->query("SELECT COUNT(*) FROM game_sessions WHERE status = 'completed'")->fetchColumn();
$avgScore = $pdo->query("SELECT AVG(score) FROM leaderboard")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ESCAPE - Admin</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .kpi-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .kpi-card { background: var(--bg-panel); padding: 25px; border-radius: 12px; text-align: center; border: 1px solid var(--border-color); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .kpi-card h2 { margin-top: 10px; color: var(--primary-color); font-size: 2rem; }
        .kpi-icon { font-size: 2.5rem; margin-bottom: 10px; display: block; }
    </style>
</head>
<body class="dark-theme">
    <div id="app-layout">
        <nav id="sidebar">
            <div id="sidebar-logo"><span class="logo-escape">ADMIN</span></div>
            <div id="sidebar-user">
                <span class="user-avatar"><?= h($admin['avatar']) ?></span>
                <span class="user-name"><?= h($admin['username']) ?></span>
                <span class="user-points"><?= $admin['total_points'] ?> pts</span>
            </div>
            <ul id="sidebar-nav">
                <li class="active"><a href="index.php">🏠 Vue globale</a></li>
                <li><a href="users.php">👥 Utilisateurs</a></li>
                <li><a href="stats.php">📊 Stats</a></li>
                <li><a href="../dashboard.php">🎮 Retour Jeu</a></li>
            </ul>
            <a href="../logout.php" id="sidebar-logout">🚪 Déconnexion</a>
        </nav>
        <main id="dashboard-content">
            <h1>🛠 Dashboard Administration</h1>

            <div class="kpi-container">
                <div class="kpi-card">
                    <span class="kpi-icon">👥</span>
                    <p>Utilisateurs</p>
                    <h2><?= $userCount ?></h2>
                </div>
                <div class="kpi-card">
                    <span class="kpi-icon">🏁</span>
                    <p>Parties Complétées</p>
                    <h2><?= $gameCount ?></h2>
                </div>
                <div class="kpi-card">
                    <span class="kpi-icon">🏆</span>
                    <p>Score Moyen</p>
                    <h2><?= round($avgScore) ?></h2>
                </div>
            </div>

            <h3>🆕 Dernières Inscriptions</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php
                    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
                    foreach ($users as $u) {
                        echo "<tr><td>{$u['id']}</td><td>".h($u['username'])."</td><td>".h($u['email'])."</td><td>{$u['created_at']}</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
