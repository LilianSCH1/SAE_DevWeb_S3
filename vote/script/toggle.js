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

function toggleValideCards() {
            const section = document.getElementById('valide-cards-section');
            const button = event.target;

            if (section.style.display === 'none') {
                section.style.display = 'block';
                button.textContent = 'Masquer les cartes valides';
                button.classList.remove('btn-primary');
                button.classList.add('btn-warning');
            } else {
                section.style.display = 'none';
                button.textContent = 'Afficher les cartes valides';
                button.classList.remove('btn-warning');
                button.classList.add('btn-primary');
            }
        }

        function toggleArchivedCards() {
            const section = document.getElementById('archived-cards-section');
            const button = event.target;

            if (section.style.display === 'none') {
                section.style.display = 'block';
                button.textContent = 'Masquer les archives';
                button.classList.remove('btn-secondary');
                button.classList.add('btn-info');
            } else {
                section.style.display = 'none';
                button.textContent = 'Afficher les archives';
                button.classList.remove('btn-info');
                button.classList.add('btn-secondary');
            }
        }

        function reportComment(commentId) {
            document.getElementById('reportCommentId').value = commentId;
            const modal = new bootstrap.Modal(document.getElementById('reportModal'));
            modal.show();
        }

        function editComment(commentId, currentComment) {
            const newComment = prompt('Modifier le commentaire:', currentComment);
            if (newComment !== null && newComment.trim() !== '') {
                const form = document.createElement('form');
                form.method = 'post';
                form.style.display = 'none';

                const commentIdInput = document.createElement('input');
                commentIdInput.type = 'hidden';
                commentIdInput.name = 'comment_id';
                commentIdInput.value = commentId;

                const commentInput = document.createElement('input');
                commentInput.type = 'hidden';
                commentInput.name = 'comment';
                commentInput.value = newComment.trim();

                const editInput = document.createElement('input');
                editInput.type = 'hidden';
                editInput.name = 'edit_comment';
                editInput.value = '1';

                form.appendChild(commentIdInput);
                form.appendChild(commentInput);
                form.appendChild(editInput);
                document.body.appendChild(form);
                form.submit();
            }
        }