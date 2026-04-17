<?php
// 1. DATABASE & SESSION INTERCONNECTION
require 'config.php';
session_start();

// Security check: Redirect if not logged in
if (!isset($_SESSION["login_data"])) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Preserve your original variable naming
$user_data = $_SESSION["login_data"];
$userid = $user_data["id"];

try {
    // Standardized database path
    require 'db/action/dbconfig.php';

    // Fetch the specific user data to refresh the profile circle
    $stmt = $conn->prepare("SELECT * FROM login WHERE id = ?");
    $stmt->execute([$userid]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MangaQuilla | Home</title>

    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Barlow:wght@400;500;600;700&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Exact preservation of your original CSS composition */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:       #111111;
            --surface:  #1a1a1a;
            --surface2: #222222;
            --border:   #2e2e2e;
            --red:      #e02020;
            --red-dark: #b81818;
            --text:     #f0f0f0;
            --muted:    #888888;
            --gold:     #f5a623;
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
            text-decoration: none;
        }

        .logo span { color: var(--red); }

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

        .search-bar .icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 1.1rem;
        }

        .profile-circle {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            border: 2px solid var(--red);
            object-fit: cover;
            flex-shrink: 0;
        }

        nav {
            background: #0d0d0d;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: center;
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
        }

        nav a.active { color: var(--text); }

        nav a.active::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 2px;
            background: var(--red);
        }

        .hero-banner {
            height: 450px;
            background: linear-gradient(rgba(0,0,0,0.35), var(--bg)),
                        url('images/demonslayer.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            padding: 0 10%;
        }

        .hero-title {
            font-family: 'Bebas Neue', cursive;
            font-size: 4.5rem;
            line-height: 0.9;
            color: #fff;
        }

        .hero-title span { color: var(--red); }

        .products-section {
            max-width: 1250px;
            margin: 3rem auto 4rem;
            padding: 0 2.5rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            margin-bottom: 1.8rem;
        }

        .section-title .line {
            width: 4px;
            height: 34px;
            background: var(--red);
        }

        .section-title h2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2.2rem;
            color: #fff;
            text-transform: uppercase;
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
            display: flex;
            flex-direction: column;
            text-decoration: none;
            color: inherit;
        }

        .product-image {
            width: 100%;
            aspect-ratio: 3/4;
            overflow: hidden;
            background: var(--surface2);
            margin-bottom: 0.9rem;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-title {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: #f4f4f4;
            min-height: 2.4rem;
        }

        .btn-update {
            margin-top: auto;
            width: 100%;
            padding: 0.85rem 1rem;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 700;
            background: var(--red);
            color: #fff;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
        }

        footer {
            text-align: center;
            padding: 2rem;
            border-top: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        @media (max-width: 1150px) { .product-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 820px) { .product-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 560px) { .product-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<header class="top-header">
    <a href="index.php" class="logo">MANGA<span>QUILLA</span></a>

    <div class="search-bar">
        <input type="text" placeholder="Search your favorite manga...">
        <span class="icon">🔍</span>
    </div>

    <a href="profile.php">
        <img src="images/<?php echo !empty($user_data['img_url']) ? htmlspecialchars($user_data['img_url']) : 'default-avatar.png'; ?>" 
             class="profile-circle" 
             alt="Profile" 
             onerror="this.src='https://ui-avatars.com/api/?name=<?php echo $user_data['username']; ?>'">
    </a>
</header>

<nav>
    <a href="index.php" class="active">Home</a>
    <a href="product1.php">Products</a>
    <a href="#">New Arrivals</a>
    <a href="profile.php">My Profile</a>
</nav>

<div class="hero-banner">
    <div>
        <h1 class="hero-title">DEMON SLAYER:<br><span>KIMETSU NO YAIBA</span></h1>
        <p class="hero-sub" style="color: var(--red); font-weight: bold;">
            Welcome back, <?php echo htmlspecialchars($user_data['username']); ?>!
        </p>
    </div>
</div>

<section class="products-section">
    <div class="section-header">
        <div class="section-title">
            <div class="line"></div>
            <h2>All Titles</h2>
        </div>
    </div>

    <div class="product-grid">
        <?php
        // Fetch books exactly as defined in your original composition
        $books = $conn->query("SELECT * FROM books ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($books as $row) :
        ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
            </div>
            <div class="product-title"><?php echo htmlspecialchars($row['title']); ?></div>
            <p class="product-excerpt"><?php echo substr(htmlspecialchars($row['excerpt']), 0, 80); ?>...</p>
            
            <a href="updatebooks.php?id=<?php echo $row['id']; ?>" class="btn-update">Update Title</a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<footer>
    &copy; 2026 MANGAQUILLA &bull; CICT Bulacan State University
</footer>

</body>
</html>