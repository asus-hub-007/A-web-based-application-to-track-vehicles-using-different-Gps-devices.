<?php
  session_start();
  $_SESSION["sbuyflg"]=0;
  if(isset($_SESSION["suname"])){
    $puname=$_SESSION["suname"];
    //Fetching First Name and Last Name
    include_once('./dbconfig.php');
    $reg_tbl="registration"; //Table Name
    $veh_tbl="vehicles";//Table Name


    //Connect to server and select database.
    //mysql_connect("$host", "$username", "$password") or die("cannot connect the database");
    //mysql_select_db("$db_name")or dies("cannot select DB");
	 if($puname == "visitor@h99.com"){
		  header("location:activeDemo.php");
	 }else{
		 
		//check if devices bought
			$sql = "SELECT * FROM $reg_tbl WHERE email='$puname'";
			//$result=mysql_query($sql);
			//$count = mysql_num_rows($result);
			$result=$conn->query($sql);
			$count = $result->num_rows;
                        //console.log("The count for samy is $count");
			if($count==1) {
			  //$row = mysql_fetch_assoc($result);
			  $row = $result->fetch_assoc();
			  $buystat = $row["buystat"];
			}
			else {$buystat = 0;}
		  
			//check if vehicles added
			$vsql = "SELECT * FROM $veh_tbl WHERE email='$puname'";
			//$vresult = mysql_query($vsql);
			//$vcount = mysql_num_rows($vresult);
			$vresult = $conn->query($vsql);
			$vcount = $vresult->num_rows;
			if($vcount > 0 ) {
			  $vehcount = $vcount;
			}
			else {$vehcount = 0;}

			//check if devices and vehicles are mapped
			$msql = "SELECT mapdto FROM $veh_tbl WHERE email='$puname'";
			//$mresult = mysql_query($msql);
			//$mcount = mysql_num_rows($mresult);
			$mresult = $conn->query($msql);
			$mcount = $mresult->num_rows;
			$mpd_cnt = 0;
			if($mcount > 0 ){
			  for ($cnti = 0; $cnti < $mcount; $cnti++){
				 //$mpd_row = mysql_fetch_array($mresult);
				 $mpd_row = $mresult->fetch_array();
				 if($mpd_row["mapdto"]){
				   $mpd_cnt++;
				 }
			  }
			}

			//Find what kind of model is bought so that accordingly the phpgmap can be routed
			$EMODEL = "DEFAULT";
			$fkimei = "860194030618888";
			$sqlmod = "Select devreq.epaytype from devreq INNER JOIN devid on devreq.epayid = devid.epayid where devreq.email = '$puname'  ";
			//$resmod = mysql_query($sqlmod);
			$resmod = $conn->query($sqlmod);
			if($resmod){
			  //$rowtypemod = mysql_fetch_row($resmod);
			  $rowtypemod = $resmod->fetch_row();
			  $EMODEL = $rowtypemod[0];
			  //echo "<br> <br>anything here";
			}
			else {//echo "<br> <br>nothing here";
			}

			//ROUTE case
			//if device is bought??
			$allfine = 0;
			if ($buystat > 0) {
			  $_SESSION["sdevcnt"] = $buystat;
			  //if vehicles added -check how many else say needs to add
			  if($vehcount > 0){
				$_SESSION["svehcnt"] = $vehcount;
				//if vehicles available but not mapped - check if mapped 
				if($mpd_cnt > 0){
				  $_SESSION["smpdcnt"] = $mpd_cnt;
				  $allfine = 1;
				}
				else {$_SESSION["smpdcnt"] = 0 ;}
			  }
			  else {$_SESSION["svehcnt"] = 0;}
			}
			else {
			  $_SESSION["sdevcnt"] = $buystat;
			  $_SESSION["smpdcnt"] = 0 ;
			  $_SESSION["svehcnt"] = 0;
			}


			if($buystat == 0 ){
			  header("location:howitworksnew.php");
			}
			else if($allfine == 1){
				if (strcmp($puname, "nsgroups_c1@gmail.com") == 0 or strcmp($puname, "nsgroups_c2@gmail.com") == 0 ){
					header("location:phpgmap_nscont.php");
				}elseif(strcmp($puname, "atldemo@hyway99.com") == 0){
				   	header("location:phpgmap_atlanta.php"); 
				}
				elseif (strcmp($EMODEL, "COLIMMO") == 0 ){
					header("location:phpgmap_col.php");
				}
				else {
					header("location:phpgmap.php");
				}
			}
			else {
			  header("location:howitworks.php");
			} 
	   }
		

  }
  else{
   header("location:pologout.php");
  }



 
?>
