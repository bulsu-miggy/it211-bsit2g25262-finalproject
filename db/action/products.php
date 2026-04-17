<?php
include("dbconfig.php");

$query = "SELECT * FROM PRODUCTS";
$result = mysqli_query($conn, $query);
?>

<h2>Products</h2>

<?php while($row = mysqli_fetch_assoc($result)) { ?>
    <div style="border:1px solid black; padding:10px; margin:10px;">
        
        <h3><?php echo $row['produc_name']; ?></h3>
        
        <p>Price: ₱<?php echo $row['product_price']; ?></p>
        <p>Stocks: <?php echo $row['stocks']; ?></p>
        <p>Status: <?php echo $row['product_status']; ?></p>

        <?php if($row['stocks'] > 0) { ?>
            <form action="checkout.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                <input type="submit" value="Buy Now">
            </form>
        <?php } else { ?>
            <p style="color:red;">Out of Stock</p>
        <?php } ?>

    </div>
<?php } ?>