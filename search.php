<?php
  session_start();
  if (!isset($_SESSION["username"])) {
    header('Location: login.php');
    exit();
  }
  include 'db/connection.php';

  $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
  $results = [];
  
  if (!empty($search_query) && strlen($search_query) >= 2) {
    try {
      // Search in women's products
      $stmt_women = $conn->prepare("
        SELECT id, title as name, excerpt as description, price, imgurl as image, 'women_products' as `table` 
        FROM women_products 
        WHERE title LIKE ? OR excerpt LIKE ? 
        LIMIT 20
      ");
      $search_param = "%$search_query%";
      $stmt_women->execute([$search_param, $search_param]);
      $women_products = $stmt_women->fetchAll(PDO::FETCH_ASSOC);
      
      // Search in men's products
      $stmt_men = $conn->prepare("
        SELECT id, title as name, excerpt as description, price, imgurl as image, 'men_products' as `table` 
        FROM men_products 
        WHERE title LIKE ? OR excerpt LIKE ? 
        LIMIT 20
      ");
      $stmt_men->execute([$search_param, $search_param]);
      $men_products = $stmt_men->fetchAll(PDO::FETCH_ASSOC);
      
      $results = array_merge($women_products, $men_products);
    } catch (PDOException $e) {
      $error = "Search error: " . $e->getMessage();
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LYNX - Search Results</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700;900&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
      .search-page {
        min-height: 100vh;
        background: #f0f2f5;
        padding: 40px 20px;
      }
      .search-container {
        max-width: 1200px;
        margin: 0 auto;
      }
      .search-header {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 50px;
        background: white;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 4px 40px rgba(0,0,0,0.1);
      }
      .search-header input {
        flex: 1;
        padding: 15px 20px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        font-family: 'Rubik', sans-serif;
      }
      .search-header button {
        padding: 15px 40px;
        background: black;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-family: 'Rubik', sans-serif;
        font-weight: bold;
        transition: all 0.3s;
      }
      .search-header button:hover {
        background: #333;
      }
      .search-results-info {
        font-family: 'Rubik', sans-serif;
        font-size: 1.2rem;
        color: #333;
        margin-bottom: 30px;
        font-weight: 500;
      }
      .search-results-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
      }
      .search-result-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        cursor: pointer;
      }
      .search-result-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
      }
      .search-result-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        background: #f0f0f0;
      }
      .search-result-info {
        padding: 20px;
      }
      .search-result-info h3 {
        font-family: 'Rubik', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: #333;
      }
      .search-result-info p {
        font-family: 'Rubik', sans-serif;
        font-size: 0.95rem;
        color: #666;
        margin: 0 0 15px 0;
        line-height: 1.4;
      }
      .search-result-price {
        font-family: 'Rubik Mono One', sans-serif;
        font-size: 1.3rem;
        font-weight: bold;
        color: black;
        margin-bottom: 15px;
      }
      .search-result-buttons {
        display: flex;
        gap: 10px;
      }
      .search-result-btn {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-family: 'Rubik', sans-serif;
        font-weight: bold;
        transition: all 0.3s;
        text-decoration: none;
        text-align: center;
        font-size: 0.9rem;
      }
      .search-result-view {
        background: black;
        color: white;
      }
      .search-result-view:hover {
        background: #333;
      }
      .search-no-results {
        background: white;
        padding: 60px 40px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      }
      .search-no-results h2 {
        font-family: 'Rubik Mono One', sans-serif;
        font-size: 2rem;
        margin-bottom: 20px;
        color: #333;
      }
      .search-no-results p {
        font-family: 'Rubik', sans-serif;
        font-size: 1.1rem;
        color: #666;
        margin-bottom: 30px;
      }
      .search-no-results a {
        display: inline-block;
        padding: 12px 30px;
        background: black;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-family: 'Rubik', sans-serif;
        font-weight: bold;
        transition: all 0.3s;
      }
      .search-no-results a:hover {
        background: #333;
      }
    </style>
</head>
<body>
  <header style="background: white; padding: 15px 40px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <div style="max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
      <h1 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin: 0;">
        <a href="index.php" style="text-decoration: none; color: black;">LYNX</a>
      </h1>
      <nav style="display: flex; gap: 30px;">
        <a href="index.php" style="text-decoration: none; color: black; font-family: 'Rubik', sans-serif; font-weight: 500;">HOME</a>
        <a href="women.php" style="text-decoration: none; color: black; font-family: 'Rubik', sans-serif; font-weight: 500;">WOMEN</a>
        <a href="men.php" style="text-decoration: none; color: black; font-family: 'Rubik', sans-serif; font-weight: 500;">MEN</a>
      </nav>
      <div style="display: flex; gap: 20px; align-items: center;">
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
    </div>
  </header>

  <main class="search-page">
    <div class="search-container">
      <div class="search-header">
        <form action="search.php" method="GET" style="display: flex; width: 100%; gap: 10px;">
          <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" placeholder="Search for products, brands, styles..." autofocus>
          <button type="submit">SEARCH</button>
        </form>
      </div>

      <?php if (!empty($search_query)): ?>
        <div class="search-results-info">
          <?php if (count($results) > 0): ?>
            Found <strong><?php echo count($results); ?></strong> result<?php echo count($results) !== 1 ? 's' : ''; ?> for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
          <?php else: ?>
            No results found for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
          <?php endif; ?>
        </div>

        <?php if (count($results) > 0): ?>
          <div class="search-results-grid">
            <?php foreach($results as $product): ?>
              <div class="search-result-card">
                <img src="<?php 
                  if (!empty($product['image'])) {
                    if (filter_var($product['image'], FILTER_VALIDATE_URL)) {
                      echo $product['image'];
                    } else {
                      echo 'images/products/' . $product['image'];
                    }
                  } else {
                    echo 'https://placehold.co/280x250/000/fff?text=' . urlencode($product['name']);
                  }
                ?>" alt="<?php echo $product['name']; ?>" class="search-result-image">
                <div class="search-result-info">
                  <h3><?php echo $product['name']; ?></h3>
                  <p><?php echo substr($product['description'], 0, 80); ?>...</p>
                  <div class="search-result-price">$<?php echo number_format($product['price'], 2); ?></div>
                  <div class="search-result-buttons">
                    <a href="product-description.php?id=<?php echo $product['id']; ?>&table=<?php echo $product['table']; ?>" class="search-result-btn search-result-view">VIEW</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="search-no-results">
            <h2>No Products Found</h2>
            <p>We couldn't find any products matching your search. Try using different keywords or browse our collections.</p>
            <a href="index.php">Back to Home</a>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="search-no-results">
          <h2>Start Searching</h2>
          <p>Enter a product name, brand, or style above to find what you're looking for.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
$(document).ready(function() {
    $('.logout-btn').on('click', function(e) {
        e.preventDefault(); 
        
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
  <script src="js/main.js"></script>
</body>
</html>
