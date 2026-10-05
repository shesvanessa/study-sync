<?php
function getSubjects() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY department, semester, name");
    return $stmt->fetchAll();
}

function getMaterials($subjectId = null) {
    global $pdo;
    if ($subjectId) {
        $stmt = $pdo->prepare("SELECT m.*, u.fullname as uploader_name, s.name as subject_name,
                               (CASE WHEN m.rating_count > 0 THEN m.rating_total / m.rating_count ELSE 0 END) as avg_rating
                               FROM materials m 
                               JOIN users u ON m.uploaded_by = u.id
                               JOIN subjects s ON m.subject_id = s.id
                               WHERE m.subject_id = ?
                               ORDER BY m.uploaded_at DESC");
        $stmt->execute([$subjectId]);
    } else {
        $stmt = $pdo->query("SELECT m.*, u.fullname as uploader_name, s.name as subject_name,
                             (CASE WHEN m.rating_count > 0 THEN m.rating_total / m.rating_count ELSE 0 END) as avg_rating
                             FROM materials m 
                             JOIN users u ON m.uploaded_by = u.id
                             JOIN subjects s ON m.subject_id = s.id
                             ORDER BY m.uploaded_at DESC LIMIT 20");
    }
    return $stmt->fetchAll();
}

function getAssignments($subjectId = null) {
    global $pdo;
    if ($subjectId) {
        $stmt = $pdo->prepare("SELECT a.*, s.name as subject_name FROM assignments a 
                               JOIN subjects s ON a.subject_id = s.id 
                               WHERE a.subject_id = ? AND a.due_date >= CURDATE()
                               ORDER BY a.due_date ASC");
        $stmt->execute([$subjectId]);
    } else {
        $stmt = $pdo->query("SELECT a.*, s.name as subject_name FROM assignments a 
                             JOIN subjects s ON a.subject_id = s.id 
                             WHERE a.due_date >= CURDATE()
                             ORDER BY a.due_date ASC LIMIT 10");
    }
    return $stmt->fetchAll();
}

function getDiscussions($subjectId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT d.*, u.fullname FROM discussions d 
                           JOIN users u ON d.user_id = u.id 
                           WHERE d.subject_id = ? 
                           ORDER BY d.created_at ASC");
    $stmt->execute([$subjectId]);
    return $stmt->fetchAll();
}

function getUnreadNotifications($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function markNotificationsRead($userId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    return $stmt->execute([$userId]);
}
?>