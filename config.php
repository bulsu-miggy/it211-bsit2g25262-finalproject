<?php
    $projectname = basename(__DIR__);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $projectUrlPath = '/' . $projectname;
        if (strpos($scriptName, $projectUrlPath) !== false) {
            $projectUrlPath = substr($scriptName, 0, strpos($scriptName, $projectUrlPath) + strlen($projectUrlPath));
        }
    $url = rtrim($protocol . '://' . $host . $projectUrlPath, '/');
    $images_folder = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . DIRECTORY_SEPARATOR . $projectname . DIRECTORY_SEPARATOR . 'images';
?>