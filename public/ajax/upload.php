<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');

// src/upload.php
$projectRoot = __DIR__ . '/../../'; // move from public/ajax to project root
require_once $projectRoot . 'require_tools.php';

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Connect to the database
$conn = new mysqli(DB_CONFIG['host'], DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['database']);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Create S3 client
$s3 = new S3Client([
    'version'     => 'latest',
    'region'      => S3_CONFIG['region'],
    'credentials' => [
        'key'    => S3_CONFIG['key'],
        'secret' => S3_CONFIG['secret'],
    ],
]);

// Authorise user
$currentUser = Sk00p\User::current();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit;
}

// Process uploaded file
if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['image']['tmp_name'];
    $name = basename($_FILES['image']['name']);
    $unique_filename = time() . '_' . $name;

    try {
        // Upload to S3
        $result = $s3->putObject([
            'Bucket'     => S3_CONFIG['bucket'],
            'Key'        => 'uploads/' . $unique_filename,
            'SourceFile' => $tmp_name,
            'ACL'        => 'public-read',
        ]);

        $s3_url = $result['ObjectURL'];

        // Save to database
        $stmt = $conn->prepare("INSERT INTO images (s3_url, filename) VALUES (?, ?)");
        $stmt->bind_param("ss", $s3_url, $unique_filename);
        $stmt->execute();
        $image_id = $conn->insert_id;
        $stmt->close();

        echo json_encode([
            'success'  => true,
            'image_id' => $image_id,
            'url'      => $s3_url
        ]);
    } catch (AwsException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Upload failed: ' . $_FILES['image']['error']]);
}

$conn->close();
