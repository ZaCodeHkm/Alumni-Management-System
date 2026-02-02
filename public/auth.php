<?php
// auth.php - Central authentication helper

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function require_role($allowed_roles) {
    require_login();
    
    // Convert single role to array
    if (!is_array($allowed_roles)) {
        $allowed_roles = [$allowed_roles];
    }
    
    // Check if user's role is in allowed roles
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: home.php");
        exit;
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}