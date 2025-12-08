function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            const icon = input.nextElementSibling.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }

// Scroll event listener for navbar
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    const logo = document.getElementById('logo');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
        logo.src = '../icons/logos/MyPulse_Black-removebg-preview.png';
    } else {
        navbar.classList.remove('scrolled');
        logo.src = '../icons/logos/MyPulse-removebg-preview.png';
    }
});
