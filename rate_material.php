<?php
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materialId = $_POST['material_id'] ?? 0;
    $rating = intval($_POST['rating'] ?? 0);
    
    if ($rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("UPDATE materials SET rating_total = rating_total + ?, rating_count = rating_count + 1 WHERE id = ?");
        $stmt->execute([$rating, $materialId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    }
}
?>