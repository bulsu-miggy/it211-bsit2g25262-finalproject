<?php
require 'db/connection.php'; // assuming PDO conn

echo "Starting product image fix...\n";

// Category mappings
$cat_dirs = [
    'basic-tops' => ['MEN -  BASIC TOP', 'WOMAN BASIC TOP'],
    'outerwear' => ['MEN OUTERWEAR', 'WOMAN OUTERWEAR'],
    'pants' => ['MEN PANTS', 'WOMAN PANTS'],
    'shorts' => ['MEN SHORTS', 'WOMAN SHORTS']
];

$tables = ['men_products', 'women_products'];
$fixed = 0;

foreach ($tables as $table) {
    $gender = str_replace('_products', '', $table);
    $stmt = $conn->prepare("SELECT id, title, imgurl, category FROM $table WHERE imgurl IS NOT NULL AND imgurl != ''");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $p) {
        $img_filename = $p['imgurl'];
        if (pathinfo($img_filename, PATHINFO_EXTENSION) !== 'png') {
            $img_filename .= '.png';
        }
        $source_dirs = [];
        if (isset($cat_dirs[$p['category']])) {
            foreach ($cat_dirs[$p['category']] as $dir) {
                $source_path = "images/Product Images/{$gender === 'men' ? 'Men' : 'Women'} Category/{$dir}/{$img_filename}";
                if (file_exists($source_path)) {
                    $dest_path = "images/products/{$img_filename}";
                    if (copy($source_path, $dest_path)) {
                        echo "Fixed: {$p['title']} -> {$dest_path}\n";
                        $fixed++;
                    } else {
                        echo "Failed to copy: {$source_path}\n";
                    }
                    break;
                }
            }
        }
    }
}

echo "Fixed {$fixed} product images.\n";
echo "Done! Check men-basic-tops.php to verify.\n";
?>
