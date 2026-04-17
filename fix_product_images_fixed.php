<?php
// Standalone PDO connection + image fix script
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lynx_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB connected.\n";
} catch(PDOException $e) {
    die("DB error: " . $e->getMessage() . "\n");
}

// Category mappings
$cat_dirs = [
    'basic-tops' => ['MEN -  BASIC TOP', 'WOMAN BASIC TOP'],
    'outerwear' => ['MEN OUTERWEAR', 'WOMAN OUTERWEAR'],
    'pants' => ['MEN PANTS', 'WOMAN PANTS'],
    'shorts' => ['MEN SHORTS', 'WOMAN SHORTS']
];

$tables = ['men_products', 'women_products'];
$fixed = 0;
$skipped = 0;

foreach ($tables as $table) {
    $gender = ($table == 'men_products') ? 'Men' : 'Women';
    echo "Processing $table ($gender)...\n";
    
    $stmt = $conn->prepare("SELECT id, title, imgurl, category FROM `$table` WHERE imgurl IS NOT NULL AND imgurl != '' ORDER BY title");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($products) . " products.\n";
    
    foreach ($products as $p) {
        $img_filename = trim($p['imgurl']);
        if (pathinfo($img_filename, PATHINFO_EXTENSION) != 'png') {
            $img_filename .= '.png';
        }
        
        if (!isset($cat_dirs[$p['category']])) {
            echo "Skip {$p['title']}: unknown category '{$p['category']}'\n";
            $skipped++;
            continue;
        }
        
        $found = false;
        foreach ($cat_dirs[$p['category']] as $dir) {
            $source_path = "images/Product Images/{$gender} Category/{$dir}/{$img_filename}";
            if (file_exists($source_path)) {
                $dest_path = "images/products/{$img_filename}";
                if (copy($source_path, $dest_path)) {
                    echo "Fixed: {$p['title']} ({$p['category']}) -> {$img_filename}\n";
                    $fixed++;
                } else {
                    echo "Failed copy {$source_path} to {$dest_path}\n";
                }
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo "No source found for {$p['title']} ({$img_filename})\n";
            $skipped++;
        }
    }
}

echo "\nSummary: Fixed $fixed images, skipped $skipped.\n";
echo "Refresh men-basic-tops.php to test Black Lynx Varsity Jersey!\n";
?>
