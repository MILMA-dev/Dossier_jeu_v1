<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCAPE - Connectez-vous ou Évadez-vous</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <div class="overlay"></div>
    <main class="auth-container">
        <header class="logo-container">
            <h1 class="logo-escape">ESCAPE</h1>
            <p class="tagline">L'aventure commence ici.</p>
        </header>

        <div class="auth-box">
            <div class="tabs">
                <button class="tab-btn active" data-target="login-form">Connexion</button>
                <button class="tab-btn" data-target="register-form">Inscription</button>
            </div>

            <form id="login-form" class="auth-form active">
                <div class="form-group">
                    <label for="login-username">Nom d'utilisateur</label>
                    <input type="text" id="login-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Mot de passe</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Entrer</button>
            </form>

            <form id="register-form" class="auth-form">
                <div class="form-group">
                    <label for="reg-username">Nom d'utilisateur</label>
                    <input type="text" id="reg-username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="reg-password">Mot de passe</label>
                    <input type="password" id="reg-password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Avatar</label>
                    <div class="avatar-picker">
                        <label><input type="radio" name="avatar" value="🧙" checked> <span>🧙</span></label>
                        <label><input type="radio" name="avatar" value="🕵️"> <span>🕵️</span></label>
                        <label><input type="radio" name="avatar" value="👨‍🚀"> <span>👨‍🚀</span></label>
                        <label><input type="radio" name="avatar" value="🤖"> <span>🤖</span></label>
                        <label><input type="radio" name="avatar" value="🧟"> <span>🧟</span></label>
                    </div>
                </div>
                <button type="submit" class="btn-primary">S'inscrire</button>
            </form>
            <div id="auth-message" class="message"></div>
        </div>
    </main>

    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.target).classList.add('active');
            });
        });

        const handleAuth = async (e, action) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', action);

            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                const msgBox = document.getElementById('auth-message');
                msgBox.textContent = result.message || (result.success ? 'Succès !' : 'Erreur');
                msgBox.className = 'message ' + (result.success ? 'success' : 'error');

                if (result.success && action === 'login') {
                    window.location.href = result.role === 'admin' ? 'admin/index.php' : 'dashboard.php';
                }
                if (result.success && action === 'register') {
                    e.target.reset();
                    setTimeout(() => {
                        document.querySelector('[data-target="login-form"]').click();
                    }, 2000);
                }
            } catch (err) {
                console.error(err);
            }
        };

        document.getElementById('login-form').addEventListener('submit', (e) => handleAuth(e, 'login'));
        document.getElementById('register-form').addEventListener('submit', (e) => handleAuth(e, 'register'));
    </script>
</body>
</html>
