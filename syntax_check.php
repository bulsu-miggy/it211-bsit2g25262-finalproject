<?php
// Simple syntax check for admin index
echo "Testing admin index syntax...\n";

// Try to parse the file
$code = file_get_contents('Admin/index.php');
if ($code === false) {
    echo "Could not read file\n";
    exit(1);
}

// Basic syntax check - look for obvious issues
$errors = [];

// Check for unclosed PHP tags
$php_open = substr_count($code, '<?php');
$php_close = substr_count($code, '?>');
if ($php_open !== $php_close) {
    $errors[] = "PHP tags mismatch: $php_open open, $php_close close";
}

// Check for obvious quote issues
$single_quotes = substr_count($code, "'");
$double_quotes = substr_count($code, '"');
// This is not a perfect check but can catch some issues

if (empty($errors)) {
    echo "Basic syntax check passed\n";
} else {
    echo "Found potential issues:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
?>