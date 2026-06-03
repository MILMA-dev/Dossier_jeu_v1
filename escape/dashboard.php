<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCAPE - Dashboard</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="dark-theme">
<button id="menu-toggle" onclick="toggleSidebar()">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
</button>

<div id="app-layout">
  <!-- SIDEBAR -->
  <nav id="sidebar">
    <div id="sidebar-logo">
      <span class="logo-escape">ESCAPE</span>
    </div>
    <div id="sidebar-user">
      <span class="user-avatar"><?= h($user['avatar']) ?></span>
      <span class="user-name"><?= h($user['username']) ?></span>
      <span class="user-points"><?= $user['total_points'] ?> pts</span>
    </div>
    <ul id="sidebar-nav">
      <li data-page="home" class="active">🏠 Accueil</li>
      <li data-page="play">🎮 Jouer</li>
      <li data-page="leaderboard">🏆 Classement</li>
      <li data-page="stats">📊 Statistiques</li>
      <li data-page="profile">👤 Profil</li>
      <li onclick="toggleTheme()">🌓 Thème</li>
      <?php if (is_admin()): ?>
        <li onclick="window.location.href='admin/index.php'">🛠 Admin</li>
      <?php endif; ?>
    </ul>
    <a href="logout.php" id="sidebar-logout">🚪 Déconnexion</a>
  </nav>

  <!-- CONTENU -->
  <main id="dashboard-content">
    <section id="home-page" class="page active">
        <h1>Bienvenue, <?= h($user['username']) ?></h1>
        <div class="welcome-card">
            <p>Êtes-vous prêt pour votre prochaine évasion ?</p>
            <button class="btn-primary" onclick="navigateTo('play')">Commencer à jouer</button>
        </div>
    </section>

    <section id="play-page" class="page">
        <h1>Choix du Niveau</h1>
        <div class="level-grid">
            <?php for($i=1; $i<=5; $i++): ?>
            <div class="level-card">
                <h3>Niveau <?= $i ?></h3>
                <button class="btn-play" onclick="window.location.href='game.php?level=<?= $i ?>'">Lancer</button>
            </div>
            <?php endfor; ?>
        </div>
    </section>

    <section id="leaderboard-page" class="page">
        <h1>Classement Mondial</h1>
        <div id="leaderboard-container">
            <!-- Loading via JS -->
        </div>
    </section>

    <section id="profile-page" class="page">
        <h1>Votre Profil</h1>
        <form id="profile-form" class="welcome-card" style="max-width: 500px;">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" value="<?= h($user['username']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= h($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Noueau mot de passe (laisser vide si inchangé)</label>
                <input type="password" name="password">
            </div>
            <div class="form-group">
                <label>Avatar</label>
                <div class="avatar-picker">
                    <?php foreach(['🧙','🕵️','👨‍🚀','🤖','🧟'] as $a): ?>
                        <label>
                            <input type="radio" name="avatar" value="<?= $a ?>" <?= $user['avatar'] == $a ? 'checked' : '' ?>>
                            <span><?= $a ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <div id="profile-msg" style="margin-top: 10px;"></div>
        </form>
    </section>
  </main>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
    }

    function toggleTheme() {
        document.body.classList.toggle('light-theme');
        document.body.classList.toggle('dark-theme');
    }

    function navigateTo(pageId) {
        document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('#sidebar-nav li').forEach(l => l.classList.remove('active'));

        const page = document.getElementById(pageId + '-page');
        if (page) page.classList.add('active');

        const navItem = document.querySelector(`[data-page="${pageId}"]`);
        if (navItem) navItem.classList.add('active');

        if (pageId === 'leaderboard') loadLeaderboard();
    }

    document.querySelectorAll('#sidebar-nav li[data-page]').forEach(li => {
        li.addEventListener('click', () => {
            navigateTo(li.dataset.page);
            if (window.innerWidth <= 768) toggleSidebar();
        });
    });

    document.getElementById('profile-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const msg = document.getElementById('profile-msg');

        try {
            const response = await fetch('api/update_profile.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            msg.textContent = result.message;
            msg.style.color = result.success ? 'var(--primary-color)' : 'var(--error-color)';
            if (result.success) setTimeout(() => window.location.reload(), 1500);
        } catch (err) {
            msg.textContent = "Erreur de connexion";
        }
    });

    async function loadLeaderboard() {
        const container = document.getElementById('leaderboard-container');
        container.innerHTML = 'Chargement...';
        try {
            const response = await fetch('api/leaderboard.php');
            const data = await response.json();

            let html = '<table><thead><tr><th>Rang</th><th>Joueur</th><th>Score</th></tr></thead><tbody>';
            data.forEach((row, index) => {
                html += `<tr><td>${index + 1}</td><td>${row.username}</td><td>${row.score}</td></tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        } catch (e) {
            container.innerHTML = 'Erreur lors du chargement.';
        }
    }
</script>
</body>
</html>
