// assets/js/script.js

// Script sederhana untuk menghilangkan alert secara otomatis (fade out)
document.addEventListener("DOMContentLoaded", function() {
    const alerts = document.querySelectorAll('.alert');
    
    if (alerts.length > 0) {
        setTimeout(() => {
            alerts.forEach(alert => {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                
                setTimeout(() => {
                    alert.remove();
                }, 500);
            });
        }, 5000); // Alert hilang setelah 5 detik
    }
});
