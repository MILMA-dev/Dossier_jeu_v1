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
    <style>
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:2000; justify-content:center; align-items:center; }
        .modal.active { display: flex; }
        .modal-content { background: var(--bg-panel); padding: 30px; border-radius: 8px; width: 400px; border: 1px solid var(--border-color); }
    </style>
</head>
<body>
    <div id="app-layout">
        <nav id="sidebar">
            <div id="sidebar-logo"><span class="logo-escape">ADMIN</span></div>
            <ul id="sidebar-nav">
                <li><a href="index.php">🏠 Vue globale</a></li>
                <li class="active"><a href="users.php">👥 Utilisateurs</a></li>
                <li><a href="stats.php">📊 Stats</a></li>
                <li><a href="../dashboard.php">🎮 Retour Jeu</a></li>
            </ul>
        </nav>
        <main id="dashboard-content">
            <h1>👥 Gestion des Utilisateurs</h1>
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
                            <button class="btn-primary" onclick='openEditModal(<?= json_encode($u) ?>)'>✏️ Éditer</button>
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
        document.getElementById('edit-user-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const res = await fetch('../api/admin_update_user.php', { method: 'POST', body: formData });
            const data = await res.json();
            alert(data.message);
            if (data.success) window.location.reload();
        }
    </script>
</body>
</html>
