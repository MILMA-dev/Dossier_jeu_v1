/**
 * SceneRenderer.js
 * Renders the CSS 3D scene based on the current node.
 */
export class SceneRenderer {
    constructor(container) {
        this.container = container;
        this.sceneElement = null;
    }

    render(node) {
        this.container.innerHTML = '';

        // Create main scene container
        const scene = document.createElement('div');
        scene.className = 'scene-3d';
        scene.style.backgroundImage = `url(${node.background})`;

        // Render directional arrows
        if (node.connections) {
            Object.entries(node.connections).forEach(([direction, targetId]) => {
                const arrow = document.createElement('div');
                arrow.className = `nav-arrow arrow-${direction}`;
                arrow.innerHTML = this.getArrowIcon(direction);
                arrow.onclick = () => window.game.engine.navigateTo(targetId);
                scene.appendChild(arrow);
            });
        }

        // Render interactive objects (puzzles, items, etc.)
        if (node.objects) {
            node.objects.forEach(obj => {
                const element = document.createElement('div');
                element.className = 'interactive-object';
                element.style.left = obj.x + '%';
                element.style.top = obj.y + '%';
                element.style.width = obj.w + '%';
                element.style.height = obj.h + '%';

                if (obj.image) {
                    element.style.backgroundImage = `url(${obj.image})`;
                }

                element.onclick = () => window.game.handleObjectInteraction(obj);
                scene.appendChild(element);
            });
        }

        this.container.appendChild(scene);
        this.sceneElement = scene;

        // Apply entry animation
        scene.classList.add('fade-in');
    }

    getArrowIcon(direction) {
        const icons = {
            'north': '↑',
            'south': '↓',
            'east': '→',
            'west': '←',
            'up': '▲',
            'down': '▼'
        };
        return icons[direction] || '→';
    }
}
