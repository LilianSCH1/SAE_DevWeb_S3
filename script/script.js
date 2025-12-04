// Fonction pour ouvrir un modal de page
function openPageModal(url, title) {
    // Créer le modal s'il n'existe pas
    if (!document.getElementById('pageModal')) {
        const modalHTML = `
            <div class="modal fade" id="pageModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pageModalLabel">${title}</h5>
                            <button type="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="pageModalBody"></div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    const modal = new bootstrap.Modal(document.getElementById('pageModal'));
    const modalBody = document.getElementById('pageModalBody');

    // Ouvrir le modal
    modal.show();

    // Charger le contenu
    fetch(url)
        .then(response => response.text())
        .then(content => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(content, 'text/html');
            const mainContent = doc.querySelector('.container') || doc.querySelector('body');
            modalBody.innerHTML = '<style>body { font-family: "Raleway", sans-serif; }</style>' + (mainContent ? mainContent.innerHTML : content);
        })
        .catch(error => {
            modalBody.innerHTML = '<p>Erreur de chargement</p>';
        });
}
