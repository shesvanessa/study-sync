<?php
require_once __DIR__ . '/../config/database.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function isCourseRep() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'course_rep';
}

function requireCourseRep() {
    requireLogin();
    if (!isCourseRep()) {
        die('Access denied. Course representative privileges required.');
    }
}

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['department'] = $user['department'];
        return true;
    }
    return false;
}

function register($fullname, $email, $password, $department, $semester) {
    global $pdo;
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password, department, semester) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $email, $hashedPassword, $department, $semester]);
        return $pdo->lastInsertId();
    } catch(PDOException $e) {
        return false;
    }
}

function addNotification($userId, $message) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    return $stmt->execute([$userId, $message]);
}
?>