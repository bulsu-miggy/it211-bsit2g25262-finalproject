<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname= "wst";

// // Create connection
// $conn = new mysqli($servername, $username, $password);

// // // Check connection
// // if ($conn->connect_error) {
// //   die("Connection failed: " . $conn->connect_error);
// // }

// // Check connection
// if (mysqli_connect_error()) {
//     die("Database connection failed: " . mysqli_connect_error());
//   }

// echo "Connected successfully";

try {
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Connected successfully";

    $sql = "CREATE DATABASE IF NOT EXISTS ". $dbname;
    // use exec() because no results are returned
    try {
        $conn->exec($sql);
    } catch (PDOException $th) {
        //echo "<br> Database Already Exists";
    }

    $sql1 = "use ". $dbname;
    $conn->exec($sql1);
    //sql to create products
    $query = "CREATE TABLE IF NOT EXISTS products (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL, 
        stock INT NOT NULL DEFAULT 10,
        stock_status ENUM('Low Stock', 'In Stock') NOT NULL,
        img VARCHAR(255) NOT NULL
        
        )";
    
    try {
        $conn->exec($query);

        $data = [
            ["Adidas Men's Barricade 14", 9500.00, 15, 'In Stock', 'image/shoe1.webp'],
            ["Adidas Unisex Harden Vol. 10", 9500.00, 5, 'Low Stock', 'image/shoe2.webp'],
            ["Nike Men's Tatum 4 PF", 6565.50, 20, 'In Stock', 'image/shoe3.webp'],
            ["Nike Men's Giannis Immortality 4", 3865.50, 12, 'In Stock', 'image/shoe4.webp']
        ];

        $query_i = $conn->prepare("INSERT INTO products (
            product,
            price,
            stock,
            stock_status,
            img
        ) VALUES (?,?,?,?,?)");
        try {
            $conn->beginTransaction();

            foreach ($data as $row)
            {

                $check = $conn->prepare("SELECT COUNT(*) FROM products WHERE product=?");
                 $check->execute([$row['0']]);

                if($check->fetchColumn() == 0){
                    $query_i->execute($row);
                }

            }

            $conn->commit();

        }catch (Exception $e){
            $conn->rollback();
            throw $e;
        }
        

    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

$querycustomer = "CREATE TABLE IF NOT EXISTS customers (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

    $query_insert_customers = $conn->prepare("INSERT INTO customers (
    username,
    fullname,
    email,
    password,
    created_at
) VALUES (?,?,?,?,?)");
    
    try {
        $conn->exec($querycustomer);


        $data_customers = [
    ['john_doe', 'John Doe', 'john.doe@email.com', 'password123', '2026-01-15'],
    ['jane_smith', 'Jane Smith', 'jane.smith@email.com', 'password456', '2026-02-03'],
    ['bob_johnson', 'Bob Johnson', 'bob.j@email.com', 'password789', '2025-11-20'],
    ['alice_brown', 'Alice Brown', 'alice.b@email.com', 'password012', '2026-03-10'],
    ['charlie_wilson', 'Charlie Wilson', 'charlie.w@email.com', 'password345', '2025-09-05'],
    ['emma_davis', 'Emma Davis', 'emma.d@email.com', 'password678', '2026-01-28']
];


        try {
            $conn->beginTransaction();

            foreach ($data_customers as $row)
            {

                $check = $conn->prepare("SELECT COUNT(*) FROM customers WHERE username=?");
                $check->execute([$row['0']]);;

                if($check->fetchColumn() == 0){
                    $query_insert_customers->execute($row);
                }

            }

            $conn->commit();
        }catch (Exception $e){
            $conn->rollback();
            throw $e;
        }
        

    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }



    $queryorder = "CREATE TABLE IF NOT EXISTS orders (
    orderid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    customerid INT UNSIGNED NOT NULL,
    productid INT UNSIGNED NOT NULL,

    date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,

    status ENUM('pending', 'completed', 'cancelled') NOT NULL,

    FOREIGN KEY (customerid) REFERENCES customers(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    FOREIGN KEY (productid) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);";

    
    try {
        $conn->exec($queryorder);


        $data_orders = [
    [1, 1, '2026-04-01', 9500.00, 'pending'],
    [2, 2, '2026-04-02', 9500.00, 'completed'],
    [3, 3, '2026-04-03', 6565.50, 'pending'],
    [4, 4, '2026-04-04', 3865.50, 'completed'],
    [5, 1, '2026-04-05', 9500.00, 'pending'],
    [6, 2, '2026-04-06', 9500.00, 'cancelled'],
    [1, 3, '2026-04-07', 6565.50, 'completed'],
    [2, 4, '2026-04-08', 3865.50, 'pending']
];

        $query_insert_orders = $conn->prepare("INSERT INTO orders (
    customerid,
    productid,
    date,
    amount,
    status
) VALUES (?,?,?,?,?)");

        try {
            $conn->beginTransaction();


foreach ($data_orders as $row) {

    $check = $conn->prepare("
        SELECT COUNT(*) FROM orders 

        WHERE customerid=? AND productid=? AND date=?
    ");

    $check->execute([$row[0], $row[1], $row[2]]);

    if ($check->fetchColumn() == 0) {
        $query_insert_orders->execute($row);
    }
}
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }

    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
         