/**
 * PuzzleEngine.js
 * Manages the logic for the 7 different puzzle types.
 */
export class PuzzleEngine {
    constructor(game) {
        this.game = game;
        this.currentPuzzle = null;
        this.container = document.getElementById('puzzle-modal');
    }

    initPuzzle(puzzleConfig) {
        this.currentPuzzle = puzzleConfig;
        this.renderPuzzle();
        this.container.classList.add('active');
    }

    renderPuzzle() {
        const content = this.container.querySelector('.puzzle-content');
        content.innerHTML = '';

        switch (this.currentPuzzle.type) {
            case 'code_4digits':
                this.renderCode4Digits(content);
                break;
            case 'combination_lock':
                this.renderCombinationLock(content);
                break;
            case 'wire_connect':
                this.renderWireConnect(content);
                break;
            case 'sequence_memory':
                this.renderSequenceMemory(content);
                break;
            case 'cipher':
                this.renderCipher(content);
                break;
            case 'jigsaw':
                this.renderJigsaw(content);
                break;
            case 'pattern_match':
                this.renderPatternMatch(content);
                break;
        }
    }

    // --- PUZZLE 1: Code 4 Digits ---
    renderCode4Digits(container) {
        const keypad = document.createElement('div');
        keypad.className = 'keypad-4digits';
        let currentInput = '';

        const display = document.createElement('div');
        display.className = 'keypad-display';
        display.textContent = '----';
        container.appendChild(display);

        const grid = document.createElement('div');
        grid.className = 'keypad-grid';
        for (let i = 1; i <= 9; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.onclick = () => {
                if (currentInput.length < 4) {
                    currentInput += i;
                    display.textContent = currentInput.padEnd(4, '-');
                    if (currentInput.length === 4) {
                        this.checkCode(currentInput, this.currentPuzzle.solution, display);
                    }
                }
            };
            grid.appendChild(btn);
        }
        container.appendChild(grid);
    }

    checkCode(input, solution, display) {
        if (input === solution) {
            display.classList.add('success');
            setTimeout(() => this.solve(), 1000);
        } else {
            display.classList.add('shake');
            setTimeout(() => {
                display.classList.remove('shake');
                display.textContent = '----';
            }, 500);
            return false;
        }
    }

    // --- PUZZLE 2: Combination Lock ---
    renderCombinationLock(container) {
        const lock = document.createElement('div');
        lock.className = 'combination-lock';
        let vals = [0, 0, 0];

        vals.forEach((v, i) => {
            const wheel = document.createElement('div');
            wheel.className = 'lock-wheel';
            wheel.dataset.index = i;
            wheel.innerHTML = `<div class="wheel-inner">0</div>`;
            wheel.onclick = () => {
                vals[i] = (vals[i] + 1) % 10;
                wheel.querySelector('.wheel-inner').textContent = vals[i];
                wheel.style.transform = `rotateX(${vals[i] * 36}deg)`;
                if (vals.join('') === this.currentPuzzle.solution) this.solve();
            };
            lock.appendChild(wheel);
        });
        container.appendChild(lock);
    }

    // --- PUZZLE 3: Wire Connect ---
    renderWireConnect(container) {
        container.innerHTML = '<p>Connectez les câbles de même couleur</p>';
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute('viewBox', '0 0 400 300');
        svg.className = 'wire-svg';

        const colors = ['red', 'blue', 'green', 'yellow'];
        let connections = 0;

        colors.forEach((color, i) => {
            const start = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            start.setAttribute('cx', 50);
            start.setAttribute('cy', 50 + i * 60);
            start.setAttribute('r', 10);
            start.setAttribute('fill', color);
            start.onclick = () => this.game.dialog.show("Faites glisser vers la destination");
            svg.appendChild(start);

            const end = document.createElementNS("http://www.w3.org/2000/svg", "circle");
            end.setAttribute('cx', 350);
            end.setAttribute('cy', 50 + i * 60);
            end.setAttribute('r', 10);
            end.setAttribute('fill', color);
            end.onclick = () => {
                // Simplified logic: just clicking both in sequence for this demo
                connections++;
                if (connections === colors.length) this.solve();
            };
            svg.appendChild(end);
        });
        container.appendChild(svg);
    }

    // --- PUZZLE 4: Sequence Memory ---
    renderSequenceMemory(container) {
        const grid = document.createElement('div');
        grid.className = 'memory-grid';
        const tiles = [];
        for (let i = 0; i < 9; i++) {
            const tile = document.createElement('div');
            tile.className = 'memory-tile';
            grid.appendChild(tile);
            tiles.push(tile);
        }
        container.appendChild(grid);
        // Logic for sequence playback and player repetition would go here
        // For simplicity:
        setTimeout(() => this.solve(), 5000);
    }

    // --- PUZZLE 5: Cipher ---
    renderCipher(container) {
        const info = document.createElement('div');
        info.innerHTML = `<p class="cipher-hint">A=D, B=E, C=F ...</p>`;
        container.appendChild(info);

        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'cipher-input';
        input.placeholder = 'DECRYPTED CODE';
        input.oninput = () => {
            if (input.value.toUpperCase() === this.currentPuzzle.solution) this.solve();
        };
        container.appendChild(input);
    }

    // --- PUZZLE 6: Jigsaw ---
    renderJigsaw(container) {
        const grid = document.createElement('div');
        grid.className = 'jigsaw-grid';
        // Simplified: 3x3 grid of tiles
        for (let i = 0; i < 9; i++) {
            const tile = document.createElement('div');
            tile.className = 'jigsaw-tile';
            tile.textContent = i;
            tile.onclick = () => {
                // Swap logic or just click through for demo
                this.solve();
            };
            grid.appendChild(tile);
        }
        container.appendChild(grid);
    }

    // --- PUZZLE 7: Pattern Match ---
    renderPatternMatch(container) {
        const target = document.createElement('div');
        target.className = 'pattern-target';
        target.textContent = "Pattern: " + this.currentPuzzle.pattern;
        container.appendChild(target);

        const grid = document.createElement('div');
        grid.className = 'pattern-grid';
        for (let i = 0; i < 9; i++) {
            const btn = document.createElement('button');
            btn.onclick = () => {
                // Logic for matching pattern
                this.solve();
            };
            grid.appendChild(btn);
        }
        container.appendChild(grid);
    }

    solve() {
        this.container.classList.remove('active');
        this.game.onPuzzleSolved(this.currentPuzzle.id);
    }

    close() {
        this.container.classList.remove('active');
    }
}
