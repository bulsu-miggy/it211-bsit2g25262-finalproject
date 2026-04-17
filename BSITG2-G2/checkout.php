<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Annyeong Haven</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body style="background-color: #D6E4FF; min-height: 100vh;">

<?php include '../header.php'; ?>

<div class="container py-5">
    <div class="row g-5">
        
        <div class="col-lg-7">
            <h4 class="fw-bold mb-4" style="color: #2E3E5C;">Shipping Address</h4>
            <form action="process_checkout.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">First Name</label>
                    <input type="text" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Last Name</label>
                    <input type="text" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Email</label>
                    <input type="email" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Phone Number</label>
                    <input type="tel" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-secondary">Address</label>
                    <input type="text" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Zip Code</label>
                    <input type="text" class="form-control border-0 rounded-4 bg-white bg-opacity-50 shadow-sm py-2">
                </div>

                <h4 class="fw-bold mt-5" style="color: #2E3E5C;">Shipping Method</h4>
                <div class="col-md-6">
                    <div class="p-3 bg-white bg-opacity-50 rounded-4 shadow-sm border border-primary border-opacity-10">
                        <div class="form-check d-flex justify-content-between align-items-center">
                            <div>
                                <input class="form-check-input" type="radio" name="shipping" id="free" checked>
                                <label class="form-check-label fw-bold ms-2" for="free">Free Shipping</label>
                                <div class="small text-muted ms-4">4-5 Days</div>
                            </div>
                            <span class="fw-bold">₱0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-white bg-opacity-50 rounded-4 shadow-sm border border-primary border-opacity-10">
                        <div class="form-check d-flex justify-content-between align-items-center">
                            <div>
                                <input class="form-check-input" type="radio" name="shipping" id="express">
                                <label class="form-check-label fw-bold ms-2" for="express">Express Shipping</label>
                                <div class="small text-muted ms-4">2-3 Days</div>
                            </div>
                            <span class="fw-bold">₱50</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="bg-white bg-opacity-50 p-4 border border-dark border-2">
                <h4 class="fw-bold mb-4">Order Information</h4>
                
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-white p-2 rounded shadow-sm text-center" style="width: 80px;">
                        <img src="../images/txt.png" class="img-fluid" alt="TXT">
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0 fw-bold">TXT Official Lightstick</h6>
                        <span class="text-danger fw-bold">₱2,000</span>
                    </div>
                </div>

                <div class="input-group mb-4">
                    <span class="input-group-text bg-secondary bg-opacity-25 border-0 rounded-start-3">
                        <i class="bi bi-tag-fill"></i>
                    </span>
                    <input type="text" class="form-control bg-secondary bg-opacity-25 border-0" placeholder="Discount Voucher">
                    <button class="btn btn-dark opacity-75 border-0 text-white fw-bold rounded-end-3 px-3" type="button">Apply</button>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Subtotal</span>
                    <span class="fw-bold">₱2000</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Voucher</span>
                    <span class="fw-bold">₱30</span>
                </div>
                <div class="d-flex justify-content-between mb-3 border-bottom border-dark pb-2">
                    <span class="text-secondary">Shipping Fee</span>
                    <span class="fw-bold">₱0</span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Total</h4>
                    <h4 class="fw-bold" style="color: #0d6efd;">₱2070</h4>
                </div>

                <button class="btn btn-dark w-100 py-3 fw-bold rounded-3 shadow-sm" style="background-color: #2E3E5C;">
                    Checkout
                </button>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>