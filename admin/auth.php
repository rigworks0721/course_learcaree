<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION[ADMIN_SESSION])) {
    header("Location: login.php");
    exit;
}