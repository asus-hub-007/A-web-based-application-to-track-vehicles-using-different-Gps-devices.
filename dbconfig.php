<?php

//creating power domain

$host="localhost";//Host name
// todo - we need to segregate env files 
$username="root"; //Mysql username
$password=""; //Mysql password
$db_name="trackeasy_dev"; //Db name
 


global $conn;
$conn = new mysqli($host, $username, $password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} else {
  //echo "db connected \n";
}  

 
 
?>
