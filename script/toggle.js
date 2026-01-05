function toggleShow(category) {
    const podium = document.getElementById(category + '-podium');
    const button = podium.querySelector('button');
    const allItems = podium.querySelectorAll('.podium-item');
    const visibleItems = podium.querySelectorAll('.podium-item:not(.d-none)');
    const hiddenItems = podium.querySelectorAll('.podium-item.d-none');

    if (button.textContent === 'Voir plus') {
        // Show more items
        let shown = 0;
        for (let i = 0; i < hiddenItems.length; i++) {
            if (shown < 5) {
                hiddenItems[i].classList.remove('d-none');
                shown++;
            } else {
                break;
            }
        }
        // If all items are now visible, change to "Voir moins"
        if (podium.querySelectorAll('.podium-item.d-none').length === 0) {
            button.textContent = 'Voir moins';
        }
    } else {
        // Hide items beyond the first 3
        for (let i = 3; i < allItems.length; i++) {
            allItems[i].classList.add('d-none');
        }
        button.textContent = 'Voir plus';
    }
}
