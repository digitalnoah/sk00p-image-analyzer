<?php
// Shared toolbox for image analyzer

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
    $projectDir = __DIR__; // Current directory of this file
    $parentDir = dirname($projectDir); // sk00p-image-analyzer
    $rootDir = dirname($parentDir); // ai-projects-sk00p

    require_once $rootDir . '/sk00p-root-tools/vendor/autoload.php';

    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable($rootDir . '/sk00p-root-tools');
    $dotenv->load();
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    Sk00p\Session::start();
}