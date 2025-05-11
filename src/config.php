<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Define configuration based on environment
$environment = $_ENV['ENVIRONMENT'] ?? 'production';

// Database configuration
$db_config = [
    'local'      => [
        'host'     => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS']
    ],
    'production' => [
        'host'     => $_ENV['PROD_DB_HOST'] ?? 'prod_host',
        'database' => $_ENV['PROD_DB_NAME'] ?? 'prod_db',
        'username' => $_ENV['PROD_DB_USER'] ?? 'prod_user',
        'password' => $_ENV['PROD_DB_PASS'] ?? 'prod_pass'
    ]
];

// AWS S3 configuration
$s3_config = [
    'local'      => [
        'key'    => $_ENV['AWS_KEY'],
        'secret' => $_ENV['AWS_SECRET'],
        'region' => $_ENV['AWS_REGION'],
        'bucket' => $_ENV['AWS_BUCKET']
    ],
    'production' => [
        'key'    => $_ENV['PROD_AWS_KEY'] ?? $_ENV['AWS_KEY'],
        'secret' => $_ENV['PROD_AWS_SECRET'] ?? $_ENV['AWS_SECRET'],
        'region' => $_ENV['PROD_AWS_REGION'] ?? $_ENV['AWS_REGION'],
        'bucket' => $_ENV['PROD_AWS_BUCKET'] ?? $_ENV['AWS_BUCKET']
    ]
];

// OpenAI API configuration
$openai_config = [
    'local'      => [
        'api_key' => $_ENV['OPENAI_API_KEY']
    ],
    'production' => [
        'api_key' => $_ENV['PROD_OPENAI_API_KEY'] ?? $_ENV['OPENAI_API_KEY']
    ]
];

// Export configurations
define('DB_CONFIG', $db_config[$environment]);
define('S3_CONFIG', $s3_config[$environment]);
define('OPENAI_CONFIG', $openai_config[$environment]);
