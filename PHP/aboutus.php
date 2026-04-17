<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Lasa Filipina</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom {
            width: 100%;
            max-width: 100%;
            margin: 20px auto 0;
            padding: 12px 20px;
            background: rgba(255, 248, 240, 0.98);
            border-radius: 25px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
        }
        .navbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .navbar-brand-custom {
            font-family: 'Times New Roman', serif;
            font-size: 28px;
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
            gap: 30px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .nav-links-custom a {
            text-decoration: none;
            color: #2f241b;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s ease;
            padding: 8px 16px;
            border-radius: 10px;
        }
        .nav-links-custom a:hover {
            color: #bc6f3b;
            background-color: rgba(188, 111, 59, 0.1);
        }
        .nav-links-custom a.active {
            background-color: #bc6f3b;
            color: white;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .cart-icon-btn {
            background: none;
            border: none;
            font-size: 28px;
            color: #bc6f3b;
            cursor: pointer;
            padding: 8px;
            text-decoration: none;
            position: relative;
            display: inline-flex;
            align-items: center;
            transition: transform 0.2s;
        }
        .cart-icon-btn:hover {
            transform: scale(1.1);
            color: #a55828;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
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
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 2px;
            color: #8b735b;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            overflow-x: hidden;
            background-image: url('../Imges/bg.jpg');
            background-size: cover;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(107deg, rgba(245, 229, 214, 0.85) 80%, rgba(255, 245, 235, 0.9) 100%);
            z-index: -1;
        }
        .page-title {
            font-family: 'Times New Roman', serif;
            font-size: 52px;
            font-weight: 700;
            color: #2f241b;
            text-align: center;
            margin: 50px 0 20px 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        .page-subtitle {
            font-family: 'Verdana', sans-serif;
            font-size: 18px;
            color: #666;
            text-align: center;
            margin-bottom: 50px;
        }
        .content-container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .about-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .about-section h2 {
            font-family: 'Times New Roman', serif;
            font-size: 36px;
            font-weight: 700;
            color: #bc6f3b;
            margin-bottom: 20px;
            border-bottom: 3px solid #bc6f3b;
            padding-bottom: 15px;
        }
        .about-section p {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
            margin-bottom: 15px;
        }
        .about-section ul {
            margin-left: 20px;
            font-size: 16px;
            line-height: 1.8;
            color: #333;
        }
        .about-section li {
            margin-bottom: 12px;
        }
        .mission-values {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        .value-card {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #bc6f3b;
        }
        .value-card h3 {
            color: #bc6f3b;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .value-card p {
            font-size: 15px;
            line-height: 1.6;
        }
        .footer-section {
            background: rgba(141, 85, 36, 0.9);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-top: 50px;
            text-align: center;
        }
        .footer-section h2 {
            color: white;
            border-bottom: 3px solid white;
        }
        .footer-section p {
            color: #f0f0f0;
        }
        @media (max-width: 768px) {
            .mission-values {
                grid-template-columns: 1fr;
            }
            .page-title {
                font-size: 36px;
            }
            .about-section {
                padding: 25px;
            }
            .about-section h2 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1 class="page-title">About Lasa Filipina</h1>
    <p class="page-subtitle">Bringing Authentic Filipino Flavors to Your Table Since 1920</p>

    <div class="content-container">
        <div class="about-section">
            <h2>Our Story</h2>
            <p>
                Lasa Filipina is a culinary institution dedicated to preserving and celebrating the rich, diverse flavors of Filipino cuisine. Founded in 1920, we have been serving authentic Filipino dishes to families and friends for over a century.
            </p>
            <p>
                What started as a humble carinderia has grown into a beloved destination for those seeking genuine Filipino food. Our commitment to quality ingredients, traditional recipes, and warm hospitality has made us a cornerstone of Filipino culinary excellence.
            </p>
            <p>
                Today, we blend generations of family recipes with modern convenience, bringing the taste of home to your table through our restaurant and online ordering platform.
            </p>
        </div>

        <div class="about-section">
            <h2>Our Mission & Values</h2>
            <div class="mission-values">
                <div class="value-card">
                    <h3>Our Mission</h3>
                    <p>To share the authentic taste of the Philippines with the world, preserving culinary traditions while embracing innovation.</p>
                </div>
                <div class="value-card">
                    <h3>Our Values</h3>
                    <p>Quality, Authenticity, Hospitality, and Community are at the heart of everything we do.</p>
                </div>
            </div>
        </div>

        <div class="about-section">
            <h2>What Makes Us Special</h2>
            <ul>
                <li><strong>Authentic Recipes:</strong> All our dishes are prepared using traditional Filipino recipes passed down through generations.</li>
                <li><strong>Quality Ingredients:</strong> We source the finest ingredients to ensure every dish meets our high standards.</li>
                <li><strong>Expert Preparation:</strong> Our skilled chefs bring decades of experience and passion to every meal.</li>
                <li><strong>Warm Hospitality:</strong> We treat every customer like family, ensuring a memorable dining experience.</li>
                <li><strong>Convenient Ordering:</strong> Enjoy authentic Filipino food from the comfort of your home with our easy-to-use platform.</li>
            </ul>
        </div>

        <div class="footer-section">
            <h2>Join Our Filipino Food Family</h2>
            <p>Whether you're craving classic adobo, refreshing beverages, or sweet desserts, Lasa Filipina has something for everyone.</p>
            <p style="margin-top: 20px; font-style: italic;">
                "Lasang Bahay, Lasang Puso - Home Taste, Made with Heart"
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
