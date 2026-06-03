/**
 * DialogEngine.js
 * Handles narration and dialogue boxes.
 */
export class DialogEngine {
    constructor() {
        this.container = document.getElementById('dialog-overlay');
        this.textElement = document.getElementById('dialog-text');
    }

    show(text, duration = 3000) {
        if (!this.container) return;
        this.textElement.textContent = text;
        this.container.classList.add('active');

        if (this.timeout) clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            this.hide();
        }, duration);
    }

    hide() {
        this.container.classList.remove('active');
    }
}
