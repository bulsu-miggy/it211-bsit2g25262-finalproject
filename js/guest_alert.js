// js/guest_alerts.js
$(document).ready(function() {
    $('.guest-view').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Membership Required',
            text: 'Please log in to see full scent profiles and care instructions.',
            icon: 'info',
            confirmButtonColor: '#2A2A2A',
            confirmButtonText: 'Login'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
    });
});