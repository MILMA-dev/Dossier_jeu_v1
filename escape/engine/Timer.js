/**
 * Timer.js
 * Handles the game countdown and urgency effects.
 */
export class Timer {
    constructor(duration, onTimeUp) {
        this.initialDuration = duration;
        this.timeLeft = duration;
        this.onTimeUp = onTimeUp;
        this.interval = null;
        this.displayElement = document.getElementById('game-timer');
    }

    start() {
        this.interval = setInterval(() => {
            this.timeLeft--;
            this.updateDisplay();

            if (this.timeLeft <= 30) {
                this.displayElement.classList.add('timer-urgent');
            }

            if (this.timeLeft <= 0) {
                this.stop();
                this.onTimeUp();
            }
        }, 1000);
    }

    stop() {
        clearInterval(this.interval);
    }

    updateDisplay() {
        if (!this.displayElement) return;
        const mins = Math.floor(this.timeLeft / 60);
        const secs = this.timeLeft % 60;
        this.displayElement.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }

    getTimeElapsed() {
        return (this.initialDuration || this.timeLeft + (this.elapsedSeconds || 0)) - this.timeLeft;
    }
}
