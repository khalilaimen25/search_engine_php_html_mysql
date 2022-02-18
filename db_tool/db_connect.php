<?php
$servername = "localhost";
$username = "khalil25";
$password = "baboo666";
$db = "my_search_engine";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $db);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
//echo "Connected successfully <br>";

?> 