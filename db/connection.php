<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname= "lynx_db";

try {
    $conn = new PDO("mysql:host=".$servername.";port=3306", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE DATABASE ". $dbname;
    // use exec() because no results are returned
    try {
        $result = $conn->exec($sql);
    } catch (PDOException $th) {
    }

    $sql = "use ". $dbname;
    $conn->exec($sql);

    //sql to create login
    $query = "CREATE TABLE IF NOT EXISTS login (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL, 
        password TEXT NOT NULL,
        login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor login date',
        logout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor logout date',
        role VARCHAR(20) DEFAULT 'client',
        UNIQUE (username,email)
        )";
    
    try {
        $conn->exec($query);
        
        //Insertion of default data
        $data = [
            ['miggy', 'miggy.ymasa@bulsu.edu.ph', md5('12345678'), 'admin'],
            ['miggy1', 'miggy1.ymasa@bulsu.edu.ph', md5('5678'), 'client'],
            ['jan', 'custodio', md5('12345678'), 'client']
        ];

        $query_i = $conn->prepare("INSERT INTO login (
            username,
            email,
            password,
            role
        ) VALUES (?,?,?,?)");

        try {
            $conn->beginTransaction();
            foreach ($data as $row)
            {
                $query_i->execute($row);
            }
            $conn->commit();

        }catch (Exception $e){
            $conn->rollback();
            throw $e;
        }

    } catch (PDOException $th) {
    }


} catch(PDOException $e) {
}
