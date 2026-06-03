/**
 * AudioEngine.js
 * Manages background music and sound effects.
 */
export class AudioEngine {
    constructor() {
        this.ambiance = new Audio();
        this.ambiance.loop = true;
        this.sfx = new Audio();
    }

    playAmbiance(src) {
        if (this.ambiance.src.includes(src)) return;
        this.ambiance.src = src;
        this.ambiance.play().catch(e => console.log("Audio play blocked by browser"));
    }

    playSFX(src) {
        const effect = new Audio(src);
        effect.play().catch(e => {});
    }

    setVolume(type, volume) {
        if (type === 'ambiance') this.ambiance.volume = volume;
        if (type === 'sfx') this.sfx.volume = volume;
    }
}
