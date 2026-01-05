function toggleShow(category) {
    const podium = document.getElementById(category + '-podium');
    const button = event.target;
    const hiddenItems = podium.querySelectorAll('.d-none');

    if (hiddenItems.length > 0) {
        // Show hidden items with transition
        hiddenItems.forEach(item => {
            item.classList.remove('d-none');
            item.classList.add('hidden'); // Start hidden
            setTimeout(() => {
                item.classList.remove('hidden'); // Fade in
            }, 10);
        });
        button.textContent = 'Voir moins';
    } else {
        // Hide items beyond the first 3 with transition
        const allItems = podium.querySelectorAll('.podium-item');
        for (let i = 3; i < allItems.length; i++) {
            allItems[i].classList.add('hidden'); // Fade out
            setTimeout(() => {
                allItems[i].classList.add('d-none');
                allItems[i].classList.remove('hidden');
            }, 300); // Match transition duration
        }
        button.textContent = 'Voir plus';
    }
}
