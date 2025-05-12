<?php
// Shared toolbox for image analyzer
error_reporting(E_ALL);
ini_set('display_errors', 1);



// Check if we're in production (EC2)
if (file_exists('/home/ec2-user/tools/vendor/autoload.php')) {
    // Production path
    require_once '/home/ec2-user/tools/vendor/autoload.php';

    // Load environment variables if Dotenv exists
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable('/home/ec2-user/tools');
        $dotenv->load();
    }
} else {
    // Local development path
    $currentDir = __DIR__; // /Applications/MAMP/htdocs/ai-projects-sk00p/sk00p-image-analyzer

    // For local environment, we see from the debug output that the root directory should be:
    // /Applications/MAMP/htdocs/ai-projects-sk00p
    // So we need to get the parent directory of the current directory
    $rootDir = dirname($currentDir); // /Applications/MAMP/htdocs/ai-projects-sk00p

    $autoloadPath = $rootDir . '/sk00p-root-tools/vendor/autoload.php';

    // If autoloader doesn't exist in expected location, try hardcoded path
    if (!file_exists($autoloadPath)) {
        $autoloadPath = '/Applications/MAMP/htdocs/ai-projects-sk00p/sk00p-root-tools/vendor/autoload.php';
    }

    require_once $autoloadPath;

    // Load environment variables
    $envPath = dirname($autoloadPath); // /Applications/MAMP/htdocs/ai-projects-sk00p/sk00p-root-tools/vendor
    $envPath = dirname($envPath); // /Applications/MAMP/htdocs/ai-projects-sk00p/sk00p-root-tools

    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    Sk00p\Session::start();
}