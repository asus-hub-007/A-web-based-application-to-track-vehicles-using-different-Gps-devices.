<?php
 //creating power domain
 include_once('./dbconfig.php');
 $tbl_name="registration"; //Table Name


 //Connect to server and select database.
 //mysql_connect("$host", "$username", "$password") or die("cannot connect the database");
 //mysql_select_db("$db_name")or dies("cannot select DB");

 //username and password sent from form
 $myusername = $_POST["uid"];
 $mypassword = $_POST["pwd"];
 

 //To protect MySQL injection
 $myusername = stripslashes($myusername);
 $mypassword = stripslashes($mypassword);
 $myusername = mysqli_real_escape_string($conn, $myusername);
 $mypassword = mysqli_real_escape_string($conn, $mypassword);
 $mypassword = md5($mypassword);
 $active = 'ACTIVE';
 $sql = "SELECT * FROM $tbl_name WHERE email='$myusername' and password='$mypassword' and status='$active'";
 //$result=mysql_query($sql);
 $result=$conn->query($sql);

 //Mysql_num_row is counting table row
 //$count = mysql_num_rows($result);
 $count = $result->num_rows; 

 //if result matched on myusername and mypassword - table row must be 1 row
 if($count==1) {
   //$row = mysql_fetch_assoc($result);
   $row = $result->fetch_assoc();
   $buystat = $row["buystat"];
   $remember_flag = $row["remember_me"];
   
   session_start();
   if($_POST["remember"] == $row["remember_me"]) {

	   
				setcookie ("member_login",$_POST["uid"],time()+ (10 * 365 * 24 * 60 * 60));
				setcookie ("member_password",$_POST["pwd"],time()+ (10 * 365 * 24 * 60 * 60));
			} else {
				
				if(isset($_COOKIE["member_login"])) {
					setcookie ("member_login","");
				}
				if(isset($_COOKIE["member_password"])) {
					setcookie ("member_password","");
				}
			}
   $_SESSION["suname"]=$myusername;
   if(isset($_SESSION["suname"])){
     header("location:homeroute.php");
   }
   else {
     echo "session not set";
   }
 }
 else {
    header("location:newuser_relog.php");
 }
?>
