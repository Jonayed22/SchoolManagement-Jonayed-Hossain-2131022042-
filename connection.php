<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "school_management_system"; 

$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>