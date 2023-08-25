<?php
  session_start();
  //to get the email ID and other details
  if(isset($_SESSION["suname"])){
    $loc_puname = $_SESSION["suname"];
    $_SESSION["sdispid"] = $_GET["vehid"];
    $_SESSION["salldisp"] = 0;
    if (strcmp($loc_puname, "nsgroups_c1@gmail.com") == 0 or strcmp($loc_puname, "nsgroups_c2@gmail.com") == 0 ){
	    header("location:phpgmap_nscont.php");
    }elseif(strcmp($loc_puname, "visitor@h99.com") == 0){
        
        header("location:activeDemo.php");
    }elseif(strcmp($loc_puname, "atldemo@hyway99.com") == 0){
        
        header("location:phpgmap_atlanta.php");
    }
    else {
	    header("location:phpgmap.php");
    }
  }  
 else {
  //echo "Record not created";
  header("location:pologout.php");
 }

?>
