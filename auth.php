<?php
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$currentUser = $_SESSION['user'];
