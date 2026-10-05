<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$subjectId = $_GET['subject_id'] ?? 0;
if ($subjectId) {
    $discussions = getDiscussions($subjectId);
    echo json_encode($discussions);
} else {
    echo json_encode([]);
}
?>