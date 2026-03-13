<?php
session_start();
require_once __DIR__ . '/../config.php';

if (empty($_SESSION[ADMIN_SESSION_KEY])) {
    header('Location: login.php');
    exit;
}