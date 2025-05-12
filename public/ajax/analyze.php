<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/php-error.log');

// src/analyze.php
$projectRoot = __DIR__ . '/../../';
require_once $projectRoot . 'require_tools.php';

require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$conn = new mysqli(DB_CONFIG['host'], DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['database']);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Get JSON POST data
$data = json_decode(file_get_contents('php://input'), true);
$image_id = $data['image_id'] ?? null;
$image_url = $data['image_url'] ?? null;

if (!$image_id || !$image_url) {
    echo json_encode(['error' => 'Missing image ID or URL']);
    exit;
}

// Call OpenAI Vision API
$client = new \GuzzleHttp\Client();

$currentUser = Sk00p\User::current();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['error' => 'Please login first']);
    exit;
}
// Cost: 1 credit per analysis
if (!Sk00p\Credits::debitForRun($currentUser->id, 1)) {
    http_response_code(402);
    echo json_encode(['error' => 'Insufficient credits']);
    exit;
}

try {
    $response = $client->post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . OPENAI_CONFIG['api_key'],
            'Content-Type'  => 'application/json',
        ],
        'json'    => [
            'model'      => 'gpt-4o',
            'messages'   => [
                [
                    'role'    => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Analyze this image and provide a structured response with the following: 
                                     1. A detailed description (100-150 words) 
                                     2. Content tags (objects, people, activities) 
                                     3. Style tags (artistic style, composition) 
                                     4. Technical tags (image quality, lighting)
                                     Format as JSON with keys: "description", "content_tags", "style_tags", "technical_tags"'
                        ],
                        [
                            'type'      => 'image_url',
                            'image_url' => ['url' => $image_url]
                        ]
                    ]
                ]
            ],
            'max_tokens' => 1000
        ]
    ]);

    $result = json_decode($response->getBody(), true);
    $content = $result['choices'][0]['message']['content'];

    // Extract JSON from response (might be embedded in markdown)
    preg_match('/\{.*\}/s', $content, $matches);
    $analysis_json = json_decode($matches[0], true);

    if (!$analysis_json) {
        throw new Exception("Could not parse JSON from OpenAI response");
    }

    // Update the images table with description and raw analysis
    $stmt = $conn->prepare("UPDATE images SET description = ?, raw_analysis = ? WHERE id = ?");
    $raw_json = json_encode($analysis_json);
    $stmt->bind_param("ssi", $analysis_json['description'], $raw_json, $image_id);
    $stmt->execute();
    $stmt->close();

    // Insert tags into image_tags table
    $tag_types = [
        'content_tags'   => 'content',
        'style_tags'     => 'style',
        'technical_tags' => 'technical'
    ];

    $stmt = $conn->prepare("INSERT IGNORE INTO image_tags (image_id, tag, tag_type) VALUES (?, ?, ?)");

    foreach ($tag_types as $tag_array => $tag_type) {
        if (isset($analysis_json[$tag_array]) && is_array($analysis_json[$tag_array])) {
            foreach ($analysis_json[$tag_array] as $tag) {
                $stmt->bind_param("iss", $image_id, $tag, $tag_type);
                $stmt->execute();
            }
        }
    }
    $stmt->close();

    // Return the analysis to the frontend
    echo json_encode([
        'success'  => true,
        'analysis' => $analysis_json
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
