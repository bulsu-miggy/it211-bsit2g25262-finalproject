$(document).ready(function() {
  $('.logout-trigger').click(function(e) {
    e.preventDefault();
    Swal.fire({
      title: 'Are you sure?',
      text: "You will be logged out!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#000000',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Yes, logout!'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = 'logout.php';
      }
    });
  });
});
