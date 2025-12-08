function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text'; icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password'; icon.className = 'bi bi-eye';
            }
        }