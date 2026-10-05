<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$subjectId = isset($_GET['subject_id']) && $_GET['subject_id'] !== 'all' ? $_GET['subject_id'] : null;
$assignments = getAssignments($subjectId);
echo json_encode($assignments);
?>