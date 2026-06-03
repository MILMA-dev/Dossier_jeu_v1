<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) { header('Location: ../index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

$stats = $pdo->query("
    SELECT level_id, COUNT(*) as total_attempts,
    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as successes
    FROM game_sessions
    GROUP BY level_id
")->fetchAll();

$levels = [];
$attempts = [];
$successes = [];

foreach ($stats as $s) {
    $levels[] = "Niveau " . $s['level_id'];
    $attempts[] = (int)$s['total_attempts'];
    $successes[] = (int)$s['successes'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ESCAPE - Statistiques</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="index.php">🏠 Vue globale</a></li>
                <li><a href="users.php">👥 Utilisateurs</a></li>
                <li class="active"><a href="stats.php">📊 Stats</a></li>
                <li><a href="../dashboard.php">🎮 Retour Jeu</a></li>
            </ul>
            <a href="../logout.php" id="sidebar-logout">🚪 Déconnexion</a>
        </nav>
        <main id="dashboard-content">
            <h1>📊 Statistiques des Niveaux</h1>

            <div class="welcome-card" style="margin-bottom: 30px;">
                <canvas id="statsChart" style="max-height: 400px;"></canvas>
            </div>

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

    <script>
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($levels) ?>,
                datasets: [{
                    label: 'Tentatives',
                    data: <?= json_encode($attempts) ?>,
                    backgroundColor: 'rgba(51, 153, 255, 0.5)',
                    borderColor: 'rgba(51, 153, 255, 1)',
                    borderWidth: 1
                }, {
                    label: 'Réussites',
                    data: <?= json_encode($successes) ?>,
                    backgroundColor: 'rgba(0, 255, 204, 0.5)',
                    borderColor: 'rgba(0, 255, 204, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } },
                    x: { grid: { display: false }, ticks: { color: '#fff' } }
                },
                plugins: {
                    legend: { labels: { color: '#fff' } }
                }
            }
        });
    </script>
</body>
</html>
