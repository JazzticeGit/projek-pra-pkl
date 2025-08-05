<?php
session_start();
include '../koneksi.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$user_id = $_SESSION['user_id'] ?? 1;
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_ids']) || !is_array($input['order_ids'])) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$order_ids = array_map('intval', $input['order_ids']);
$placeholders = str_repeat('?,', count($order_ids) - 1) . '?';

// Cek jika ada update dalam 1 menit terakhir
$query = "
    SELECT id, status, updated_at 
    FROM pemesanan 
    WHERE user_id = ? 
    AND id IN ($placeholders)
    AND updated_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
";

$stmt = mysqli_prepare($koneksi, $query);
$params = array_merge([$user_id], $order_ids);
$types = str_repeat('i', count($params));

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$updates = [];
while ($row = mysqli_fetch_assoc($result)) {
    $updates[] = [
        'id' => $row['id'],
        'status' => $row['status'],
        'updated_at' => $row['updated_at']
    ];
}

echo json_encode([
    'updates' => $updates,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>