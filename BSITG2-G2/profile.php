<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annyeong Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-primary bg-opacity-10" style="min-height: 100vh;">

<?php include '../header.php'; ?>

    <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <button class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>UPDATE PROFILE
                </button>
                </div>

            <form class="row g-4 bg-white p-4 rounded-4 shadow-sm">
                <div class="col-md-6">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">First Name</label>
                    <input type="text" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">Last Name</label>
                    <input type="text" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>

                <div class="col-md-6">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">Email</label>
                    <input type="email" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>

                <div class="col-md-6">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">Phone Number</label>
                    <input type="tel" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>

                <div class="col-md-9">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">Address</label>
                    <input type="text" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label mb-1 ms-1 text-dark fw-semibold">Zip Code</label>
                    <input type="text" class="form-control border-0 rounded-4 py-2 bg-secondary bg-opacity-10 shadow-sm">
                </div>

                <div class="col-12 text-center mt-5">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow">SAVE CHANGES</button>
                </div>
            </form>

        </div>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>