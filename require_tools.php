<?php
// Shared toolbox for image analyzer - production & local compatible

// Try to resolve the path dynamically based on current directory
$possiblePaths = [
    // Local development paths
    '/Applications/MAMP/htdocs/ai-projects-sk00p/sk00p-root-tools',
    dirname(__DIR__) . '/sk00p-root-tools',
    dirname(dirname(__DIR__)) . '/sk00p-root-tools',
    // Production paths
    '/home/ec2-user/sk00p-root-tools',
    '/home/ec2-user/tools'
];

// Find the first existing tools path
$toolsPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/vendor/autoload.php')) {
        $toolsPath = $path;
        break;
    }
}

// If no valid path was found
if ($toolsPath === null) {
    die("Could not locate shared tools directory. Please check your installation.");
}

// Load the autoloader
require_once $toolsPath . '/vendor/autoload.php';

// Then load environment variables
$dotenv = Dotenv\Dotenv::createImmutable($toolsPath);
$dotenv->load();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    Sk00p\Session::start();
}