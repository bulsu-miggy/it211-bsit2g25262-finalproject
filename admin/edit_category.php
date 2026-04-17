<?php
session_start();
// I-check kung admin ay logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Siguraduhin na tama ang path ng db_connect.php mo
include('../config/db_connect.php'); 

// 1. KUNIN ANG DATA NG CATEGORY
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM categories WHERE id = '$id'");
    
    if (mysqli_num_rows($query) > 0) {
        $category = mysqli_fetch_assoc($query);
    } else {
        header("Location: categories.php");
        exit();
    }
}

// 2. UPDATE LOGIC (Kapag clinick ang Save Changes)
if (isset($_POST['update_category'])) {
    $id = mysqli_real_escape_string($conn, $_POST['cat_id']);
    $name = mysqli_real_escape_string($conn, $_POST['cat_name']);
    $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $icon_name = $category['icon']; // Default value ay yung dati

    // File Upload Logic
    if (!empty($_FILES['cat_icon']['name'])) {
        $target_dir = "../uploads/";
        $file_extension = pathinfo($_FILES['cat_icon']['name'], PATHINFO_EXTENSION);
        $icon_name = "cat_" . time() . "." . $file_extension;
        $target_file = $target_dir . $icon_name;

        if (move_uploaded_file($_FILES['cat_icon']['tmp_name'], $target_file)) {
            // Success upload
        }
    }

    $sql = "UPDATE categories SET 
            name = '$name', 
            meta_description = '$meta_desc', 
            icon = '$icon_name', 
            is_active = '$is_active', 
            is_featured = '$is_featured' 
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: categories.php?msg=updated");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category | Sparkverse</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 40px; }
        .container { max-width: 850px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        
        label { display: block; font-weight: 600; margin-bottom: 10px; font-size: 14px; color: #1e293b; }

        /* Upload Box Design */
        .upload-wrapper { position: relative; width: 100%; margin-bottom: 25px; }
        .upload-area {
            border: 2px dashed #cbd5e1; border-radius: 8px; padding: 40px; text-align: center;
            background: #fff; transition: 0.3s; min-height: 180px; display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .upload-area i { font-size: 40px; color: #94a3b8; margin-bottom: 10px; }
        .upload-area p { margin: 5px 0; font-size: 14px; color: #64748b; }
        
        /* Ito yung sikreto para gumana ang upload box na walang JS */
        .file-input {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0; cursor: pointer; z-index: 10;
        }
        .upload-area:hover { border-color: #3b82f6; background: #f0f9ff; }

        /* Form Controls */
        .form-group { margin-bottom: 25px; }
        .input-text, textarea {
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; font-family: inherit; box-sizing: border-box;
        }
        textarea { resize: vertical; }
        .char-count { font-size: 12px; color: #94a3b8; margin-top: 5px; display: block; }

        /* Category Settings (Checkbox) */
        .settings-title { font-weight: 700; font-size: 16px; margin-bottom: 15px; display: block; }
        .checkbox-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 20px; }
        .checkbox-row input[type="checkbox"] { width: 18px; height: 18px; margin-top: 3px; cursor: pointer; }
        .checkbox-text label { margin-bottom: 0; cursor: pointer; font-size: 15px; }
        .checkbox-text p { margin: 2px 0 0 0; font-size: 13px; color: #64748b; }

        /* Action Buttons */
        .form-footer {
            display: flex; justify-content: flex-end; gap: 12px; margin-top: 40px;
            padding-top: 20px; border-top: 1px solid #e2e8f0;
        }
        .btn { padding: 10px 24px; border-radius: 6px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; text-decoration: none; transition: 0.2s; }
        .btn-cancel { background: white; color: #64748b; border: 1px solid #e2e8f0; }
        .btn-cancel:hover { background: #f1f5f9; }
        .btn-save { background: #1e293b; color: white; display: flex; align-items: center; gap: 8px; }
        .btn-save:hover { background: #0f172a; }

        /* Existing Image Preview */
        .current-icon { max-height: 100px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="container">
    <form action="edit_category.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="cat_id" value="<?php echo $category['id']; ?>">

        <label>Category Icon</label>
        <div class="upload-wrapper">
            <input type="file" name="cat_icon" class="file-input">
            <div class="upload-area">
                <?php if(!empty($category['icon']) && file_exists("../uploads/".$category['icon'])): ?>
                    <img src="../uploads/<?php echo $category['icon']; ?>" class="current-icon">
                    <p style="color: #3b82f6; font-weight: 600;">Current: <?php echo htmlspecialchars($category['icon']); ?></p>
                    <p>Click or drag to replace icon</p>
                <?php else: ?>
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Click to upload or drag and drop</p>
                    <p style="font-size: 12px;">PNG, JPG, SVG up to 5MB</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Category Name *</label>
            <input type="text" name="cat_name" class="input-text" value="<?php echo htmlspecialchars($category['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>Meta Description</label>
            <textarea name="meta_desc" rows="4"><?php echo htmlspecialchars($category['meta_description']); ?></textarea>
            <span class="char-count">85/160 characters</span>
        </div>

        <span class="settings-title">Category Settings</span>
        
        <div class="checkbox-row">
            <input type="checkbox" name="is_active" id="active" <?php echo ($category['is_active'] == 1) ? 'checked' : ''; ?>>
            <div class="checkbox-text">
                <label for="active">Active Category</label>
                <p>Category will be visible on the storefront</p>
            </div>
        </div>

        <div class="checkbox-row">
            <input type="checkbox" name="is_featured" id="featured" <?php echo ($category['is_featured'] == 1) ? 'checked' : ''; ?>>
            <div class="checkbox-text">
                <label for="featured">Featured Category</label>
                <p>Display this category prominently on the homepage</p>
            </div>
        </div>

        <div class="form-footer">
            <a href="categories.php" class="btn btn-cancel">Cancel</a>
            <button type="submit" name="update_category" class="btn btn-save">
                <i class="fa-solid fa-floppy-disk"></i> Save Changes
            </button>
        </div>
    </form>
</div>

</body>
</html>