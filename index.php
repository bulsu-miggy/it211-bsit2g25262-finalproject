<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"]))
  {
    header('Location: login.php');
    exit();
  }
  
  include 'db/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LYNX - Home</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700;900&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
      .main-banner {
          position: relative;
          height: 100vh;
          background: linear-gradient(to right, #e9e9e9, #f5f5f5);
          overflow: hidden;
      }
      .main-banner .model {
          position: absolute;
          bottom: 0;
          left: 50%;
          transform: translateX(-50%);
          height: 100%;
          width: auto;
          object-fit: cover;
          z-index: 2;
      }
      .main-banner .headline-behind {
          position: absolute;
          top: 40%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-family: 'Rubik', sans-serif;
          font-weight: 900;
          font-size: 15rem;
          color: rgba(0, 0, 0, 0.05);
          z-index: 1;
          white-space: nowrap;
      }
      .main-banner .headline-front {
          position: absolute;
          top: 55%;
          left: 50%;
          transform: translate(-50%, -50%);
          font-family: 'Rubik', sans-serif;
          font-weight: 900;
          font-size: 7rem;
          color: white;
          z-index: 3;
          display: flex;
          flex-direction: column;
          align-items: center;
          text-shadow: 0px 0px 20px rgba(0,0,0,0.2);
      }
      .main-banner .headline-row {
          display: flex;
          align-items: baseline;
          gap: 0.5rem;
      }
      .main-banner .confidence {
          font-size: 9rem;
          margin-top: -1rem;
      }
      .main-banner .sub-labels {
          position: absolute;
          left: 80px;
          top: 70%;
          transform: translateY(-50%);
          z-index: 4;
      }
      .main-banner .new-collection-pill {
          border: 2px solid black;
          border-radius: 50px;
          padding: 10px 25px;
          display: inline-block;
          margin-bottom: 20px;
          color: black;
          font-family: 'Rubik', sans-serif;
          font-weight: 500;
          font-size: 14px;
          letter-spacing: 1px;
      }
      .main-banner .sk8-series {
          font-family: 'Rubik', sans-serif;
          font-weight: 700;
          font-size: 2.5rem;
          color: black;
      }
      .hollow-text {
        -webkit-text-stroke: 2px white;
        -webkit-text-fill-color: transparent;
      }
    </style>
</head>
<body>
  <!-- HEADER -->
  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>

    <nav class="nav">
      <a href="women.php">WOMEN</a>
      <a href="men.php">MEN</a>
    </nav>

    <div class="icons">
        <span class="material-symbols-outlined" onclick="openSearchModal()" style="cursor: pointer; transition: all 0.3s;" title="Search">search</span>
        <a href="shopping-cart.php" title="Cart" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">shopping_cart</span>
        </a>
        <a href="profiles.php" title="Profile" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">account_circle</span>
        </a>
        <a href="#" class="logout-btn" style="color: black; text-decoration: none;">
    <span class="material-symbols-outlined">logout</span>
</a>
    </div>
  </header>

  <!-- SEARCH MODAL -->
  <div id="searchModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; animation: fadeIn 0.3s ease;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 10px 50px rgba(0,0,0,0.3); animation: slideUp 0.3s ease;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin: 0; color: black;">SEARCH</h2>
        <span class="material-symbols-outlined" onclick="closeSearchModal()" style="cursor: pointer; font-size: 28px; color: #666;">close</span>
      </div>
      <form id="searchForm" onsubmit="performSearch(event)" style="display: flex; gap: 10px;">
        <input type="text" id="searchInput" placeholder="Search products by name, brand, style..." style="flex: 1; padding: 15px 20px; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: 'Rubik', sans-serif;" autofocus>
        <button type="submit" style="padding: 15px 30px; background: black; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: 'Rubik', sans-serif; font-weight: bold; transition: all 0.3s; white-space: nowrap;">SEARCH</button>
      </form>
      <p style="font-family: 'Rubik', sans-serif; color: #666; font-size: 0.9rem; margin-top: 15px;">Search for products across our Men's and Women's collections. Enter at least 2 characters to search.</p>
    </div>
  </div>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideUp {
      from { transform: translate(-50%, -40%); opacity: 0; }
      to { transform: translate(-50%, -50%); opacity: 1; }
    }
  </style>

  <!-- Main Banner Full Screen -->
  <div class="main-banner">
      <div class="headline-behind">DETAILS</div>
      
      <img src="images/Main Banner.png" alt="Model" class="model">    

      <div class="headline-front">
          <div class="headline-row">DETAILS <span class="hollow-text">THAT</span> DEFINE</div>
          <div class="confidence">CONFIDENCE</div>
      </div>
  </div>

<section class="best-products" style="padding: 80px 20px; max-width: 1200px; margin: 0 auto;">
    <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 2rem; margin-bottom: 40px; text-align: center;">BEST OF THE BEST</h2>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        <?php
            try {
                // Binago natin ang table name sa 'products' 
                // at nagdagdag ng 'LIMIT 3' para 3 items lang ang lumabas
                $stmt = $conn->prepare("SELECT id, title, price, image_url, sub_category FROM products LIMIT 3");
                $stmt->execute();
                
                if($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <div class="product-card" style="text-align: center; border-radius: 15px; overflow: hidden; transition: 0.3s; border: 1px solid #eee;">
                            <a href="viewProduct.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                                <div style="height: 400px; overflow: hidden; background: #f5f5f5;">
                                    <img src="images/products/<?php echo htmlspecialchars($row['image_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($row['title']); ?>"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                                <div style="padding: 20px;">
                                    <p style="color: #888; font-size: 0.8rem; text-transform: uppercase;"><?php echo htmlspecialchars($row['sub_category']); ?></p>
                                    <h3 style="font-weight: 700; margin: 5px 0;"><?php echo htmlspecialchars($row['title']); ?></h3>
                                    <p style="font-weight: 700; color: #000;">₱<?php echo number_format($row['price'], 2); ?></p>
                                </div>
                            </a>
                        </div>
                        <?php
                    }
                } else {
                    echo "<p style='text-align: center; grid-column: span 3; color: #888;'>No products found in the database.</p>";
                }
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        ?>
    </div>
</section>

  <!-- SK8 Collection Section -->
  <div class="sk8-section" style="background: black; padding: 80px 20px; margin: 0 auto;">
    <div class="sk8-container" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; font-family: 'Rubik', sans-serif; color: white;">
      <!-- Image -->
      <div class="sk8-image">
        <img src="images/skate.png" alt="Skateboarder" style="width: 100%; height: 700px; object-fit: cover; border-radius: 10px;">
      </div>
      
      <!-- Text Content -->
      <div class="sk8-text">
        <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 4rem; font-weight: bold; letter-spacing: 3px; line-height: 1; margin: 0 0 20px 0; text-transform: uppercase;">
          MADE 4 FASHION,<br>
          MADE 4 SK8
        </h2>
        <p style="font-size: 1.3rem; line-height: 1.8; margin-bottom: 30px;">
          Built for movement, made for style. The SK8 Collection delivers durable, functional fits with a street-ready edge—featuring relaxed silhouettes, tough fabrics, and utility details designed to keep up on and off the board. Ride hard, look sharp.
        </p>
        <button style="padding: 15px 40px; font-size: 1.1rem; font-weight: bold; background: transparent; border: 2px solid white; color: white; border-radius: 50px; cursor: pointer; transition: all 0.3s; font-family: 'Rubik', sans-serif;">
          SHOP SK8 COLLECTION
        </button>
      </div>
    </div>
  </div>

  <!-- DISCOVER MORE Products -->
  <div class="discover-section" style="padding: 60px 20px; background: white;">
    <h3 style="font-family: 'Rubik Mono One', sans-serif; font-size: 2.5rem; color: black; letter-spacing: 1px; text-align: center; margin-bottom: 40px;">DISCOVER MORE</h3>
    <div class="category-selection-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; max-width: 1200px; margin: 0 auto;">
      <a href="women.php" class="cat-card-v2" style="text-decoration: none;">
        <div class="img-box" style="height: 500px; overflow: hidden; background: #f0f0f0;">
          <img src="images/WOMAN.png" alt="Women Category" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
        </div>
        <h3 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.5rem; margin-top: 15px; text-align: center; color: black;">WOMEN</h3>
      </a>
      <a href="men.php" class="cat-card-v2" style="text-decoration: none;">
        <div class="img-box" style="height: 500px; overflow: hidden; background: #f0f0f0;">
          <img src="images/MAN.png" alt="Men Category" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
        </div>
        <h3 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.5rem; margin-top: 15px; text-align: center; color: black;">MEN</h3>
      </a>
    </div>
  </div>

  <!-- Footer - Very Bottom -->
  <footer class="footer-banner" style="background: black; padding: 60px 20px; color: white; font-family: 'Rubik', sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 60px; align-items: start;">
      <!-- LYNX Logo -->
      <div>
        <h1 style="font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; font-weight: bold; letter-spacing: 2px; margin: 0; color: white;">LYNX</h1>
      </div>
      
      <!-- SHOP -->
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">SHOP</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">MEN</a></li>
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">WOMEN</a></li>
        </ul>
      </div>
      
      <!-- COMPANY -->
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">COMPANY</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="about.php" style="color: white; text-decoration: none;">ABOUT US</a></li>
        </ul>
      </div>
      
      <!-- BECOME A MEMBER -->
      <div style="text-align: right;">
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">BECOME A MEMBER</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="register.php" style="color: white; text-decoration: none;">JOIN US</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script>
$(document).ready(function() {
    $('.logout-btn').on('click', function(e) {
        e.preventDefault(); 
        
        console.log("Logout clicked!"); 

        Swal.fire({
            title: 'Logout of LYNX?',
            text: "Are you sure you want to sign out?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'logout.php'; 
            }
        });
    });
});
</script>

  <script>
    document.querySelectorAll('.btn-cart').forEach(button => {
      button.addEventListener('click', () => {
        const id = button.dataset.id;
        const table = button.dataset.table;
        fetch('db/action/cart.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `action=add&id=${id}&table=${table}&qty=1`
        }).then(response => response.json()).then(data => {
          if (data.success) alert('Added to cart!');
        });
      });
    });

    // Search Modal Functions
    function openSearchModal() {
      document.getElementById('searchModal').style.display = 'flex';
      document.getElementById('searchInput').focus();
    }

    function closeSearchModal() {
      document.getElementById('searchModal').style.display = 'none';
      document.getElementById('searchInput').value = '';
    }

    function performSearch(event) {
      event.preventDefault();
      const query = document.getElementById('searchInput').value.trim();
      if (query.length >= 2) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
      } else {
        alert('Please enter at least 2 characters to search');
      }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('searchModal');
      if (event.target === modal) {
        closeSearchModal();
      }
    });

    // Allow closing modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeSearchModal();
      }
    });
\n  </script>\n\n  <script>\n  $(document).ready(function() {\n    $('#logout-trigger').click(function(e) {\n      e.preventDefault();\n      Swal.fire({\n        title: 'Are you sure?',\n        text: \"You will be logged out!\",\n        icon: 'warning',\n        showCancelButton: true,\n        confirmButtonColor: '#000000',\n        cancelButtonColor: '#6c757d',\n        confirmButtonText: 'Yes, logout!'\n      }).then((result) => {\n        if (result.isConfirmed) {\n          window.location.href = 'logout.php';\n        }\n      });\n    });\n  });\n  </script>\n</body>\n</html>
