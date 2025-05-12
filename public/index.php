<?php
// Shared infrastructure bootstrapping for image-analyzer
$projectRoot = __DIR__ . '/../';
require_once $projectRoot . 'require_tools.php';

// Optionally you can expose user data to the front-end later here.

// Serve the existing static markup
readfile(__DIR__ . '/index.html');