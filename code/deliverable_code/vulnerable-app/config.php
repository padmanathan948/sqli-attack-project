<?php
// Database connection settings for the VULNERABLE version.
$db_host = '127.0.0.1';
$db_user = 'miniapp';
$db_pass = 'miniapp_pw';
$db_name = 'miniapp';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
