function openPageModal(url, title) {
    // Créer le modal s'il n'existe pas
    if (!document.getElementById('pageModal')) {
        const modalHTML = `
            <div class="modal fade" id="pageModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title" id="pageModalLabel"></h2>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="pageModalBody"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    const modalElement = document.getElementById('pageModal');
    const modal = new bootstrap.Modal(modalElement);
    const modalBody = document.getElementById('pageModalBody');
    const modalTitle = document.getElementById('pageModalLabel');

    // Mettre le titre en MAJUSCULES
    modalTitle.textContent = title.toUpperCase();

    modal.show();

    // Charger le contenu
    fetch(url)
        .then(response => response.text())
        .then(content => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(content, 'text/html');
            const mainContent = doc.querySelector('.container') || doc.querySelector('body');
            modalBody.innerHTML =
                '<style>body { font-family: "Raleway", sans-serif; }</style>' +
                (mainContent ? mainContent.innerHTML : content);
        })
        .catch(error => {
            modalBody.innerHTML = '<p>Erreur de chargement</p>';
        });
}

// Handle artist form submission
document.addEventListener('DOMContentLoaded', function() {
    const addArtistForm = document.getElementById('addArtistForm');
    if (addArtistForm) {
        addArtistForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addArtistModal'));
                    modal.hide();
                    // Reset the form
                    addArtistForm.reset();
                    // Optionally, refresh the artist list or update the UI
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la soumission du formulaire.');
            });
        });
    }
});
