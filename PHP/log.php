<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <style>
        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 1.5rem auto 0;
            padding: 1rem 1.5rem;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand-custom {
            font-family: 'Times New Roman', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #2f241b;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .navbar-brand-custom:hover {
            transform: scale(1.02);
            color: #bc6f3b;
        }

        .nav-links-custom {
            display: flex;
            gap: 1.25rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-links-custom a {
            text-decoration: none;
            color: #2f241b;
            font-size: 1rem;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 0.5rem 1rem;
            border-radius: 12px;
        }

        .nav-links-custom a:hover {
            color: #bc6f3b;
            background-color: rgba(188, 111, 59, 0.1);
        }

        .nav-links-custom a.active {
            background-color: #bc6f3b;
            color: white;
        }

        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #bc6f3b;
            cursor: pointer;
            padding: 0.5rem;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }

        .cart-icon-btn:hover {
            transform: scale(1.05);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.15rem 0.45rem;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 1.2rem;
            text-align: center;
        }

        .avatar-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            background: #f0e2d6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-icon:hover {
            transform: scale(1.05);
        }

        .avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .since-badge {
            font-size: 0.8rem;
            letter-spacing: 0.1rem;
            color: #8b735b;
            font-weight: 600;
        }

        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
    </style>

</head>
<body>
    <header>
        <header class="site-header">
    
     <div class="container">
        <nav class="navbar-custom">
            <div class="navbar-inner">
                <a href="home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
                <ul class="nav-links-custom">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="dishes.php" class="active">Menu</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
                <div class="navbar-actions">
                    <span class="since-badge">SINCE 1920</span>
                    <a href="cart.php" class="cart-icon-btn">
                        <i class="bi bi-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                    <div class="avatar-icon">
                        <img src="../images/logi.png" alt="User Avatar" class="avatar-img">
                    </div>
                </div>
            </div>
        </nav>
    </div> 
    <div class="LogCont">
        <label for="Login Email"></label>
       

    </div>
</header>
    </header>
</body>
</html>