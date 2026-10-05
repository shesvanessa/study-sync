<?php
require_once 'includes/auth.php';
requireCourseRep();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $subjectId = $_POST['subject_id'] ?? 0;
    $dueDate = $_POST['due_date'] ?? '';
    
    $stmt = $pdo->prepare("INSERT INTO assignments (title, description, subject_id, due_date, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $subjectId, $dueDate, $_SESSION['user_id']]);
    
    // Notify students
    $notifyStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) SELECT id, ? FROM users WHERE role = 'student'");
    $notifyStmt->execute(["New assignment posted: " . $title . " (Due: " . $dueDate . ")"]);
    
    echo json_encode(['success' => true]);
}
?>