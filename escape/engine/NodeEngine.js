/**
 * NodeEngine.js
 * Handles movement between different views (nodes) in the game.
 */
export class NodeEngine {
    constructor(game) {
        this.game = game;
        this.currentNode = null;
        this.levelData = null;
    }

    loadLevel(levelData, startNode = 'node_start') {
        this.levelData = levelData;
        this.navigateTo(startNode);
    }

    navigateTo(nodeId) {
        const node = this.levelData.nodes[nodeId];
        if (!node) {
            console.error(`Node ${nodeId} not found in level data.`);
            return;
        }

        this.currentNode = node;
        this.currentNode.id = nodeId;

        // Update visited nodes
        if (!this.game.state.visited_nodes.includes(nodeId)) {
            this.game.state.visited_nodes.push(nodeId);
        }

        // Trigger scene rendering
        this.game.renderer.render(this.currentNode);

        // Notify game of node change
        this.game.onNodeChanged(nodeId);
    }

    getAvailableDirections() {
        return this.currentNode.connections || {};
    }
}
