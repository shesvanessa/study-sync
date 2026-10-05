<?php
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = $_POST['subject_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    
    if ($subjectId && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO discussions (subject_id, user_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$subjectId, $_SESSION['user_id'], $message]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
}
?>