<?php

function base_url($path = '') {
    $base = APP_BASE_URL;
    if ($path) { $base .= '/' . ltrim($path, '/'); }
    return $base;
}

function is_logged_in() {
    return !empty($_SESSION['user']);
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . base_url('index.php?view=login'));
        exit;
    }
}

function is_admin() {
    return !empty($_SESSION['user']) && ($_SESSION['user']['is_admin'] ?? 0) == 1;
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function flash($key, $val = null) {
    if ($val !== null) {
        $_SESSION['flash'][$key] = $val;
        return;
    }
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="'.csrf_token().'">';
}
function check_csrf() {
    if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
        die('Invalid CSRF token');
    }
}

// Returns relative time string (e.g. "2 hours ago")
function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff/60) . ' minute' . (floor($diff/60)==1?'':'s') . ' ago';
    if ($diff < 86400) return floor($diff/3600) . ' hour' . (floor($diff/3600)==1?'':'s') . ' ago';
    if ($diff < 2592000) return floor($diff/86400) . ' day' . (floor($diff/86400)==1?'':'s') . ' ago';
    return floor($diff/2592000) . ' month' . (floor($diff/2592000)==1?'':'s') . ' ago';
}