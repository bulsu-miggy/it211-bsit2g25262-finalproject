/**
 * js/header.js
 * Handles interactive elements in the site header, such as the user profile dropdown 
 * and logout confirmation dialogs.
 */
document.addEventListener('DOMContentLoaded', function() {
    const userIcon = document.getElementById('userIcon');
    const userDropdown = document.getElementById('userDropdown');

    // Toggle the profile dropdown menu when the user icon is clicked
    if (userIcon && userDropdown) {
        userIcon.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        // Close the dropdown if the user clicks anywhere else on the page
        document.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    }

    // Intercept logout link to show a confirmation if the user has items in their cart
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(event) {
            const cartCount = parseInt(this.dataset.cartCount || '0', 10);
            if (cartCount > 0) {
                event.preventDefault();
                event.stopPropagation();
                Swal.fire({
                    title: 'You still have items in your basket',
                    text: 'If you logout now, your basket will remain saved while you are logged out. Do you want to continue?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2A2A2A',
                    cancelButtonColor: '#888',
                    confirmButtonText: 'Logout anyway',
                    cancelButtonText: 'Stay logged in'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = logoutLink.href;
                    }
                });
            }
        });
    }
});
