<?php
session_start();

if (!isset($_SESSION["username"]) && !isset($_SESSION["password"])) {
    session_destroy();
    header('Location: login.php');
    exit();
}
require "config.php";
$text = "Add New Book";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $text; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <!-- HEADER -->
    <header>
    </header>

    <!-- MAIN -->
    <main>
        <div class="container">
            <div class="row registration justify-content-center align-items-center">
                <div class="col-lg-4 mx-auto">
                    <h2 class="text-center mb-3"><?php echo $text; ?></h2>
                    <form action="<?php echo "$url/db/action/toaddbook.php"; ?>" method="POST" enctype="multipart/form-data" novalidate>
                        <div class="row">
                            <div class="col border border-2 rounded rounded-3 p-5">
                                <div class="mb-5">
                                    <img id="uploadPreview" src="<?php echo "$url/images/avatar.png"; ?>" class="mb-3 avatar img-fluid"/><br>
                                    <input type="file" id="imglink" name="imglink" accept=".jpg,.jpeg,.png" onchange="PreviewImage();"/>
                                </div>
                                <div class="mb-3">
                                    <label for="book-title" class="form-label d-none">Title</label>
                                    <input type="text" class="form-control" name="title" id="book-title" required placeholder="Enter Book Title">
                                </div>
                                <div class="mb-3">
                                    <label for="book-author" class="form-label d-none">Author</label>
                                    <input type="text" class="form-control" name="author" id="book-author" required placeholder="Enter Book Author">
                                </div>
                                <div class="mb-3">
                                    <label for="book-category" class="form-label d-none">Category</label>
                                    <input type="text" class="form-control" name="category" id="book-category" required placeholder="Enter Book Category">
                                </div>
                                <div class="mb-3">
                                    <label for="book-price" class="form-label d-none">Price</label>
                                    <input type="number" step="0.01" class="form-control" name="price" id="book-price" required placeholder="Enter Book Price">
                                </div>
                                <div class="mb-3">
                                    <label for="book-excerpt" class="form-label d-none">Excerpt</label>
                                    <textarea class="form-control" name="excerpt" id="book-excerpt" rows="3" required placeholder="Enter Book Excerpt"></textarea>
                                </div>
                                <div class="d-grid mt-5">
                                    <button type="submit" name="submit" class="btn btn-success">Add Book</button>
                                </div>
                                <div class="mt-4 text-center">
                                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script type="text/javascript">
        function PreviewImage() {
            var oFReader = new FileReader();
            oFReader.readAsDataURL(document.getElementById("imglink").files[0]);

            oFReader.onload = function (oFREvent) {
                document.getElementById("uploadPreview").src = oFREvent.target.result;
            };
        };
    </script>

    <!-- FOOTER -->
    <footer>
    </footer>

</body>
</html>