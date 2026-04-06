<?php
/**
 * ============================================
 * LOGOUT (logout.php)
 * ============================================
 * Menghapus session dan redirect ke halaman login.
 */
session_start();
require_once 'config/db.php';

// Hapus semua session
$_SESSION = [];
session_destroy();

// Redirect ke login
header("Location: " . BASE_URL . "/login.php");
exit();
