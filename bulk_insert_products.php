<?php
require 'db/action/connect.php'; // or db/connection.php

function parseCategory($path) {
    $parts = explode('/', $path);
    if (strpos($parts[1], 'Men') !== false) {
        $gender = 'men';
    } elseif (strpos($parts[1], 'Women') !== false) {
        $gender = 'women';
    } else {
        return null;
    }
    $cat = strtolower(str_replace(['Men Category/', 'Women Category/', 'MEN ', 'WOMAN '], '', $parts[2]));
    $cat = str_replace([' - ', ' '], '-', $cat);
    $cats = ['basic-tops', 'outerwear', 'pants', 'shorts'];
    foreach ($cats as $c) {
        if (strpos($cat, $c) !== false) {
            return ['gender' => $gender, 'category' => $c];
        }
    }
    return null;
}

$dir = 'images/Product Images';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$products = [];
foreach ($iterator as $file) {
    if ($file->getExtension() === 'png') {
        $path = $file->getPathname();
$rel_path = str_replace('c:/xampp/htdocs/LYNX/', '', $path);
        $cat = parseCategory($rel_path);
        if ($cat) {
            $name = $file->getFilenameWithoutExtension();
            $img = substr($rel_path, strrpos($rel_path, '/') + 1); // filename only
            $products[] = [
                'title' => $name,
                'excerpt' => 'New product from bulk import.',
                'price' => 29.99,
                'imgurl' => $img,
                'category' => $cat['category'],
                'table' => $cat['gender'] . '_products'
            ];
            echo "Queued: $name for {$cat['gender']}/{$cat['category']}\n";
        }
    }
}

foreach ($products as $p) {
    $table = $p['table'];
    $sql = "INSERT INTO $table (title, excerpt, price, imgurl, category) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$p['title'], $p['excerpt'], $p['price'], $p['imgurl'], $p['category']]);
    echo "Inserted: {$p['title']}\n";
}

echo "Bulk insert complete. " . count($products) . " products added.";
?>

