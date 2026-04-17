<?php
  session_start();
  if (!isset($_SESSION["username"]) || !isset($_SESSION["password"]))
  {
    header('Location: login.php');
    exit();
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LYNX - Add Product</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  </head>
<body>
  <!-- HEADER -->
  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>

    <nav class="nav" style="display: none;">
        <a href="index.php">HOME</a>
        <a href="women.php">WOMEN</a>
        <a href="men.php">MEN</a>
    </nav>


    <div class="icons">
        <span class="material-symbols-outlined" onclick="openSearchModal()" style="cursor: pointer; transition: all 0.3s;" title="Search">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
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
        <input type="text" id="searchInput" placeholder="Search products..." style="flex: 1; padding: 15px 20px; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: 'Rubik', sans-serif;" autofocus>
        <button type="submit" style="padding: 15px 30px; background: black; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: 'Rubik', sans-serif; font-weight: bold; transition: all 0.3s; white-space: nowrap;">SEARCH</button>
      </form>
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

  <main class="main">
    <div class="form-card">
      <h2 id="add-title">ADD PRODUCT</h2>
      <?php 
      $gender = $_GET['gender'] ?? 'women';
      $cat = $_GET['category'] ?? '';
      $nice_cat = ucwords(str_replace('-', ' ', $cat));
      ?>
      <div class="context-info" style="background: #f8f8f8; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
        <strong>Context:</strong> <?php echo ucfirst($gender); ?> - <?php echo $nice_cat ?: 'General'; ?>
      </div>
      <div class="gender-tabs" style="display: flex; gap: 20px; margin-bottom: 20px; justify-content: center;">
        <button type="button" onclick="switchGender('women')" class="tab-btn <?php echo $gender=='women' ? 'active' : ''; ?>" id="women-tab">WOMEN</button>
        <button type="button" onclick="switchGender('men')" class="tab-btn <?php echo $gender=='men' ? 'active' : ''; ?>" id="men-tab">MEN</button>
      </div>
      <form action="" method="post" enctype="multipart/form-data" id="product-form" onsubmit="setFormAction();">
        <div class="input-group">
          <input type="text" name="name" placeholder="PRODUCT NAME" required>
        </div>
        <div class="input-group">
          <textarea name="description" placeholder="DESCRIPTION" required></textarea>
        </div>
        <div class="input-group">
          <input type="number" step="0.01" name="price" placeholder="PRICE" required>
        </div>
        <div class="input-group">
          <input type="file" name="image" accept=".jpg,.jpeg,.png">
        </div>
        <input type="hidden" name="gender" value="<?php echo $gender; ?>">
        <input type="hidden" name="category" id="category-input" value="<?php echo $cat ?: $gender; ?>">
        <button type="submit" name="submit" class="btn" id="submit-btn"><?php echo $nice_cat ? 'ADD ' . strtoupper($nice_cat) . ' PRODUCT' : 'ADD ' . strtoupper($gender) . ' PRODUCT'; ?></button>
      </form>

      <script>
        let currentGender = '<?php echo $gender; ?>';
        let currentCategory = '<?php echo $cat; ?>';
        function switchGender(gender) {
          currentGender = gender;
          document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
          document.getElementById(gender + '-tab').classList.add('active');
          document.getElementById('category-input').value = currentCategory || gender;
          document.getElementById('gender-input').value = gender;
          const title = document.getElementById('add-title');
          const btn = document.getElementById('submit-btn');
          title.textContent = 'ADD ' + gender.toUpperCase() + (currentCategory ? ' ' + currentCategory.toUpperCase() : '') + ' PRODUCT';
          btn.textContent = 'ADD ' + (currentCategory ? currentCategory.toUpperCase() + ' ' : '') + gender.toUpperCase() + ' PRODUCT';
        }
        function setFormAction() {
          document.getElementById('product-form').action = 'db/action/addproduct.php';
        }

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
      </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      <style>
        .tab-btn { padding: 15px 30px; border: 2px solid black; background: white; color: black; font-weight: bold; border-radius: 25px; cursor: pointer; transition: all 0.3s; }
        .tab-btn.active { background: black; color: white; }
        .tab-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
      </style>
    </div>
  </main>
</body>
</html>
