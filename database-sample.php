<?php

$host = 'localhost';
$db = 'your_database_name';
$user = 'your_username';
$pass = 'your_password';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

?>