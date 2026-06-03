/**
 * Inventory.js
 * Manages player items and their combinations.
 */
export class Inventory {
    constructor(game) {
        this.game = game;
        this.items = [];
        this.selectedItem = null;
        this.container = document.getElementById('inventory-slots');
    }

    addItem(item) {
        this.items.push(item);
        this.render();
        this.game.dialog.show(`Vous avez ramassé : ${item.name}`);
    }

    removeItem(itemId) {
        this.items = this.items.filter(i => i.id !== itemId);
        if (this.selectedItem && this.selectedItem.id === itemId) {
            this.selectedItem = null;
        }
        this.render();
    }

    hasItem(itemId) {
        return this.items.some(i => i.id === itemId);
    }

    selectItem(item) {
        if (this.selectedItem === item) {
            this.selectedItem = null;
        } else {
            this.selectedItem = item;
        }
        this.render();
    }

    render() {
        if (!this.container) return;
        this.container.innerHTML = '';

        this.items.forEach(item => {
            const slot = document.createElement('div');
            slot.className = `inventory-slot ${this.selectedItem === item ? 'selected' : ''}`;
            slot.innerHTML = `<img src="${item.icon}" title="${item.name}">`;
            slot.onclick = () => this.selectItem(item);
            this.container.appendChild(slot);
        });
    }
}
