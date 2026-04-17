<?php
session_start();
include 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- HANDLE VOUCHER CLAIM REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_voucher'])) {
    $voucher_title = $_POST['voucher_title'];

    $check_stmt = $conn->prepare("SELECT id FROM user_vouchers WHERE user_id = ? AND voucher_title = ?");
    $check_stmt->bind_param("is", $user_id, $voucher_title);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows === 0) {
        // Default is_used is 0 (not yet used)
        $insert_stmt = $conn->prepare("INSERT INTO user_vouchers (user_id, voucher_title, is_used) VALUES (?, ?, 0)");
        $insert_stmt->bind_param("is", $user_id, $voucher_title);
        
        if ($insert_stmt->execute()) {
            header("Location: giftcards.php?status=success&voucher=" . urlencode($voucher_title));
            exit();
        } else {
            header("Location: giftcards.php?status=error");
            exit();
        }
        $insert_stmt->close();
    } else {
        header("Location: giftcards.php?status=already_claimed");
        exit();
    }
    $check_stmt->close();
}

$vouchers_data = [
    ['title' => '10% OFF DISCOUNT', 'min_spend' => 0, 'image' => 'voucher.png'],
    ['title' => '20% OFF DISCOUNT', 'min_spend' => 0, 'image' => 'voucher.png'],
    ['title' => '30% OFF DISCOUNT', 'min_spend' => 0, 'image' => 'voucher.png']
];

// Kunin ang claimed at used status mula sa database
$user_vouchers_status = [];
$stmt = $conn->prepare("SELECT voucher_title, is_used FROM user_vouchers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_vouchers_status[$row['voucher_title']] = $row['is_used'];
}
$stmt->close();

$vouchers = [];
foreach ($vouchers_data as $v) {
    $is_claimed = isset($user_vouchers_status[$v['title']]);
    $is_used = $is_claimed ? ($user_vouchers_status[$v['title']] == 1) : false;
    $vouchers[] = array_merge($v, ['is_claimed' => $is_claimed, 'is_used' => $is_used]);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sparkverse | Gift Cards</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fdfdfd; margin: 0; padding-top: 100px; }
        .navbar { position: fixed; top: 0; left: 0; right: 0; background: white; border-top: 5px solid #FFD700; border-bottom: 2px solid #FFD700; box-shadow: 0 2px 10px rgba(0,0,0,0.05); z-index: 1000; height: 80px; display: flex; align-items: center; }
        .nav-container { width: 95%; max-width: 1400px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-section img { height: 55px; width: 55px; border-radius: 50%; }
        .logo-section span { font-weight: 800; font-size: 22px; color: #000; letter-spacing: 0.5px; }
        .nav-links { display: flex; gap: 30px; align-items: center;}
        .nav-links a { text-decoration: none; color: #00B4D8; font-weight: 700; font-size: 16px; transition: 0.3s; }
        .nav-links a.active { color: #00B4D8; border-bottom: 2px solid #00B4D8; }
        .nav-right { display: flex; align-items: center; gap: 20px; }
        .nav-right a { text-decoration: none; font-size: 24px; color: #333; display: flex; align-items: center; }
        .logout-btn { color: #C1121F !important; font-weight: 700; font-size: 18px !important; margin-left: 5px; }
        .main-wrapper { padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .page-title { color: #00B4D8; font-size: 32px; font-weight: bold; margin-bottom: 30px; text-transform: uppercase; }
        .voucher-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; }
        .voucher-card { background: white; border-radius: 15px; padding: 20px; border: 1px solid #eee; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: left; transition: 0.3s; }
        .voucher-card:hover { transform: translateY(-5px); }
        .voucher-card img { width: 100%; border-radius: 10px; margin-bottom: 15px; border: 2px solid #00B4D8; }
        .voucher-info h3 { font-size: 22px; color: #333; margin: 10px 0 5px 0; font-weight: 700; }
        .voucher-info p { font-size: 12px; color: #666; margin: 0; line-height: 1.4; }
        .claim-btn { display: block; width: 100%; text-align: center; color: #00B4D8; font-size: 24px; font-weight: 800; margin-top: 20px; text-decoration: none; border: none; background: none; cursor: pointer; transition: 0.3s; }
        .claim-btn:hover:not([disabled]) { color: #007791; transform: scale(1.05); }
        .claim-btn[disabled] { color: #888; cursor: not-allowed; opacity: 0.7; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: 600; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
<header class="navbar">
    <div class="nav-container">
        <div class="logo-section">
            <img src="character.png" alt="Logo">
            <span>SPARKVERSE</span>
        </div>

        <nav class="nav-links">
            <a href="home.php">Home</a>
            <a href="albums.php">Albums</a>
            <a href="photocards.php">Photocards</a>
            <a href="lightsticks.php">Lightsticks</a>
            <a href="merchandise.php">Merchandise</a>
            <a href="giftcards.php" class="active">Gift Cards</a>
            <a href="my_orders.php" >My Orders</a>
        </nav>

        <div class="nav-right">
            <a href="cart.php" title="Cart">🛒</a>
            <a href="profile.php" title="Profile">👤</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<div class="main-wrapper">
    <div class="container">
        <h1 class="page-title">Vouchers</h1>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
            <div class="alert alert-success">Successfully claimed voucher: <?php echo htmlspecialchars($_GET['voucher']); ?>!</div>
        <?php endif; ?>
        <div class="voucher-grid">
            <?php foreach ($vouchers as $v): ?>
                <div class="voucher-card">
                    <img src="GIFTCARDS/<?php echo $v['image']; ?>" alt="Voucher">
                    <div class="voucher-info">
                        <h3><?php echo $v['title']; ?></h3>
                        <p>Min. Spend ₱<?php echo $v['min_spend']; ?></p>
                        <p>VALID FOR ONE TIME USE ONLY</p>
                        <form method="post">
                            <input type="hidden" name="voucher_title" value="<?php echo htmlspecialchars($v['title']); ?>">
                            <?php if ($v['is_used']): ?>
                                <button type="button" class="claim-btn" style="color: #ff4d4d;" disabled>USED</button>
                            <?php elseif ($v['is_claimed']): ?>
                                <button type="button" class="claim-btn" disabled>CLAIMED</button>
                            <?php else: ?>
                                <button type="submit" name="claim_voucher" class="claim-btn">CLAIM</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>