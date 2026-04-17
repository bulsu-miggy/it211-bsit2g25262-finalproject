<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manga Profile</title>

<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #000000, #1a0000, #330000);
    background-size: cover;
    color: white;
}

/* FULLSCREEN CONTAINER */
.container {
    width: 100%;
    min-height: 100vh;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a0000 50%, #2a0000 100%);
    padding: 20px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}

/* TITLE */
.title {
    color: red;
    font-weight: bold;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.back-btn {
    display: inline-block;
    color: white;
    text-decoration: none;
    font-size: 14px;
    background: #111;
    padding: 8px 15px;
    border-radius: 6px;
    border: 1px solid #555;
    transition: 0.3s;
}

.back-btn:hover {
    background: red;
    border-color: red;
}

/* GRID */
.grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 20px;
    flex: 1;
}

/* CARDS */
.card {
    background: #111;
    border-radius: 10px;
    padding: 20px;
    color: white;
}

.right-top {
    margin-bottom: 20px;
}

.right-bottom {
    border: 2px solid #00aaff;
}

/* AVATAR */
.avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: gray;
    margin: auto;
    display: block;
    margin-bottom: 10px;
}

/* UNIFIED RED BUTTONS - ALL RED + WHITE TEXT */
.btn {
    background: red;
    color: white;
    padding: 12px 20px;
    border: none;
    margin-top: 10px;
    cursor: pointer;
    border-radius: 6px;
    width: 100%;
    font-weight: bold;
    font-size: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn:hover {
    background: #cc0000;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 0, 0, 0.6);
}

/* EMOJIS */
.cart-btn::before {
    content: '🛒';
    font-size: 18px;
}

.purchase-btn::before {
    content: '💰';
    font-size: 18px;
}

.cart-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* STATS */
.stats {
    display: flex;
    justify-content: space-around;
    margin-top: 10px;
    text-align: center;
}

.stats h2 {
    margin: 0;
    color: #00aaff;
}

/* LIST */
ul {
    padding-left: 20px;
}

li {
    margin: 5px 0;
}

/* MOBILE */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: 1fr;
    }
}
</style>

</head>
<body>

<?php
// 1. Connection & Session (Interconnection Logic)
session_start();
require 'db/action/dbconfig.php'; // Ensure this matches your path

// Check if user is logged in
if (!isset($_SESSION["login_data"])) {
    header('Location: login.php');
    exit();
}

// 2. KEEPING YOUR VARIABLE NAMES: Fetching Data
// We use the ID from the session to get the real data from your 'login' table
$userid = $_SESSION["login_data"]["id"];

try {
    $stmt = $conn->prepare("SELECT * FROM login WHERE id = ?");
    $stmt->execute([$userid]);
    $db_row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Map DB data to your existing $user_data array variable
    $user_data = [
        'id'            => $db_row['id'],
        'username'      => $db_row['username'],
        'member_since'  => date("M Y", strtotime($db_row['login_date'])), 
        'fav_genre'     => 'Action / Romance', // Static for now as per your frame
        'avatar'        => !empty($db_row['img_url']) ? $db_row['img_url'] : 'avatar.jpg',
        'total_read'    => 120,
        'favorites'     => 25,
        'chapters_read' => 560,
        'cart_items'    => 3
    ];

    // Map your $history variable
    $history = [
        "Demon Slayer - Chapter 120",
        "Attack on Titan - Chapter 80",
        "Jujutsu Kaisen - Chapter 200"
    ];

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// 3. Handling Actions
if (($_POST['action'] ?? '') === 'edit_profile') {
    header('Location: edit_profile.php?id=' . $user_data['id']);
    exit;
}

if (($_POST['action'] ?? '') === 'view_cart') {
    header('Location: cart.php');
    exit;
}

if (($_POST['action'] ?? '') === 'purchase') {
    header('Location: checkout.php');
    exit;
}

$message = '';
if (isset($_GET['saved'])) {
    $message = '<div style="color:#00ff00; padding:10px; background:#004400; border-radius:5px; margin-bottom:20px;">Profile updated successfully!</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manga Profile | <?php echo htmlspecialchars($user_data['username']); ?></title>

<style>
/* ... Keeping your exact CSS styles ... */
html, body { height: 100%; margin: 0; padding: 0; }
body { margin: 0; padding: 0; font-family: Arial, sans-serif; background: linear-gradient(135deg, #000000, #1a0000, #330000); background-size: cover; color: white; }
.container { width: 100%; min-height: 100vh; background: linear-gradient(135deg, #0a0a0a 0%, #1a0000 50%, #2a0000 100%); padding: 20px; box-sizing: border-box; display: flex; flex-direction: column; }
.title { color: red; font-weight: bold; margin-bottom: 20px; display: flex; align-items: center; gap: 20px; }
.back-btn { display: inline-block; color: white; text-decoration: none; font-size: 14px; background: #111; padding: 8px 15px; border-radius: 6px; border: 1px solid #555; transition: 0.3s; }
.back-btn:hover { background: red; border-color: red; }
.grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; flex: 1; }
.card { background: #111; border-radius: 10px; padding: 20px; color: white; }
.right-top { margin-bottom: 20px; }
.right-bottom { border: 2px solid #00aaff; }
.avatar { width: 100px; height: 100px; border-radius: 50%; background: gray; margin: auto; display: block; margin-bottom: 10px; object-fit: cover; border: 2px solid red; }
.btn { background: red; color: white; padding: 12px 20px; border: none; margin-top: 10px; cursor: pointer; border-radius: 6px; width: 100%; font-weight: bold; font-size: 16px; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 0, 0, 0.4); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; gap: 10px; text-decoration: none; }
.btn:hover { background: #cc0000; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 0, 0, 0.6); }
.cart-btn::before { content: '🛒'; font-size: 18px; }
.purchase-btn::before { content: '💰'; font-size: 18px; }
.cart-badge { position: absolute; top: -5px; right: -5px; background: #ff4444; color: white; border-radius: 50%; width: 20px; height: 20px; font-size: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
.stats { display: flex; justify-content: space-around; margin-top: 10px; text-align: center; }
.stats h2 { margin: 0; color: #00aaff; }
ul { padding-left: 20px; }
li { margin: 5px 0; }
@media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
</style>

</head>
<body>

<div class="container">

    <?php echo $message; ?>

    <h2 class="title">
        <a href="index.php" class="back-btn">← Back</a>
        PROFILE
    </h2>

    <div class="grid">

        <div class="card">
            <img src="images/<?php echo htmlspecialchars($user_data['avatar']); ?>" class="avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo $user_data['username']; ?>'">
            <h3><?php echo htmlspecialchars($user_data['username']); ?></h3>
            <p>Member Since: <?php echo htmlspecialchars($user_data['member_since']); ?></p>
            <p>Favorite Genre: <?php echo htmlspecialchars($user_data['fav_genre']); ?></p>

            <form method="POST">
                <input type="hidden" name="action" value="edit_profile">
                <button type="submit" class="btn">EDIT PROFILE</button>
            </form>

            <button class="cart-btn btn" onclick="viewCart()">
                <span class="cart-badge"><?php echo $user_data['cart_items']; ?></span>
                View Cart
            </button>

            <a href="logout.php" class="btn" style="background:transparent; border:1px solid #555; box-shadow:none; margin-top:15px;">LOGOUT</a>
        </div>

        <div>
            <div class="card right-top">
                <h3>Reading History (<?php echo count($history); ?>)</h3>
                <ul>
                    <?php foreach($history as $manga): ?>
                        <li><?php echo htmlspecialchars($manga); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card right-bottom">
                <h3>Manga Stats</h3>
                <div class="stats">
                    <div>
                        <h2><?php echo number_format($user_data['total_read']); ?></h2>
                        <p>Manga Read</p>
                    </div>
                    <div>
                        <h2><?php echo number_format($user_data['favorites']); ?></h2>
                        <p>Favorites</p>
                    </div>
                    <div>
                        <h2><?php echo number_format($user_data['chapters_read']); ?></h2>
                        <p>Chapters</p>
                    </div>
                </div>

                <form method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="action" value="purchase">
                    <button type="submit" class="purchase-btn btn">Proceed to Purchase</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function viewCart() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = '<input type="hidden" name="action" value="view_cart">';
    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>