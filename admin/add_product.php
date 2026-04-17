<?php
//session start - dito binubuksan yung session para magkaroon ng access ang mga saved variables gaya ng admin_id
session_start();

//login checker - dito naman tinitignan kung ang user ay admin, if wala siyang session id, ibabalik siya sa login page
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // ito naman ung redirect sa login page if walang session id
    exit(); // dito naman sa part na to is iniistop yung pag-load ng script para hindi makita yung mga sensitive data
}

//db connect - dito naman iniimport yung database connection file para magkaroon ng access sa database at magamit yung connection variable ($conn) sa buong script
include('../config/db_connect.php'); 

//form submission - dito sa logic part, kapag pinindot ang button na may name na 'add_product'
if(isset($_POST['add_product'])) {
    
    //data sanitation - yung mysqli_real_escape_string ay para naman ifilter ung ang special characters kumbaga para iwas sa SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_name = mysqli_real_escape_string($conn, $_POST['category']); 
    $price = $_POST['price']; // Kinukuha ang presyo mula sa form.
    $stock = $_POST['stock']; // Kinukuha ang bilang ng stock mula sa form.
    
    //category mapping - kung saan pag sinearch yung 'categories' table, mahanap ung ID na kapares ng napiling category name
    $cat_query = "SELECT id FROM categories WHERE name = '$category_name' LIMIT 1";
    $cat_result = mysqli_query($conn, $cat_query); // dito sa part na to papasok ang ang query sa database.
    
    if($cat_result && mysqli_num_rows($cat_result) > 0) {
        $cat_row = mysqli_fetch_assoc($cat_result); // ung result is ginagawang array
        $category_id = $cat_row['id']; //pagsave ng id sa variable na category_id
    } else {
        $category_id = "NULL"; //eto may naka set na default value if walang mahanap na category
    }

    //image handling - dito ung paghandle sa mga uploaded file gamit ung $_FILES superglobal
    $image = $_FILES['image']['name'];           // kukuha ng original file name.
    $image_size = $_FILES['image']['size'];     // kukuha ng file size.
    $image_tmp_name = $_FILES['image']['tmp_name']; // ginagamit para i-access yung temporary file 
    $image_folder = '../uploads/' . $image;     // eto ginagamit para imove ang file pagkatapos

    // 8. FILE VALIDATION: Sinisiguro na ang image ay hindi lalampas sa 2MB (2 million bytes).
    if($image_size > 2000000) {
        $message[] = 'Image size is too large!'; // Error message.
    } else {
        // 9. SQL INSERT: Binubuo ang query para magpasok ng bagong record sa 'products' table.
        $insert_query = "INSERT INTO products (name, category_id, price, stock, image) 
                         VALUES ('$name', $category_id, '$price', '$stock', '$image')";
        
        // 10. EXECUTION & UPLOAD: Pinapatakbo ang query. Kung success, ililipat ang file sa 'uploads' folder.
        if(mysqli_query($conn, $insert_query)) {
            if(!empty($image)) {
                move_uploaded_file($image_tmp_name, $image_folder); // Ito ang actual na pag-save ng image file.
            }
            $message[] = 'Product added successfully!'; // Success message.
        } else {
            $message[] = 'Error: ' . mysqli_error($conn); // Ipakita ang error kung nag-fail ang database.
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Add Product</title> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* CSS VARIABLES: Pinapadali ang pagbabago ng kulay sa buong system. */
        :root { --primary-yellow: #facc15; --dark-sidebar: #1e293b; --bg-light: #f8fafc; }

        /* GLOBAL STYLES: Setting ng default font at background color ng buong body. */
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); margin: 0; display: flex; }

        /* SIDEBAR STYLING: Pag-aayos ng itsura ng navigation sa kaliwa. */
        .sidebar { width: 260px; background: var(--dark-sidebar); height: 100vh; position: fixed; color: white; padding: 20px 0; }
        .sidebar-header { text-align: center; padding-bottom: 30px; }
        .logo-box { width: 80px; height: 80px; margin: 0 auto; border: 3px solid var(--primary-yellow); border-radius: 50%; overflow: hidden; }

        /* MENU STYLING: Pag-aayos ng listahan ng mga menu links sa sidebar. */
        .sidebar-menu { list-style: none; padding: 0; margin-top: 20px; }
        .sidebar-menu li a { color: #94a3b8; text-decoration: none; padding: 15px 25px; display: block; font-weight: 500; transition: 0.3s; }
        .sidebar-menu li.active a { background: #334155; color: white; border-left: 4px solid var(--primary-yellow); }

        /* CONTENT AREA: Ang main space para sa form, may margin para hindi matakpan ng sidebar. */
        .main-content { margin-left: 260px; width: calc(100% - 260px); }
        .dashboard-container { padding: 40px; max-width: 800px; }

        /* CARD DESIGN: Ang puting container ng inyong form. */
        .form-card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 20px; } /* Space sa pagitan ng bawat input. */
        .form-group label { display: block; font-weight: 600; color: #475569; margin-bottom: 8px; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; box-sizing: border-box; }

        /* BUTTON DESIGN: Stylized yellow button na may hover effect. */
        .submit-btn { background: var(--primary-yellow); color: #0f172a; padding: 14px 30px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; width: 100%; transition: 0.3s; }
        .submit-btn:hover { background: #eab308; }

        /* BACK LINK: Estilo ng "Back to Inventory" link. */
        .back-link { display: inline-block; margin-top: 20px; color: #64748b; text-decoration: none; font-size: 14px; }
        
        /* MESSAGE BOX: Style para sa success/error notifications. */
        .message { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo-box"><img src="../character.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;"></div>
            <h3 style="font-size: 14px; margin-top: 15px; letter-spacing: 1px;">SPARKVERSE ADMIN</h3>
        </div>
         <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-gauge"></i> &nbsp; Dashboard</a></li>
            <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> &nbsp; Orders</a></li>
            <li class="active"><a href="products.php"><i class="fa-solid fa-box"></i> &nbsp; Products</a></li>
            <li><a href="categories.php"><i class="fa-solid fa-folder"></i> &nbsp; Categories</a></li>
            <li><a href="customers.php"><i class="fa-solid fa-users"></i> &nbsp; Customers</a></li>
             <li><a href="analytics.php"><i class="fa-solid fa-chart-line"></i> &nbsp; Analytics</a></li>
            <li><a href="logout.php" style="color: #ef4444;"><i class="fa-solid fa-right-from-bracket"></i> &nbsp; Logout</a></li>
        </ul>
    </aside>

    <div class="main-content">
        <main class="dashboard-container">
            <div class="header-section">
                <h1>Add New Product</h1>
                <p style="color: #64748b;">Fill in the details to add a new item to Sparkverse.</p>
            </div>

            <?php
            if(isset($message)){
                foreach($message as $msg){ echo '<div class="message">'.$msg.'</div>'; }
            }
            ?>

            <div class="form-card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" placeholder="e.g. BTS - Proof Album" required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Albums">Albums</option>
                            <option value="Photocards">Photocards</option>
                            <option value="Lightsticks">Lightsticks</option>
                            <option value="Merchandise">Merchandise</option>
                            <option value="Limited Edition">Limited Edition</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price (₱)</label>
                        <input type="number" name="price" step="0.01" placeholder="0.00" required>
                    </div>

                    <div class="form-group">
                        <label>Stock Quantity</label>
                        <input type="number" name="stock" placeholder="e.g. 50" required>
                    </div>

                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="image" accept="image/png, image/jpg, image/jpeg" required>
                    </div>

                    <button type="submit" name="add_product" class="submit-btn">SAVE PRODUCT</button>
                </form>
            </div>
            <a href="products.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Inventory</a>
        </main>
    </div>
</body>
</html>