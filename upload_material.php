<?php
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $subjectId = $_POST['subject_id'] ?? '';
    
    if (empty($title) || empty($subjectId) || !isset($_FILES['file'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }
    
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['file']['name']));
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO materials (title, description, file_path, subject_id, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $targetPath, $subjectId, $_SESSION['user_id']]);
        
        // Notify all students in that department (simplified)
        $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) SELECT id, ? FROM users WHERE role = 'student'");
        $notifyStmt->execute(["New material uploaded: " . $title]);
        
        echo json_encode(['success' => true, 'message' => 'File uploaded successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
    }
}
?>