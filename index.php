<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Lasa Filipina | Authentic Filipino Cuisine Since 1920</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- No external stylesheets to preserve exact original layout; all CSS is inline as provided -->
  <style>
    /* ----- ORIGINAL CSS FROM FINALPROJECT/DOCX (preserved exactly) ----- */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    /* Body with background image */
    body {
      font-family: system-ui, 'Segoe UI', 'Roboto', 'Helvetica Neue', sans-serif;
      background-image: url('images/bg.jpg');
      background-size: cover;
      color: #2c2418;
      line-height: 1.5;
      position: relative;
    }

    /* Body overlay for better text readability */
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

    /* Color Variables */
    :root {
      --deep-umber: #2f241b;
      --terracotta: #bc6f3b;
      --burnt-orange: #b45f2b;
      --cream: #fff6ed;
      --sand: #f2e4d8;
      --golden-accent: #c48b3b;
      --shadow-sm: 0 8px 20px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 12px 28px rgba(0, 0, 0, 0.08);
    }

    /* Container */
    .container {
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
      padding: 0 2rem;
    }

    /* Header & Navigation */
    .site-header {
      background: rgba(255, 248, 240, 0.96);
      backdrop-filter: blur(2px);
      box-shadow: var(--shadow-sm);
      position: sticky;
      top: 0;
      z-index: 100;
      border-bottom: 1px solid rgba(188, 111, 59, 0.2);
    }

    .navbar {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      padding: 1.2rem 0;
      gap: 1.5rem;
    }

    .logo {
      font-size: 1.9rem;
      font-weight: 700;
      letter-spacing: -0.5px;
      background: linear-gradient(135deg, #a55828, #c97e4a);
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
      transition: transform 0.2s ease;
    }

    .logo:hover {
      transform: scale(1.02);
    }

    .navbar-custom {
      width: 100%;
      max-width: 100%;
      margin: 1.5rem auto 0;
      padding: 1rem 1.5rem;
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
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--deep-umber);
      text-decoration: none;
      transition: transform 0.3s ease;
    }

    .navbar-brand-custom:hover {
      transform: scale(1.02);
      color: var(--terracotta);
    }

    .nav-links-custom {
      display: flex;
      gap: 1.5rem;
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
      color: var(--terracotta);
      background-color: rgba(188, 111, 59, 0.1);
    }

    .nav-links-custom a.active {
      background-color: var(--terracotta);
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
      font-size: 1.25rem;
      color: var(--terracotta);
      cursor: pointer;
      padding: 0.6rem;
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
      font-size: 0.75rem;
      letter-spacing: 0.12rem;
      text-transform: uppercase;
      color: #8b735b;
      font-weight: 600;
    }

    /* Hero Section - No background image (original) */
    .hero {
      min-height: 85vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
    }

    .hero-content {
      max-width: 880px;
      padding: 2rem;
      animation: fadeUp 1s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }

    .hero h1 {
      font-size: 5rem;
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 1rem;
      color: #2a1f16;
      letter-spacing: -0.02em;
    }

    .hero-tagline {
      font-size: 1.3rem;
      color: #4f3724;
      max-width: 600px;
      margin: 1rem auto 2rem;
    }

    .btn-get-started {
      display: inline-flex;
      align-items: center;
      gap: 0.6rem;
      background: var(--deep-umber);
      color: white;
      border: none;
      padding: 1rem 2.4rem;
      font-size: 1.2rem;
      font-weight: 600;
      border-radius: 60px;
      cursor: pointer;
      transition: all 0.25s;
      background: linear-gradient(145deg, #3f2c1f, #2f241b);
    }

    .btn-get-started:hover {
      background: var(--terracotta);
      transform: translateY(-3px);
      box-shadow: 0 12px 22px rgba(188, 111, 59, 0.3);
      gap: 0.8rem;
    }

    /* About Section - Semi-transparent to show body background */
    .section-divider {
      background: rgba(242, 228, 216, 0.85);
      backdrop-filter: blur(4px);
      padding: 4rem 0 3rem;
    }

    .about-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 3rem;
      align-items: center;
      justify-content: space-between;
    }

    .about-text {
      flex: 1.2;
    }

    .about-text h2 {
      font-size: 2.2rem;
      font-weight: 700;
      color: #2f241b;
      border-left: 6px solid var(--terracotta);
      padding-left: 1.2rem;
      margin-bottom: 1.5rem;
    }

    .about-text p {
      font-size: 1.1rem;
      line-height: 1.6;
      color: #3e2c1e;
      margin-bottom: 1.2rem;
    }

    .about-highlight {
      background: rgba(255, 242, 230, 0.9);
      padding: 1.2rem 1.8rem;
      border-radius: 32px;
      font-style: italic;
      font-weight: 500;
      color: #9b5a2e;
      display: inline-block;
      margin-top: 0.6rem;
    }

    .about-icon {
      flex: 0.8;
      text-align: center;
      font-size: 6rem;
      background: rgba(255, 241, 228, 0.9);
      padding: 2rem;
      border-radius: 48px;
      box-shadow: var(--shadow-sm);
    }

    /* Contact Section */
    .contact-section {
      background: rgba(47, 36, 27, 0.95);
      color: #fef0e3;
      padding: 3rem 0;
    }

    .contact-flex {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 3rem;
      align-items: baseline;
    }

    .contact-item h4 {
      font-size: 1.3rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #e7bc8b;
    }

    .contact-item p, .contact-item a {
      color: #f3dfce;
      text-decoration: none;
    }

    .contact-item a:hover {
      color: #e0a56e;
      text-decoration: underline;
    }

    /* Footer */
    footer {
      background: rgba(30, 23, 18, 0.95);
      color: #b99e86;
      text-align: center;
      padding: 1.8rem;
      font-size: 0.85rem;
    }

    /* Animations */
    @keyframes fadeUp {
      from {
        opacity: 0;
        transform: translateY(28px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Responsive Design - original */
    @media (max-width: 780px) {
      .container {
        padding: 0 1.5rem;
      }
      .navbar-inner {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
      }
      .nav-links-custom {
        gap: 1.2rem;
        flex-wrap: wrap;
        justify-content: center;
        margin: 1rem auto 0;
      }
      .navbar-actions {
        justify-content: center;
      }
      .hero {
        min-height: auto;
        padding: 2rem 0;
      }
      .hero h1 {
        font-size: 3.2rem;
      }
      .hero-tagline {
        font-size: 1rem;
      }
      .about-preview {
        flex-direction: column;
        text-align: center;
      }
      .about-text h2 {
        border-left: none;
        border-bottom: 3px solid var(--terracotta);
        display: inline-block;
        padding-bottom: 0.4rem;
        padding-left: 0;
      }
      .btn-get-started {
        padding: 0.8rem 1.8rem;
        font-size: 1rem;
      }
    }

    @media (max-width: 480px) {
      .hero-content {
        padding: 1.5rem;
        border-radius: 32px;
      }
      .hero h1 {
        font-size: 2.5rem;
      }
      .contact-flex {
        gap: 1.8rem;
        flex-direction: column;
        align-items: center;
        text-align: center;
      }
    }

    /* Additional minor adjustment to ensure logo image inside hero stays as original index.php style */
    .hero .logo img {
      max-width: 100%;
      height: auto;
      display: block;
      margin: 0 auto;
    }
    .hero {
      flex-direction: column;
    }
  </style>
</head>
<body>
  <!-- Header: identical to index.php / original design (navbar unchanged) -->
  <!-- <header class="site-header">
    <div class="container">
      <nav class="navbar-custom">
        <div class="navbar-inner">
          <a href="PHP/home.php" class="navbar-brand-custom">🇵🇭 Lasa Filipina</a>
          <ul class="nav-links-custom">
            <li><a href="" class="active">Home</a></li>
            <li><a href="">Menu</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact Us</a></li>
          </ul>
          <div class="navbar-actions">
            <span class="since-badge">SINCE 1920</span>
            <a href="PHP/cart.php" class="cart-icon-btn">
              <i class="bi bi-cart"></i>
              <span class="cart-count">0</span>
            </a>
            <div class="avatar-icon">
              <img src="images/logi.png" alt="User Avatar" class="avatar-img">
            </div>
          </div>
        </div>
      </nav>
    </div>
  </header> -->

  <main>
    <!-- Hero Section: Exactly as in index.php (the "get started" entry point) with logo image and get started button -->
    <section class="hero" style="display: flex; flex-direction: column;">
      <div class="logo" style="margin-bottom: -30px;">
        <!-- image path remains images/logi.png as in original index.php -->
        <img src="images/logi.png" alt="Lasa Filipina Logo">
      </div>
      <div class="hero-content">
        <!-- The "GET STARTED" button exactly as provided, links to home.php (preserving navigation) -->
        <a href="PHP/loginorsignup.php" class="btn-get-started" id="getStartedBtn">GET STARTED →</a>
      </div>
    </section>

    <!-- About Section (section-divider) same as original index.php content -->
    <div class="section-divider">
      <div class="container about-preview">
        <div class="about-text">
          <h2>About Lasa Filipina</h2>
          <p>Established in 1920 by the Mercado family, Lasa Filipina has been the heart of authentic Filipino cuisine for four generations. We honor traditional recipes passed down through time — each dish tells a story of celebration, family, and love.</p>
          <p>From our humble carinderia roots to a beloved culinary landmark, we bring you the genuine taste of the Philippines: adobo, kare-kare, sinigang, and so much more, prepared with soul.</p>
          <div class="about-highlight"> "Serving with a smile since 1920" </div>
        </div>
        <!-- about-icon div removed as per original (commented in index.php) -->
      </div>
    </div>

    <!-- Contact Us section (exactly as original index.php) -->
    <div class="contact-section" id="contact-section">
      <div class="container contact-flex">
        <div class="contact-item">
          <h4>Visit Us</h4>
          <p>123 Escolta St, Binondo, Manila, PH</p>
        </div>
        <div class="contact-item">
          <h4>Call Us</h4>
          <p>+63 (2) 8123 4567</p>
        </div>
        <div class="contact-item">
          <h4>Email</h4>
          <p><a href="mailto:hello@lasafilipina.com">hello@lasafilipina.com</a></p>
        </div>
        <div class="contact-item">
          <h4>Hours</h4>
          <p>Mon–Sun: 10:00 AM – 10:00 PM</p>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <p>© 2025 Lasa Filipina — Authentic Filipino Heritage Since 1920. Savor the tradition.</p>
  </footer>


</body>
</html>