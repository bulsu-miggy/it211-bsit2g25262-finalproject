<?php

require '../config.php';    

$servername = "localhost";
$username = "root";
$password = "";
$dbname= "w71";

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

    $sql = "use ". $dbname;
    $conn->exec($sql);
    //sql to create login
    $query = "CREATE TABLE IF NOT EXISTS login (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(200) NOT NULL,
        last_name VARCHAR(200) NOT NULL,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL, 
        img_url TEXT,
        password TEXT NOT NULL,
        login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor login date',
        logout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor logout date'
        )";
    
    try {
        $conn->exec($query);
        
        echo "DB Created Successfully.";

        $data = [
            ['miggy', 'miggy.ymasa@bulsu.edu.ph', md5('12345678')],
        ];

        $query_i = $conn->prepare("INSERT INTO login (
            username,
            email,
            password
        ) VALUES (?,?,?)");

        try {
            $conn->beginTransaction();
            foreach ($data as $row)
            {
                $query_i->execute($row);
            }
            $conn->commit();
            echo "<br/> New record created successfully";
        }catch (Exception $e){
            $conn->rollback();
            throw $e;
        }
        
        $conn = null;

        echo "<br/> Redirecting... ";
        
        header("refresh: 1; url = $url/login.php");

        exit();
    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
        $conn = null;
        exit();
    }

    //SQL to create book data. table name "books"
    /** 
     * ACT6 - Starts here
     * id
     * title
     * excerpt
     * image = "url of image saved in the webfiles",
    */

    

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}