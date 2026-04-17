<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PRODUCTS</title>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #111111;
      --surface:   #1a1a1a;
      --surface2:  #222222;
      --border:    #2e2e2e;
      --red:       #e02020;
      --red-dark:  #b81818;
      --text:      #f0f0f0;
      --muted:     #888888;
      --strike:    #555555;
      --gold:      #f5a623;
    }

    body {
      background: var(--bg);
      color: var(--text);
      font-family: 'Barlow', sans-serif;
      min-height: 100vh;
    }

   
    .top-header {
      background: #0d0d0d;
      border-bottom: 3px solid var(--red);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1.3rem 2.5rem;
      gap: 1rem;
    }

    .logo {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      letter-spacing: 0.03em;
      color: #fff;
    }

    .logo span {
      color: var(--red);
    }

    .search-bar {
      flex: 1;
      max-width: 520px;
      position: relative;
    }

    .search-bar input {
      width: 100%;
      background: #1c1c1c;
      border: 1px solid var(--border);
      color: var(--text);
      padding: 0.85rem 3rem 0.85rem 1rem;
      border-radius: 8px;
      font-size: 0.95rem;
    }

    .search-bar input:focus {
      outline: none;
      border-color: var(--red);
    }

    .search-bar .icon {
      position: absolute;
      right: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 1.1rem;
      pointer-events: none;
    }

    .profile-circle {
      width: 46px;
      height: 46px;
      border-radius: 50%;
      background: #2a2a2a;
      border: 1px solid var(--border);
      flex-shrink: 0;
    }


    nav {
      background: #0d0d0d;
      border-bottom: 1px solid var(--border);
      display: flex;
      justify-content: center;
      gap: 0;
    }

    nav a {
      text-decoration: none;
      color: var(--muted);
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700;
      font-size: 0.9rem;
      letter-spacing: 0.12em;
      padding: 1.1rem 2.4rem;
      text-transform: uppercase;
      position: relative;
      transition: color 0.2s;
    }

    nav a:hover { color: var(--text); }

    nav a.active {
      color: var(--text);
    }

    nav a.active::after {
      content: '';
      position: absolute;
      bottom: 0; left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 2px;
      background: var(--red);
      border-radius: 2px;
    }


    .products-section {
      max-width: 1250px;
      margin: 3rem auto 4rem;
      padding: 0 2.5rem;
    }

    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 1rem;
      margin-bottom: 1.8rem;
      flex-wrap: wrap;
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }

    .section-title .line {
      width: 4px;
      height: 34px;
      background: var(--red);
      border-radius: 2px;
    }

    .section-title h2 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2.2rem;
      letter-spacing: 0.05em;
      color: #fff;
      text-transform: uppercase;
    }

    .view-all {
      background: var(--surface2);
      border: 1px solid var(--border);
      color: var(--muted);
      padding: 0.7rem 1.2rem;
      border-radius: 6px;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.9rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      transition: 0.2s;
    }

    .view-all:hover {
      color: var(--text);
      border-color: var(--text);
    }


    .filters {
      display: flex;
      gap: 0.7rem;
      flex-wrap: wrap;
      margin-bottom: 2.2rem;
    }

    .filter-btn {
      padding: 0.6rem 1.15rem;
      border-radius: 999px;
      border: 1px solid var(--border);
      background: #181818;
      color: var(--muted);
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.2s;
    }

    .filter-btn:hover {
      border-color: var(--red);
      color: var(--text);
    }

    .filter-btn.active {
      background: var(--red);
      color: #fff;
      border-color: var(--red);
    }


    .product-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 1.2rem;
    }

    .product-card {
      background: #161616;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 1rem;
      transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
      display: flex;
      flex-direction: column;
    }

    .product-card:hover {
      transform: translateY(-4px);
      border-color: var(--red);
      box-shadow: 0 10px 28px rgba(0,0,0,0.35);
    }

    .product-image {
      width: 100%;
      aspect-ratio: 3/4;
      border-radius: 5px;
      overflow: hidden;
      background: var(--surface2);
      margin-bottom: 0.9rem;
      border: 1px solid #252525;
    }

    .product-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 0.3s ease;
    }

    .product-card:hover .product-image img {
      transform: scale(1.03);
    }

    .product-title {
      font-size: 0.95rem;
      font-weight: 600;
      color: #f4f4f4;
      line-height: 1.35;
      min-height: 48px;
      margin-bottom: 0.9rem;
    }

    .product-price {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      margin-bottom: 0.35rem;
    }

    .rating {
      display: flex;
      align-items: center;
      gap: 0.45rem;
      margin-bottom: 1rem;
    }

    .stars {
      color: var(--gold);
      font-size: 0.95rem;
      letter-spacing: 0.03em;
    }

    .review-count {
      font-size: 0.78rem;
      color: var(--muted);
    }

    .btn-cart {
      margin-top: auto;
      width: 100%;
      padding: 0.85rem 1rem;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      border-radius: 5px;
      border: none;
      background: var(--red);
      color: #fff;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-cart:hover {
      background: var(--red-dark);
      transform: translateY(-1px);
      box-shadow: 0 6px 18px rgba(224,32,32,0.35);
    }


    #toast {
      position: fixed;
      bottom: 2rem; right: 2rem;
      background: #222;
      border: 1px solid var(--border);
      border-left: 3px solid var(--red);
      color: var(--text);
      font-size: 0.82rem;
      padding: 0.75rem 1.2rem;
      border-radius: 5px;
      opacity: 0;
      transform: translateY(12px);
      transition: all 0.3s;
      pointer-events: none;
      z-index: 999;
    }

    #toast.show { opacity: 1; transform: translateY(0); }


    @media (max-width: 1150px) {
      .product-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 820px) {
      .top-header {
        flex-wrap: wrap;
        justify-content: center;
      }

      .search-bar {
        order: 3;
        width: 100%;
        max-width: 100%;
      }

      .product-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 560px) {
      .products-section {
        padding: 0 1.2rem;
      }

      nav {
        flex-wrap: wrap;
      }

      nav a {
        padding: 1rem 1.2rem;
      }

      .product-grid {
        grid-template-columns: 1fr;
      }

      .section-title h2 {
        font-size: 1.8rem;
      }
    }

    /* ── PRODUCT DETAIL PAGE ── */

    .product-detail-wrapper {
      max-width: 1100px;
      margin: 3rem auto;
      padding: 0 2.5rem;
    }

    /* Hero */
    .product-hero {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 3rem;
      margin-bottom: 4rem;
      align-items: flex-start;
    }

    .hero-gallery {
      display: flex;
      flex-direction: column;
      gap: 0.85rem;
    }

    .hero-main-img {
      width: 100%;
      aspect-ratio: 3/4;
      background: #1c1c1c;
      border: 1px solid var(--border);
      border-radius: 8px;
      overflow: hidden;
    }

    .hero-main-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .hero-thumbs {
      display: flex;
      gap: 0.65rem;
    }

    .hero-thumb {
      flex: 1;
      aspect-ratio: 3/4;
      background: #1c1c1c;
      border: 1px solid var(--border);
      border-radius: 5px;
      overflow: hidden;
      cursor: pointer;
      transition: border-color 0.2s;
    }

    .hero-thumb:hover,
    .hero-thumb.active {
      border-color: var(--red);
    }

    .hero-thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    /* Hero Info */
    .hero-info {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
      padding-top: 0.5rem;
    }

    .hero-title {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 3rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.03em;
      line-height: 1.05;
      color: #fff;
    }

    .hero-subtitle {
      font-size: 0.95rem;
      color: var(--muted);
      margin-top: -0.6rem;
    }

    .hero-rating {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .hero-rating .stars { color: var(--gold); font-size: 1.05rem; }
    .hero-rating .count { font-size: 0.85rem; color: var(--muted); }

    .hero-price-row {
      display: flex;
      align-items: baseline;
      gap: 0.8rem;
    }

    .hero-price {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 2.8rem;
      font-weight: 800;
      color: #fff;
    }

    .hero-strike {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.3rem;
      color: var(--strike);
      text-decoration: line-through;
    }

    .hero-divider {
      border: none;
      border-top: 1px solid var(--border);
    }

    /* Volume selector */
    .volume-label {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 0.55rem;
    }

    .volume-btns {
      display: flex;
      gap: 0.6rem;
      flex-wrap: wrap;
    }

    .vol-btn {
      padding: 0.55rem 1.1rem;
      background: #181818;
      border: 1px solid var(--border);
      color: var(--muted);
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.85rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .vol-btn:hover { border-color: var(--text); color: var(--text); }
    .vol-btn.active { background: var(--red); border-color: var(--red); color: #fff; }

    /* CTA buttons */
    .cta-row {
      display: flex;
      gap: 0.85rem;
      margin-top: 0.5rem;
    }

    .btn-add-cart {
      flex: 1;
      padding: 1rem;
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text);
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.9rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-add-cart:hover { border-color: var(--text); background: #1e1e1e; }

    .btn-buy-now {
      flex: 1.4;
      padding: 1rem;
      background: var(--red);
      border: none;
      color: #fff;
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 0.9rem;
      font-weight: 700;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-buy-now:hover {
      background: var(--red-dark);
      box-shadow: 0 6px 20px rgba(224,32,32,0.35);
      transform: translateY(-1px);
    }

    /* Description */
    .detail-section {
      margin-bottom: 3.5rem;
    }

    .detail-section-title {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      margin-bottom: 1.6rem;
    }

    .detail-section-title .line {
      width: 4px;
      height: 28px;
      background: var(--red);
      border-radius: 2px;
    }

    .detail-section-title h3 {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.7rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #fff;
    }

    .desc-block {
      background: #141414;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 1.8rem;
    }

    .desc-sub {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 1.2rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: var(--red);
      margin-bottom: 0.8rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--border);
    }

    .desc-text {
      font-size: 0.92rem;
      line-height: 1.7;
      color: #ccc;
      margin-bottom: 1.8rem;
    }

    .desc-details {
      display: flex;
      flex-direction: column;
      gap: 0.45rem;
      font-size: 0.88rem;
    }

    .desc-details .detail-row {
      display: flex;
      gap: 0.5rem;
    }

    .desc-details .detail-key {
      font-weight: 700;
      color: var(--text);
      min-width: 200px;
    }

    .desc-details .detail-val {
      color: #aaa;
    }

    /* You May Also Like */
    .similar-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 1.2rem;
    }

    /* Reviews */
    .reviews-block {
      background: #141414;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 1.8rem;
    }

    .reviews-summary {
      display: flex;
      align-items: center;
      gap: 2.5rem;
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px solid var(--border);
    }

    .reviews-score {
      text-align: center;
    }

    .reviews-score .big-num {
      font-family: 'Barlow Condensed', sans-serif;
      font-size: 5rem;
      font-weight: 800;
      color: var(--red);
      line-height: 1;
    }

    .reviews-score .stars { color: var(--gold); font-size: 1.1rem; }
    .reviews-score .count { font-size: 0.78rem; color: var(--muted); margin-top: 0.3rem; }

    .reviews-bars {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }

    .bar-row {
      display: flex;
      align-items: center;
      gap: 0.7rem;
      font-size: 0.8rem;
    }

    .bar-label {
      font-family: 'Barlow Condensed', sans-serif;
      font-weight: 700;
      color: var(--muted);
      min-width: 10px;
    }

    .bar-track {
      flex: 1;
      height: 6px;
      background: #2a2a2a;
      border-radius: 3px;
      overflow: hidden;
    }

    .bar-fill {
      height: 100%;
      background: var(--red);
      border-radius: 3px;
    }

    .review-card {
      padding: 1.2rem 0;
      border-bottom: 1px solid var(--border);
    }

    .review-card:last-child { border-bottom: none; }

    .review-header {
      display: flex;
      align-items: center;
      gap: 0.85rem;
      margin-bottom: 0.5rem;
    }

    .reviewer-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: #2a2a2a;
      border: 1px solid var(--border);
      flex-shrink: 0;
    }

    .reviewer-meta {
      flex: 1;
    }

    .reviewer-name {
      font-weight: 700;
      font-size: 0.92rem;
      color: var(--text);
    }

    .reviewer-info {
      font-size: 0.78rem;
      color: var(--muted);
      margin-top: 0.15rem;
    }

    .reviewer-stars { color: var(--gold); font-size: 0.88rem; }

    .review-text {
      font-size: 0.88rem;
      line-height: 1.65;
      color: #bbb;
      padding-left: 3.1rem;
    }

    /* Responsive */
    @media (max-width: 860px) {
      .product-hero { grid-template-columns: 1fr; gap: 2rem; }
      .similar-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 560px) {
      .product-detail-wrapper { padding: 0 1.2rem; }
      .hero-title { font-size: 2.2rem; }
      .similar-grid { grid-template-columns: repeat(2, 1fr); }
      .reviews-summary { flex-direction: column; gap: 1rem; }
    }
  </style>
</head>
<body>

<header class="top-header">
  <div class="logo">MANGA<span>QUILLA</span></div>

  <div class="search-bar">
    <input type="text" placeholder="Search manga..." />
    <span class="icon">🔍</span>
  </div>

  <div class="profile-circle"></div>
</header>

<nav>
  <a href="index.php">Home</a>
  <a href="product1.php" class="active">Products</a>
  <a href="product1.php">New Arrivals</a>
  <a href="about.php">About Us</a>
</nav>



<!-- PRODUCT DETAIL CONTENT -->
<div class="product-detail-wrapper">

  <!-- Hero: Image Gallery + Info -->
  <div class="product-hero">

    <!-- Gallery -->
    <div class="hero-gallery">
      <div class="hero-main-img">
        <img id="mainImg" src="" alt="Demon Slayer Cover" onerror="this.style.display='none'"/>
      </div>
      <div class="hero-thumbs">
        <div class="hero-thumb active" onclick="switchThumb(this, 0)"><img src="" alt="" onerror="this.style.display='none'"/></div>
        <div class="hero-thumb" onclick="switchThumb(this, 1)"><img src="" alt="" onerror="this.style.display='none'"/></div>
        <div class="hero-thumb" onclick="switchThumb(this, 2)"><img src="" alt="" onerror="this.style.display='none'"/></div>
        <div class="hero-thumb" onclick="switchThumb(this, 3)"><img src="" alt="" onerror="this.style.display='none'"/></div>
        <div class="hero-thumb" onclick="switchThumb(this, 4)"><img src="" alt="" onerror="this.style.display='none'"/></div>
      </div>
    </div>

    <!-- Info -->
    <div class="hero-info">
      <div>
        <h1 class="hero-title">Demon Slayer</h1>
        <p class="hero-subtitle">Infinite Castle Arc, Volume 1</p>
      </div>

      <div class="hero-rating">
        <span class="stars">★★★★★</span>
        <span class="count">(50)</span>
      </div>

      <div class="hero-price-row">
        <span class="hero-price">₱600.00</span>
        <span class="hero-strike">₱900.00</span>
      </div>

      <hr class="hero-divider"/>

      <div>
        <div class="volume-label">Select Volume</div>
        <div class="volume-btns">
          <button class="vol-btn active" onclick="selectVol(this)">Vol.1</button>
          <button class="vol-btn" onclick="selectVol(this)">Vol.2</button>
          <button class="vol-btn" onclick="selectVol(this)">Vol.3</button>
          <button class="vol-btn" onclick="selectVol(this)">Vol.4</button>
        </div>
      </div>

      <div class="cta-row">
        <button class="btn-add-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
        <button class="btn-buy-now">Buy Now</button>
      </div>
    </div>
  </div>

  <!-- Description -->
  <div class="detail-section">
    <div class="detail-section-title">
      <div class="line"></div>
      <h3>Description</h3>
    </div>

    <div class="desc-block">
      <div class="desc-sub">Story</div>
      <p class="desc-text">
        Step into a world where courage battles darkness in Demon Slayer: Kimetsu no Yaiba.
        This globally acclaimed anime follows Tanjiro Kamado, a kind-hearted boy turned demon slayer after his
        family is brutally attacked by demons, leaving only his sister Nezuko—who has been transformed into one.
        Blending breathtaking animation, emotional storytelling, and intense sword-fighting action, Demon Slayer
        delivers an unforgettable viewing experience. Each episode showcases stunning visuals and powerful
        character development as Tanjiro journeys to avenge his family and find a cure for his sister.
      </p>

      <div class="desc-sub">Details</div>
      <div class="desc-details">
        <div class="detail-row"><span class="detail-key">Original Creator (Manga Author):</span><span class="detail-val">Koyoharu Gotouge</span></div>
        <div class="detail-row"><span class="detail-key">Anime Studio:</span><span class="detail-val">Ufotable</span></div>
        <div class="detail-row"><span class="detail-key">Director:</span><span class="detail-val">Haruo Sotozaki</span></div>
        <div class="detail-row"><span class="detail-key">Genre:</span><span class="detail-val">Action, Adventure, Dark Fantasy</span></div>
        <div class="detail-row"><span class="detail-key">Original Release:</span><span class="detail-val">2019</span></div>
        <div class="detail-row"><span class="detail-key">Source Material:</span><span class="detail-val">Manga series published in Weekly Shōnen Jump</span></div>
        <div class="detail-row"><span class="detail-key">Main Character:</span><span class="detail-val">Tanjiro Kamado</span></div>
        <div class="detail-row"><span class="detail-key">Notable Companion:</span><span class="detail-val">Nezuko Kamado</span></div>
      </div>
    </div>
  </div>

  <!-- You May Also Like -->
  <div class="detail-section">
    <div class="detail-section-title">
      <div class="line"></div>
      <h3>You May Also Like</h3>
    </div>

    <div class="similar-grid">
      <!-- Card 1 -->
      <div class="product-card">
        <div class="product-image">
          <img src="https://m.media-amazon.com/images/I/81lBIqIlFQL.jpg" alt="Jujutsu Kaisen"/>
        </div>
        <div class="product-title">Jujutsu Kaisen, Vol. 1</div>
        <div class="product-price">₱600.00</div>
        <div class="rating"><span class="stars">★★★★★</span><span class="review-count">(50)</span></div>
        <button class="btn-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
      </div>
      <!-- Card 2 -->
      <div class="product-card">
        <div class="product-image">
          <img src="https://m.media-amazon.com/images/I/91GpFkG4FUL.jpg" alt="Naruto Shippuden"/>
        </div>
        <div class="product-title">Naruto: Shippuden</div>
        <div class="product-price">₱600.00</div>
        <div class="rating"><span class="stars">★★★★★</span><span class="review-count">(60)</span></div>
        <button class="btn-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
      </div>
      <!-- Card 3 -->
      <div class="product-card">
        <div class="product-image">
          <img src="https://m.media-amazon.com/images/I/91M9vhAvO4L.jpg" alt="One Piece"/>
        </div>
        <div class="product-title">One Piece, Vol. 100</div>
        <div class="product-price">₱600.00</div>
        <div class="rating"><span class="stars">★★★★★</span><span class="review-count">(60)</span></div>
        <button class="btn-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
      </div>
      <!-- Card 4 -->
      <div class="product-card">
        <div class="product-image">
          <img src="https://m.media-amazon.com/images/I/81cBbsz+2RL.jpg" alt="Bleach"/>
        </div>
        <div class="product-title">Bleach, Vol. 1</div>
        <div class="product-price">₱600.00</div>
        <div class="rating"><span class="stars">★★★★★</span><span class="review-count">(40)</span></div>
        <button class="btn-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
      </div>
      <!-- Card 5 -->
      <div class="product-card">
        <div class="product-image">
          <img src="https://m.media-amazon.com/images/I/71lMFd1ROBL.jpg" alt="One Punch Man"/>
        </div>
        <div class="product-title">One Punch Man, Vol. 34</div>
        <div class="product-price">₱600.00</div>
        <div class="rating"><span class="stars">★★★★★</span><span class="review-count">(92)</span></div>
        <button class="btn-cart" onclick="showToast('Added to cart!')">Add to Cart</button>
      </div>
    </div>
  </div>

  <!-- Reviews -->
  <div class="detail-section">
    <div class="detail-section-title">
      <div class="line"></div>
      <h3>Reviews</h3>
    </div>

    <div class="reviews-block">
      <!-- Summary -->
      <div class="reviews-summary">
        <div class="reviews-score">
          <div class="big-num">4.9</div>
          <div class="stars">★★★★★</div>
          <div class="count">(50)</div>
        </div>
        <div class="reviews-bars">
          <div class="bar-row">
            <span class="bar-label">5</span>
            <div class="bar-track"><div class="bar-fill" style="width:88%"></div></div>
          </div>
          <div class="bar-row">
            <span class="bar-label">4</span>
            <div class="bar-track"><div class="bar-fill" style="width:55%"></div></div>
          </div>
          <div class="bar-row">
            <span class="bar-label">3</span>
            <div class="bar-track"><div class="bar-fill" style="width:20%"></div></div>
          </div>
          <div class="bar-row">
            <span class="bar-label">2</span>
            <div class="bar-track"><div class="bar-fill" style="width:10%"></div></div>
          </div>
          <div class="bar-row">
            <span class="bar-label">1</span>
            <div class="bar-track"><div class="bar-fill" style="width:8%"></div></div>
          </div>
        </div>
      </div>

      <!-- Review 1 -->
      <div class="review-card">
        <div class="review-header">
          <div class="reviewer-avatar"></div>
          <div class="reviewer-meta">
            <div class="reviewer-name">Benedict Garma</div>
            <div class="reviewer-info">
              <span class="reviewer-stars">★★★★★</span>
              &nbsp; 2025-07-24 11:39 &nbsp;|&nbsp; Variation: Vol.1
            </div>
          </div>
        </div>
        <p class="review-text">
          "Absolutely stunning to read. The manga illustration is on another level every fight feels like a movie scene.
          The story is simple but emotional, especially Tanjiro and Nezuko's bond. Highly recommended!"
        </p>
      </div>

      <!-- Review 2 -->
      <div class="review-card">
        <div class="review-header">
          <div class="reviewer-avatar"></div>
          <div class="reviewer-meta">
            <div class="reviewer-name">Benedict Garma</div>
            <div class="reviewer-info">
              <span class="reviewer-stars">★★★★★</span>
              &nbsp; 2025-07-24 11:39 &nbsp;|&nbsp; Variation: Vol.1
            </div>
          </div>
        </div>
        <p class="review-text">
          "I started reading Demon Slayer out of curiosity, and I ended up finishing the entire manga in just a
          few days. The storytelling is straightforward but very emotional, especially the relationship between
          Tanjiro and Nezuko. What I really appreciated is how the author didn't drag the story too long—it has
          a clear direction and a satisfying ending. The art may look simple at first, but during fight scenes,
          it becomes incredibly expressive and intense. Definitely a must-read for manga fans."
        </p>
      </div>
    </div>
  </div>

</div><!-- end product-detail-wrapper -->

<!-- Toast -->
<div id="toast"></div>

<script>
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function selectVol(btn) {
    document.querySelectorAll('.vol-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
  }

  function switchThumb(el, idx) {
    document.querySelectorAll('.hero-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
  }
</script>

</body>
</html>