<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname= "project_WST";

try {
    $conn = new PDO("mysql:host=$servername;port=3306", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE DATABASE IF NOT EXISTS ". $dbname;
    try {
        $conn->exec($sql);
    } catch (PDOException $th) {
        echo "<br> Database Already Exists";
    }

    $sql = "use ". $dbname;
    $conn->exec($sql);

    $sql = "use ". $dbname;
    $conn->exec($sql);

    $counter = 0; // ← Add this line
    
    // =============================================
    // LOGIN TABLE
    // =============================================
    $query = "CREATE TABLE IF NOT EXISTS login (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(200) NOT NULL,
        last_name VARCHAR(200) NOT NULL,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL, 
        img_url VARCHAR(255),
        password TEXT NOT NULL,
        login_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor login date',
        logout_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Monitor logout date'
        )";
    
    try {
        $conn->exec($query);
        
        $data = [
            ['Aquilla James', 'Dela Cruz', 'Akila', '2024104677@bulsu.edu.ph', "", md5('12345678')],
            ['John Edrick', 'Maigue', 'Edrik', '2024104678@bulsu.edu.ph', "", md5('12345678')],
            ['Ian Gabriel', 'San Pedro', 'Ian', '2024104679@bulsu.edu.ph', "", md5('12345678')],
            ['Bennedict', 'Garma', 'Ben', '2024104680@bulsu.edu.ph', "", md5('12345678')],
            ['Ernesto Jay-r', 'Agustin', 'Ernesto', '2024104681@bulsu.edu.ph', "", md5('12345678')],
        ];

        if ($counter == 0) {
        $query_i = $conn->prepare("INSERT INTO login (
            first_name,
            last_name, 
            username,
            email,
            img_url,
            password
        ) VALUES (?,?,?,?,?,?)");

         try {
            $conn->beginTransaction();
            foreach ($data as $row)
            {
                $check = $conn->prepare("SELECT COUNT(*) FROM login WHERE username=?");
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
        }

    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

    // =============================================
    // BOOKS TABLE (updated to include all columns used in XML export)
    // =============================================
    $query_create_books = "CREATE TABLE IF NOT EXISTS books (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        excerpt TEXT,
        image VARCHAR(255),
        category VARCHAR(100),
        author VARCHAR(200),
        publish_date DATE,
        price DECIMAL(10,2),
        media VARCHAR(255),
        media_type VARCHAR(50)
        )";
    
    try {
        $conn->exec($query_create_books);

        $data_books = [
            ['Julius Ceasar', ' Roman general and statesman known for conquering Gaul, winning a major civil war, and acting as dictator, which effectively transformed the Roman Republic into the Roman Empire.', 'images/JuliusCeasar.jpg', 'History', 'Unknown', '2020-01-01', 9.99, '', ''],
            ['Napoleon Bonaparte', 'French military and political leader who rose to prominence during the French Revolution, crowning himself Emperor of the French in 1804.', 'images/Napoleon.jpg', 'History', 'Unknown', '2020-01-01', 9.99, '', ''],
            ['Socrates', 'Philosopher known for his contributions to Western philosophy.', 'images/Socrates.jpg', 'Philosophy', 'Unknown', '2020-01-01', 9.99, '', '']
        ];

        if ($counter == 0) {
        $query_insert_books = $conn->prepare("INSERT INTO books (
            title,
            excerpt,
            image,
            category,
            author,
            publish_date,
            price,
            media,
            media_type
        ) VALUES (?,?,?,?,?,?,?,?,?)");

        try {
            $conn->beginTransaction();

            foreach ($data_books as $row)
            {
                $check = $conn->prepare("SELECT COUNT(*) FROM books WHERE title=?");
                $check->execute([$row['0']]);

                if($check->fetchColumn() == 0){
                    $query_insert_books->execute($row);
                }
            }
            $counter++;
            $conn->commit();
        }catch (Exception $e){
            $conn->rollback();
            throw $e;
        }
        }

    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

    // =============================================
    // ACCOUNTS TABLE
    // =============================================
    $query_create_accounts = "CREATE TABLE IF NOT EXISTS accounts (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        login_id INT(11) UNSIGNED NOT NULL,
        account_type ENUM('customer', 'admin', 'seller') DEFAULT 'customer',
        phone VARCHAR(20),
        address TEXT,
        city VARCHAR(100),
        province VARCHAR(100),
        zip_code VARCHAR(10),
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (login_id) REFERENCES login(id) ON DELETE CASCADE
    )";

    try {
        $conn->exec($query_create_accounts);
    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

    // =============================================
    // CARTS TABLE
    // =============================================
    $query_create_carts = "CREATE TABLE IF NOT EXISTS carts (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        account_id INT(11) UNSIGNED NOT NULL,
        book_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )";

    try {
        $conn->exec($query_create_carts);
    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

    // =============================================
    // TRANSACTIONS TABLE
    // =============================================
    $query_create_transactions = "CREATE TABLE IF NOT EXISTS transactions (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        account_id INT(11) UNSIGNED NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'paid', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
        payment_method ENUM('cash', 'gcash', 'credit_card', 'bank_transfer') DEFAULT 'cash',
        shipping_address TEXT,
        notes TEXT,
        ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
    )";

    try {
        $conn->exec($query_create_transactions);
    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

    // =============================================
    // TRANSACTION ITEMS TABLE
    // =============================================
    $query_create_transaction_items = "CREATE TABLE IF NOT EXISTS transaction_items (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT(11) UNSIGNED NOT NULL,
        book_id INT(11) UNSIGNED NOT NULL,
        quantity INT(11) NOT NULL DEFAULT 1,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE RESTRICT
    )";

    try {
        $conn->exec($query_create_transaction_items);
    } catch (PDOException $th) {
        echo "Error in creating Table";
        echo $th;
    }

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}