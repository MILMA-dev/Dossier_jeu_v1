<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db.php';

$level_id = $_GET['level'] ?? 1;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESCAPE - Mission en cours</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/game.css">
    <link rel="stylesheet" href="assets/css/puzzles.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body class="game-body">
    <div id="game-container">
        <!-- HUD Superior -->
        <header id="game-hud">
            <div class="hud-left">
                <span id="level-name">Niveau <?php echo $level_id; ?></span>
            </div>
            <div class="hud-center">
                <div id="game-timer">00:00</div>
            </div>
            <div class="hud-right">
                <button class="btn-settings-toggle" onclick="window.game.toggleSettings()">⚙️</button>
            </div>
        </header>

        <!-- Main Viewport -->
        <div id="viewport">
            <!-- Level Info -->
            <div id="level-info-overlay">
                <h3 id="info-level-name"></h3>
                <p id="info-level-summary"></p>
                <div id="level-objective"></div>
            </div>

            <!-- Map -->
            <div id="game-map"></div>
        </div>

        <!-- Inventory Footer -->
        <footer id="game-footer">
            <div id="inventory-slots">
                <!-- Items injected by JS -->
            </div>
        </footer>

        <!-- Overlays -->
        <div id="dialog-overlay">
            <p id="dialog-text"></p>
        </div>

        <div id="settings-menu">
            <h2>OPTIONS</h2>
            <button class="btn-primary" onclick="window.game.toggleSettings()">Reprendre</button>
            <button class="btn-primary" onclick="window.game.saveState()">Sauvegarder</button>
            <button class="btn-primary" style="background: var(--error-color)" onclick="window.location.href='dashboard.php'">Quitter</button>
        </div>
        <div class="settings-overlay" onclick="window.game.toggleSettings()"></div>

        <div id="puzzle-modal">
            <div class="puzzle-container">
                <button class="close-puzzle" onclick="window.game.puzzle.close()">×</button>
                <div class="puzzle-content"></div>
            </div>
        </div>
    </div>

    <script type="module">
        import { NodeEngine } from './engine/NodeEngine.js';
        import { SceneRenderer } from './engine/SceneRenderer.js';
        import { PuzzleEngine } from './engine/PuzzleEngine.js';
        import { Timer } from './engine/Timer.js';
        import { Inventory } from './engine/Inventory.js';
        import { DialogEngine } from './engine/DialogEngine.js';
        import { AudioEngine } from './engine/AudioEngine.js';

        class Game {
            constructor() {
                this.state = {
                    level_id: <?php echo $level_id; ?>,
                    current_node: 'node_start',
                    inventory: [],
                    puzzle_states: {},
                    visited_nodes: []
                };

                this.renderer = new SceneRenderer(document.getElementById('viewport'));
                this.engine = new NodeEngine(this);
                this.puzzle = new PuzzleEngine(this);
                this.inventory = new Inventory(this);
                this.dialog = new DialogEngine();
                this.audio = new AudioEngine();
                this.timer = null;
                this.isPaused = false;

                window.game = this; // Global access
                this.init();
            }

            async init() {
                try {
                    const response = await fetch(`levels/level${this.state.level_id}.json`);
                    this.levelData = await response.json();

                    document.getElementById('level-name').textContent = this.levelData.name;
                    document.getElementById('info-level-name').textContent = this.levelData.name;
                    document.getElementById('info-level-summary').textContent = this.levelData.summary || this.levelData.description;
                    document.getElementById('level-objective').innerHTML = "🎯 Objectif: " + (this.levelData.objective || "S'échapper");

                    this.renderMap();

                    this.timer = new Timer(this.levelData.time_limit, () => this.gameOver());
                    this.timer.start();

                    this.engine.loadLevel(this.levelData);
                } catch (err) {
                    console.error("Failed to load level:", err);
                    this.dialog.show("Erreur de chargement du niveau.");
                }
            }

            toggleSettings() {
                this.isPaused = !this.isPaused;
                document.getElementById('settings-menu').classList.toggle('open');
                document.querySelector('.settings-overlay').classList.toggle('active');

                if (this.isPaused) {
                    this.timer.stop();
                } else {
                    this.timer.start();
                }
            }

            handleObjectInteraction(obj) {
                if (this.isPaused) return;

                if (obj.type === 'examine') {
                    this.dialog.show(obj.dialog);
                } else if (obj.type === 'puzzle') {
                    if (this.state.puzzle_states[obj.puzzle_id]) {
                        this.dialog.show("Déjà résolu.");
                    } else {
                        const puzzleConfig = this.levelData.puzzles ? this.levelData.puzzles[obj.puzzle_id] : null;
                        this.puzzle.initPuzzle({
                            id: obj.puzzle_id,
                            type: obj.puzzle_type || puzzleConfig.type,
                            solution: obj.solution || (puzzleConfig ? puzzleConfig.solution : ''),
                            pattern: obj.pattern || (puzzleConfig ? puzzleConfig.pattern : '')
                        });
                    }
                } else if (obj.type === 'exit') {
                    if (obj.require_item && !this.inventory.hasItem(obj.require_item)) {
                        this.dialog.show("Il vous faut : " + obj.require_item);
                    } else {
                        this.completeLevel();
                    }
                }
            }

            onPuzzleSolved(puzzleId) {
                this.state.puzzle_states[puzzleId] = true;
                this.dialog.show("Puzzle résolu !");

                if (this.levelData.puzzles && this.levelData.puzzles[puzzleId] && this.levelData.puzzles[puzzleId].reward_item) {
                    this.inventory.addItem(this.levelData.puzzles[puzzleId].reward_item);
                }
                this.saveState();
            }

            onNodeChanged(nodeId) {
                this.state.current_node = nodeId;
                this.updateMap();
                this.saveState();
            }

            renderMap() {
                const mapContainer = document.getElementById('game-map');
                if (!mapContainer || !this.levelData.nodes) return;
                mapContainer.innerHTML = '';

                Object.entries(this.levelData.nodes).forEach(([id, node]) => {
                    if (node.map_x !== undefined) {
                        const dot = document.createElement('div');
                        dot.className = 'map-node';
                        dot.id = `map-node-${id}`;
                        dot.style.left = node.map_x + '%';
                        dot.style.top = node.map_y + '%';
                        mapContainer.appendChild(dot);
                    }
                });
                this.updateMap();
            }

            updateMap() {
                document.querySelectorAll('.map-node').forEach(n => n.classList.remove('active'));
                const activeNode = document.getElementById(`map-node-${this.state.current_node}`);
                if (activeNode) activeNode.classList.add('active');
            }

            async saveState() {
                const formData = new FormData();
                formData.append('action', 'save');
                formData.append('level_id', this.state.level_id);
                formData.append('current_node', this.state.current_node);
                formData.append('inventory', JSON.stringify(this.state.inventory));
                formData.append('puzzle_states', JSON.stringify(this.state.puzzle_states));
                formData.append('time_elapsed', this.timer.getTimeElapsed());

                try {
                    await fetch('api/game_state.php', { method: 'POST', body: formData });
                    if (this.isPaused) this.dialog.show("Progression sauvegardée.");
                } catch(e) {}
            }

            async completeLevel() {
                this.timer.stop();
                const score = Math.max(0, 1000 - (this.timer.getTimeElapsed() * 2));

                const formData = new FormData();
                formData.append('action', 'complete');
                formData.append('level_id', this.state.level_id);
                formData.append('score', score);
                formData.append('time_elapsed', this.timer.getTimeElapsed());

                await fetch('api/game_state.php', { method: 'POST', body: formData });

                this.dialog.show("NIVEAU COMPLÉTÉ ! Score: " + score, 5000);
                setTimeout(() => window.location.href = 'dashboard.php', 3000);
            }

            gameOver() {
                this.dialog.show("TEMPS ÉCOULÉ...", 5000);
                setTimeout(() => window.location.href = 'dashboard.php', 3000);
            }
        }

        new Game();
    </script>
</body>
</html>
