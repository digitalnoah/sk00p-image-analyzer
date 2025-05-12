<?php
// Shared infrastructure bootstrapping for image-analyzer
$projectRoot = __DIR__ . '/../';
require_once $projectRoot . 'require_tools.php';

use Sk00p\UI;

// Collect HTML content
$html = file_get_contents(__DIR__ . '/index.html');

// Remove the first <header>...</header> block to avoid duplicate headers
$html = preg_replace('/<header[\s\S]*?<\/header>/', '', $html, 1);

// Output document until <body> tag then insert shared header
$parts = explode('<body>', $html, 2);
if (count($parts) === 2) {
    echo $parts[0];
    echo '<body>';
    UI::header();
    echo $parts[1];
} else {
    // Fallback
    UI::header();
    echo $html;
}