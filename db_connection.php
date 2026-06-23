<?php
// DATABASE CONNECTION CONFIGURATION FILE

$host = "localhost"; // Database server hostname

$user = "root"; //username

$pass = ""; //MySQL password 

$db = "ca"; //Database name

$conn = mysqli_connect($host, $user, $pass, $db); // Establish connection to MySQL database using mysqli_connect()

// ERROR HANDLING: Check if connection was successful
// If connection fails, mysqli_connect() returns false
if (!$conn) {
   
    die("Connection failed: " . mysqli_connect_error()); // mysqli_connect_error() provides details about why the connection failed
}

?>