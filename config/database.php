<?php

$host = 'localhost';
$user = 'ture2759_turikale';
$pass = '@turikalebintang#';
$db = 'ture2759_turikaleprint';


$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection Error: " . mysqli_connect_error());
}

define('GEMINI_API_KEY', 'AIzaSyDB7P8M13WVOpp47_b3o1SndEaazXqVfRQ');
?>
