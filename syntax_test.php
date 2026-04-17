<?php
// Test if the admin index file can be parsed without syntax errors
echo "Testing admin index.php syntax...\n";

try {
    // Try to include the file (this will catch most syntax errors)
    ob_start();
    include 'Admin/index.php';
    ob_end_clean();
    echo "✅ Syntax check passed - no parse errors detected\n";
} catch (Throwable $e) {
    echo "❌ Syntax error detected: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}
?>