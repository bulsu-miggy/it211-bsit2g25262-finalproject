
<?php
require 'db/connection.php';

$base_dir = 'images/Product Images';
$products = [];

foreach (['Men Category', 'Women Category'] as $gender_dir) {
    $gender = strtolower(str_replace(' Category', '', $gender_dir));
    $gender_dir_path = $base_dir . '/' . $gender_dir;
    if (!is_dir($gender_dir_path)) continue;

    $cats = ['MEN -  BASIC TOP', 'MEN OUTERWEAR', 'MEN PANTS', 'MEN SHORTS', 'WOMAN BASIC TOP', 'WOMAN OUTERWEAR', 'WOMAN PANTS', 'WOMAN SHORTS'];
    $cat_map = [
        'basic-tops' => ['MEN -  BASIC TOP', 'WOMAN BASIC TOP'],
        'outerwear' => ['MEN OUTERWEAR', 'WOMAN OUTERWEAR'],
        'pants' => ['MEN PANTS', 'WOMAN PANTS'],
        'shorts' => ['MEN SHORTS', 'WOMAN SHORTS']
    ];

    foreach ($cat_map as $cat => $dirs) {
        foreach ($dirs as $dir) {
            $full_dir = $gender_dir_path . '/' . $dir;
            if (!is_dir($full_dir)) continue;
            
            $files = glob($full_dir . '/*.png');
            foreach ($files as $file) {
                $filename = basename($file);
                $title = pathinfo($filename, PATHINFO_FILENAME);
                $rel_img = 'images/Product Images/' . $gender_dir . '/' . $dir . '/' . $filename;
                $products[] = [
                    'title' => $title,
                    'excerpt' => 'Premium clothing item.',
                    'price' => rand(19, 89) / 10 * 10, 
                    'imgurl' => $filename, 
                    'category' => $cat,
                    'table' => $gender . '_products'
                ];
            }
        }
    }
}

echo "Found " . count($products) . " products to insert.\n";

foreach ($products as $p) {
    $table = $p['table'];
    $sql = "INSERT IGNORE INTO $table (title, excerpt, price, imgurl, category) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE imgurl=imgurl";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$p['title'], $p['excerpt'], $p['price'], $p['imgurl'], $p['category']]);
    echo "Added/Updated: {$p['title']} to {$table} ({$p['category']})\n";
}

echo "Bulk insert complete.";
?>

