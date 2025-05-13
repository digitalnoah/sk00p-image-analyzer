<?php
// ajax/export.php – download CSV or JSON of filtered library
require_once __DIR__ . '/../../require_tools.php';
require_once __DIR__ . '/../../src/config.php';

$user = Sk00p\User::current();
if (!$user) {
    http_response_code(401);
    exit('Not authorised');
}

$format = ($_GET['format'] ?? 'csv') === 'json' ? 'json' : 'csv';
$tag = trim($_GET['tag'] ?? '');
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$mysqli = new mysqli(DB_CONFIG['host'], DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['database']);

$tagFilterSql = $tag ? 'AND it.tag = ?' : '';
$searchFilterSql = $search ? 'AND it.tag LIKE ?' : '';

$orderSql = match ($sort) {
    'oldest'   => 'i.upload_date ASC',
    'filename' => 'i.filename ASC',
    'tagcount' => 'tag_cnt DESC',
    default    => 'i.upload_date DESC',
};

$sql = "SELECT i.id, i.filename, i.thumb_url, i.s3_url,
               GROUP_CONCAT(it.tag SEPARATOR ',') AS tags,
               COUNT(it.tag) AS tag_cnt
        FROM images i
        LEFT JOIN image_tags it ON it.image_id = i.id
        WHERE i.user_id = ? $tagFilterSql $searchFilterSql
        GROUP BY i.id
        ORDER BY $orderSql";
$stmt = $mysqli->prepare($sql);
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
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

if ($format === 'json') {
    header('Content-Type: application/json');
    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode($rows);
} else {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="tag-library.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['id', 'filename', 'thumb_url', 'orig_url', 'tags']);
    while ($row = $res->fetch_assoc()) {
        fputcsv($out, [$row['id'], $row['filename'], $row['thumb_url'], $row['s3_url'], $row['tags']]);
    }
    fclose($out);
}
$stmt->close();
$mysqli->close();