<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Lasa Filipina</title>
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
        .contact-section {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .contact-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .contact-info-card {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid #bc6f3b;
            text-align: center;
        }
        .contact-info-card i {
            font-size: 32px;
            color: #bc6f3b;
            margin-bottom: 15px;
        }
        .contact-info-card h3 {
            color: #2f241b;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .contact-info-card p {
            color: #666;
            font-size: 15px;
            margin: 0;
        }
        .form-section {
            background: #f8f8f8;
            padding: 40px;
            border-radius: 15px;
        }
        .form-section h2 {
            font-family: 'Times New Roman', serif;
            font-size: 36px;
            font-weight: 700;
            color: #bc6f3b;
            margin-bottom: 30px;
            border-bottom: 3px solid #bc6f3b;
            padding-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #2f241b;
            margin-bottom: 8px;
            display: block;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: Arial, sans-serif;
            font-size: 15px;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #bc6f3b;
            box-shadow: 0 0 0 3px rgba(188, 111, 59, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }
        .submit-btn {
            background-color: #bc6f3b;
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .submit-btn:hover {
            background-color: #a55828;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }
        @media (max-width: 768px) {
            .contact-info-grid {
                grid-template-columns: 1fr;
            }
            .page-title {
                font-size: 36px;
            }
            .contact-section {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <h1 class="page-title">Contact Us</h1>
    <p class="page-subtitle">We'd Love to Hear From You!</p>

    <div class="content-container">
        <div class="contact-section">
            <div class="contact-info-grid">
                <div class="contact-info-card">
                    <i class="bi bi-geo-alt-fill"></i>
                    <h3>Address</h3>
                    <p>
                        123 Filipino Street<br>
                        Metro Manila, Philippines
                    </p>
                </div>
                <div class="contact-info-card">
                    <i class="bi bi-telephone-fill"></i>
                    <h3>Phone</h3>
                    <p>
                        +63 (2) 1234 5678<br>
                        +63 (917) 123 4567
                    </p>
                </div>
                <div class="contact-info-card">
                    <i class="bi bi-envelope-fill"></i>
                    <h3>Email</h3>
                    <p>
                        info@lasafilipina.com<br>
                        orders@lasafilipina.com
                    </p>
                </div>
                <div class="contact-info-card">
                    <i class="bi bi-clock-fill"></i>
                    <h3>Hours</h3>
                    <p>
                        Mon - Fri: 10 AM - 10 PM<br>
                        Sat - Sun: 9 AM - 11 PM
                    </p>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>Send us a Message</h2>
            <div class="success-message" id="successMessage">
                ✓ Thank you for your message! We'll get back to you soon.
            </div>
            <form id="contactForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show success message
            const successMessage = document.getElementById('successMessage');
            successMessage.style.display = 'block';
            
            // Reset form
            this.reset();
            
            // Hide message after 5 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 5000);
        });
    </script>
</body>
</html>
