<?php
// ajax/library_data.php – returns paginated thumbnails + tag stats for the current user

declare(strict_types=1);

require_once __DIR__ . '/../../require_tools.php';
require_once __DIR__ . '/../../src/config.php';

header('Content-Type: application/json');

$user = Sk00p\User::current();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authorised']);
    exit;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = max(1, min(48, (int) ($_GET['perPage'] ?? 24)));
$offset = ($page - 1) * $perPage;
$tag = trim($_GET['tag'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$mysqli = new mysqli(DB_CONFIG['host'], DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['database']);
if ($mysqli->connect_error) {
    echo json_encode(['error' => 'DB connect error']);
    exit;
}

// ---------- total images & top tags ------------
$tagFilterSql = $tag ? 'AND it.tag = ?' : '';
$searchFilterSql = $search ? 'AND it.tag LIKE ?' : '';

// total count
$totalStmtSql = "SELECT COUNT(DISTINCT i.id) AS cnt
                 FROM images i
                 LEFT JOIN image_tags it ON it.image_id = i.id
                 WHERE i.user_id = ? $tagFilterSql $searchFilterSql";
$totalStmt = $mysqli->prepare($totalStmtSql);
$params = [$user->id];
$types = 'i';
if ($tag) {
    $types .= 's';
    $params[] = $tag;
}
if ($search) {
    $types .= 's';
    $params[] = "%$search%";
}
$totalStmt->bind_param($types, ...$params);
$totalStmt->execute();
$totalRes = $totalStmt->get_result()->fetch_assoc();
$total = (int) $totalRes['cnt'];
$totalPages = max(1, (int) ceil($total / $perPage));
$totalStmt->close();

// sorting
$orderSql = match ($sort) {
    'oldest'   => 'i.upload_date ASC',
    'filename' => 'i.filename ASC',
    'tagcount' => 'tag_cnt DESC',
    default    => 'i.upload_date DESC', // newest
};

// data query
$dataSql = "SELECT i.id, i.thumb_url, i.s3_url, i.filename,
                   GROUP_CONCAT(it.tag SEPARATOR ',') AS tags,
                   COUNT(it.tag) AS tag_cnt
            FROM images i
            LEFT JOIN image_tags it ON it.image_id = i.id
            WHERE i.user_id = ? $tagFilterSql $searchFilterSql
            GROUP BY i.id
            ORDER BY $orderSql
            LIMIT ? OFFSET ?";
$dataStmt = $mysqli->prepare($dataSql);
$params = [$user->id];
$types = 'i';
if ($tag) {
    $types .= 's';
    $params[] = $tag;
}
if ($search) {
    $types .= 's';
    $params[] = "%$search%";
}
$types .= 'ii';
$params[] = $perPage;
$params[] = $offset;
$dataStmt->bind_param($types, ...$params);
$dataStmt->execute();
$result = $dataStmt->get_result();
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = [
        'id'    => (int) $row['id'],
        'thumb' => $row['thumb_url'],
        'tags'  => $row['tags'] ? explode(',', $row['tags']) : [],
    ];
}
$dataStmt->close();

// top tags
$topSql = "SELECT it.tag, COUNT(*) cnt
           FROM image_tags it
           JOIN images i ON i.id = it.image_id
           WHERE i.user_id = ?
           GROUP BY it.tag
           ORDER BY cnt DESC
           LIMIT 20";
$topStmt = $mysqli->prepare($topSql);
$topStmt->bind_param('i', $user->id);
$topStmt->execute();
$topRes = $topStmt->get_result();
$topTags = [];
while ($row = $topRes->fetch_assoc()) {
    $topTags[] = $row['tag'];
}
$topStmt->close();

$mysqli->close();

echo json_encode([
    'images'     => $images,
    'totalPages' => $totalPages,
    'page'       => $page,
    'topTags'    => $topTags,
]);