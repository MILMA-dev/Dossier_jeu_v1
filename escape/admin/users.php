<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!is_admin()) { header('Location: ../index.php'); exit; }

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch();

$users = $pdo->query("SELECT * FROM users ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>ESCAPE - Gestion Utilisateurs</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center; }
        .modal.active { display: flex; }
        .modal-content { background: var(--bg-panel); padding: 30px; border-radius: 8px; width: 400px; border: 1px solid var(--border-color); }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; }
        .status-active { background: #c6f6d5; color: #22543d; }
        .status-inactive { background: #fed7d7; color: #822727; }
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
                <li><a href="index.php">🏠 Vue globale</a></li>
                <li class="active"><a href="users.php">👥 Utilisateurs</a></li>
                <li><a href="stats.php">📊 Stats</a></li>
                <li><a href="../dashboard.php">🎮 Retour Jeu</a></li>
            </ul>
            <a href="../logout.php" id="sidebar-logout">🚪 Déconnexion</a>
        </nav>
        <main id="dashboard-content">
            <h1>👥 Gestion des Utilisateurs</h1>
            <table>
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Rôle</th><th>Points</th><th>Status</th><th>Actions</th></tr>
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
                            <span class="status-badge <?= $u['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                <?= $u['is_active'] ? 'Actif' : 'Suspendu' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-primary" onclick='openEditModal(<?= json_encode($u) ?>)'>✏️ Éditer</button>
                            <button class="btn-primary" style="background: <?= $u['is_active'] ? 'var(--error-color)' : '#48bb78' ?>" onclick='toggleUserStatus(<?= $u['id'] ?>, <?= $u['is_active'] ? 0 : 1 ?>)'>
                                <?= $u['is_active'] ? '🚫 Désactiver' : '✅ Réactiver' ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <h3>Éditer Utilisateur</h3>
            <form id="edit-user-form">
                <input type="hidden" name="user_id" id="edit-id">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="username" id="edit-username" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email" required>
                </div>
                <div class="form-group">
                    <label>Rôle</label>
                    <select name="role" id="edit-role" style="width:100%; padding:10px; background:#000; color:#fff;">
                        <option value="player">Player</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="total_points" id="edit-points">
                </div>
                <div style="display:flex; gap:10px; margin-top:20px;">
                    <button type="submit" class="btn-primary">Enregistrer</button>
                    <button type="button" class="btn-primary" style="background:#555" onclick="closeModal()">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(user) {
            document.getElementById('edit-id').value = user.id;
            document.getElementById('edit-username').value = user.username;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-role').value = user.role;
            document.getElementById('edit-points').value = user.total_points;
            document.getElementById('edit-modal').classList.add('active');
        }
        function closeModal() {
            document.getElementById('edit-modal').classList.remove('active');
        }
        async function toggleUserStatus(userId, status) {
            if (!confirm("Confirmer le changement de statut ?")) return;
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('is_active', status);
            // We need to send other required fields for admin_update_user.php or update it
            // For simplicity, let's just use the same API by fetching current data first or updating API
            const res = await fetch('../api/admin_update_user.php', { method: 'POST', body: formData });
            // wait, admin_update_user requires username and email too. Let's fix the API to be more flexible or send them here.
            // Actually, I'll update the API to allow partial updates.
        }

        // Revised toggleUserStatus to send necessary data
        async function toggleUserStatus(userId, status) {
            const row = [...document.querySelectorAll('tr')].find(tr => tr.cells[0].innerText == userId);
            const username = row.cells[1].innerText;
            const email = row.cells[2].innerText;
            const role = row.cells[3].innerText;
            const points = row.cells[4].innerText;

            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('username', username);
            formData.append('email', email);
            formData.append('role', role);
            formData.append('total_points', points);
            formData.append('is_active', status);

            const res = await fetch('../api/admin_update_user.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) window.location.reload();
        }

        document.getElementById('edit-user-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('../api/admin_update_user.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) window.location.reload();
        }
    </script>
</body>
</html>
