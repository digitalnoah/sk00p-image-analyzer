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
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

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
    $name = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $uuid = time() . '_' . bin2hex(random_bytes(4));
    $orig_key = 'images/original/' . $uuid . '.' . $ext;
    $thumb_key = 'images/thumb/' . $uuid . '.webp';

    // 1) create thumbnail locally
    $driverInstance = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
    $manager = new ImageManager($driverInstance);
    $thumb = $manager->read($tmp_name)
        ->resize(1000, 1000, function ($c) {
            $c->aspectRatio();
        });
    $thumbTemp = sys_get_temp_dir() . '/' . $uuid . '.webp';
    $thumb->save($thumbTemp, 85, 'webp');

    try {
        // Upload original
        $resultOrig = $s3->putObject([
            'Bucket'     => S3_CONFIG['bucket'],
            'Key'        => $orig_key,
            'SourceFile' => $tmp_name,
            'ACL'        => 'public-read',
        ]);

        // Upload thumbnail
        $resultThumb = $s3->putObject([
            'Bucket'      => S3_CONFIG['bucket'],
            'Key'         => $thumb_key,
            'SourceFile'  => $thumbTemp,
            'ACL'         => 'public-read',
            'ContentType' => 'image/webp'
        ]);

        $s3_url = $resultOrig['ObjectURL'];
        $thumb_url = $resultThumb['ObjectURL'];

        $filename = $uuid . '.' . $ext;

        // Save to database (ensure table has thumb_url column!)
        $stmt = $conn->prepare("INSERT INTO images (user_id, s3_url, thumb_url, filename) VALUES (?,?,?,?)");
        $stmt->bind_param("isss", $currentUser->id, $s3_url, $thumb_url, $filename);
        $stmt->execute();
        $image_id = $stmt->insert_id ?? $conn->insert_id;
        $stmt->close();

        echo json_encode([
            'success'  => true,
            'image_id' => $image_id,
            'url'      => $s3_url,
            'thumb'    => $thumb_url
        ]);
    } catch (Throwable $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Upload failed: ' . $_FILES['image']['error']]);
}

$conn->close();
