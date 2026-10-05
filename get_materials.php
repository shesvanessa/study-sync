<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$subjectId = isset($_GET['subject_id']) && $_GET['subject_id'] !== 'all' ? $_GET['subject_id'] : null;
$materials = getMaterials($subjectId);

foreach ($materials as &$material) {
    $material['avg_rating'] = number_format($material['avg_rating'], 1);
}
echo json_encode($materials);
?>