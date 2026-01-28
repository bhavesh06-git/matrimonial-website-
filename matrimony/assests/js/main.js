// assests/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Animate On Scroll (AOS)
    AOS.init({
        duration: 800, // animation duration in ms
        once: true,    // whether animation should happen only once
        offset: 50,    // offset (in px) from the original trigger point
    });

    // Add scrolled class to navbar on scroll
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Client-side form validation feedback for Bootstrap
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

});