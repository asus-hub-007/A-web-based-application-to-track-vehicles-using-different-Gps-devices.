<?php
  session_start();
  //to get the email ID and other details
  if(isset($_SESSION["suname"])){
    $loc_puname = $_SESSION["suname"];
    $_SESSION["sdispid"] = $_GET["vehid"];
    $_SESSION["salldisp"] = 1;
    header("location:activeDemo.php");

  }  
 else {
  //echo "Record not created";
  header("location:pologout.php");
 }

?>
