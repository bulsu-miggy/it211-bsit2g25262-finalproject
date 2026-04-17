<?php session_start(); ?>

<?php
  require "../config.php";

  if (!isset($_SESSION["login_data"]))
  {
    session_destroy();
    header('Location: ../login/login.php');
    exit();
  }

  $user_data = $_SESSION["login_data"];
  $userid = null;
  $queryParams = [];
  $stmt = null;

  if (isset($user_data["id"])) {
    $userid = $user_data["id"];
    $stmt = "SELECT * FROM annyeonghaven.user_profile WHERE id = :id";
    $queryParams = [':id' => $userid];
  } elseif (isset($user_data["user_id"])) {
    $userid = $user_data["user_id"];
    $stmt = "SELECT * FROM annyeonghaven.user_profile WHERE user_id = :user_id";
    $queryParams = [':user_id' => $userid];
  } elseif (isset($user_data["username"])) {
    $stmt = "SELECT * FROM annyeonghaven.user_profile WHERE username = :username";
    $queryParams = [':username' => $user_data["username"]];
  } elseif (isset($user_data["email"])) {
    $stmt = "SELECT * FROM annyeonghaven.user_profile WHERE email = :email";
    $queryParams = [':email' => $user_data["email"]];
  } else {
    session_destroy();
    header('Location: ../login/login.php');
    exit();
  }

  try {
    require '../db/dbconfig.php';

    $query = $conn->prepare($stmt);
    $query->execute($queryParams);
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    $user_data = array_shift($result);
    $conn = null;
  } catch(PDOException $e) {
    // handle error if needed
  }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../index/style.css">
    
</head>
<body>

<?php include '../header.php'; ?>

  <div class="container my-5">
    <div class="row">
      <div class="col text-center">
        <h1>Hi, <?php echo htmlspecialchars($user_data["username"]); ?></h1>
        <p class="lead">Welcome back to Annyeong Haven.</p>
      </div>
    </div>
  </div>


<div class="parallax-1"></div>
<div style="height:150px;background-color:white;text-align:center;padding:50px;">
  <h2>Are you dreaming of having a lightstick?</h2>
  <h5>You can purchase your favorite K-Pop lightsticks at Annyeong Haven!</h5>
</div>

  <div class="container mb-5">
    <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
      </div>
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img src="images/txt.png" class="d-block w-100" alt="TXT product">
          <div class="carousel-caption d-md-block">
            <h5>Featured TXT Product</h5>
            <p>Discover the latest official merchandise.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="images/twice.png" class="d-block w-100" alt="TWICE product">
          <div class="carousel-caption d-md-block">
            <h5>New Arrivals</h5>
            <p>Check out the most popular items this season.</p>
          </div>
        </div>
        <div class="carousel-item">
          <img src="images/bts.png" class="d-block w-100" alt="BTS product">
          <div class="carousel-caption d-md-block">
            <h5>Best Sellers</h5>
            <p>Shop the fan favorites in our store.</p>
          </div>
        </div>
      </div>
      <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>

<div class="parallax-2">
  <div style="height:150px;background-color:white;text-align:center;padding:50px;">
    <h2>Annyeong Haven, your K-Pop Paradise.</h2>
    <p>Purchase anytime and anywhere.</p>
  </div>
</div>

<div class="parallax-3">
  <div style="height:20px;background-color:gray;text-align:center;padding:10px;">
  </div>
</div>


<div class="parallax-4"></div>

    <footer class="footer-section pt-5" style="background: linear-gradient(90deg, #000000 0%, #13195b 40%, #3a36c6 100%); color: white;">
      <div class="container pb-5">
        
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="fw-bold display-5">ANNYEONG HAVEN</h1>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Office</h5>
                <p class="mb-1">Malolos City, Bulacan</p>
                <p class="mb-0">annyeonghaven@gmail.com</p>
            </div>
            <div class="col-md-4">
                <h5 class="fw-bold mb-3">Business hours</h5>
                <p class="mb-1">Monday - Friday: 9am - 6pm</p>
                <p class="mb-0">Saturday: 9am - 12pm</p>
            </div>
        </div>

        <div class="row align-items-center py-3 border-top border-bottom border-secondary">
            <div class="col-auto">
                <h5 class="mb-0 fw-bold">Get social</h5>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-3">
                    <a href="#" class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center text-decoration-none fw-bold" style="width: 35px; height: 35px;">f</a>
                    <a href="#" class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center text-decoration-none fw-bold" style="width: 35px; height: 35px;">in</a>
                </div>
            </div>
        </div>
        
      </div>
    </footer>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
</body>
</html>
