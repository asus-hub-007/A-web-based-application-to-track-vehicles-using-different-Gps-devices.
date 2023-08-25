<?php
  session_start();
  date_default_timezone_set("Asia/Calcutta");

  $today_dt = date("Y-m-d", strtotime("today"));
  $minus1_day = date("Y-m-d", strtotime($today_dt."-1 day"));
  $minus2_day = date("Y-m-d", strtotime($today_dt."-2 day"));
  $minus3_day = date("Y-m-d", strtotime($today_dt."-3 day"));
  $minus4_day = date("Y-m-d", strtotime($today_dt."-4 day"));
  $minus5_day = date("Y-m-d", strtotime($today_dt."-5 day"));
  $minus6_day = date("Y-m-d", strtotime($today_dt."-6 day"));

 // $date_array = [$today_dt, $minus1_day, $minus2_day, $minus3_day, $minus4_day, $minus5_day, $minus6_day];
  $date_array = ["2022-12-17", "2022-12-16", "2022-12-15", "2022-12-14", "2022-12-13", "2022-12-12", "2022-12-11"];
  $date_array_display = [$today_dt, $minus1_day, $minus2_day, $minus3_day, $minus4_day, $minus5_day, $minus6_day];
  $daily_dist_tvl = array();
  $max_speed_of_day = array();
  $max_viols_of_day = array();






  $_SESSION["sbuyflg"]=0;
  if(isset($_SESSION["suname"])){
    $puname=$_SESSION["suname"];
    //Fetching First Name and Last Name

    include_once('./dbconfig.php');

    $tbl_name="mydetails"; //Table Name
    $sam_events_tbl = "sam_events";//Events Table Name

  //pull vehicle details
   $devid_tbl = "devid";//tbl name
   $devid_sql = "SELECT id, mapdas FROM $devid_tbl WHERE assigndto='$puname'";
   $devid_rslt = mysqli_query($conn,$devid_sql);
   $devid_cnt = mysqli_num_rows($devid_rslt);


   //array of nicks & mapdto
   $pmapdas_id_nick = array(array());
   $debug_array = array(array());
   $pmapdas_id_idx = 0;
   $pdevid_arr = array();
   //pmapdas_id_nick[devid, mapdas, nick];
   if($devid_cnt > 0){
     for ($row=0; $row < $devid_cnt; $row++){
        $devid_row = mysqli_fetch_assoc($devid_rslt);
        $pmapdasv = $devid_row["mapdas"];
        $pdevid = $devid_row["id"];
        $pindevidtbl = "devidtbl";

        $dev_tbl ="vehicles";
        $dev_sql ="SELECT nick, speed, mapdto FROM $dev_tbl WHERE email='$puname'";
        $dev_rslt = mysqli_query($conn,$dev_sql);
        $dev_cnt = mysqli_num_rows($dev_rslt);
       
        for ($vehrow =0; $vehrow < $dev_cnt; $vehrow++) {
           $devnick_row = mysqli_fetch_assoc($dev_rslt); 
           $pmapdto = $devnick_row["mapdto"];
           $pnick = $devnick_row["nick"]; 
		   $povspeed = $devovspeed_row["speed"];
           $pinvehtbl = "vehicletbl";
           $debug_array[$row][$vehrow]= "As-".$pmapdasv."To-".$pmapdto;
	   if (strcmp($pmapdasv, $pmapdto) == 0) {
             $pdevid_arr[$pmapdas_id_idx] = $pdevid;


             $pmapdas_id_nick[$pmapdas_id_idx][0] = $pdevid;
             $pmapdas_id_nick[$pmapdas_id_idx][1] = $pmapdasv;
             $pmapdas_id_nick[$pmapdas_id_idx][2] = $pnick;
	     $pmapdas_id_nick[$pmapdas_id_idx][3] = $povspeed;
             $pmapdas_id_idx++;
             $plastid = $pdevid;
             break; 
           }
		   echo$povspeed;
        }//for $vehrow
     }//for $row
   }
   $pidmapd_len = count($pmapdas_id_nick);

   //if sessions vehicle id is defined then show that else show the last nick
   $dispid = $plastid;
  // $_SESSION["sdispid"] = $dispid;
   
   if(isset($_SESSION["sdispid"])){
     $dispid = $_SESSION["sdispid"];
     $msg_ses = "set on session";
   }
   else {
     $_SESSION["sdispid"] = $dispid;
	// $_SESSION["salldisp"] = 0;
     $msg_ses = "set while not in session";
   }
   //echo "<br>"."<br>"."<br>"."User ID is $puname";
   //
   if ($pidmapd_len < 2 ){
     $alldisp = 0; 
   }
   elseif (isset($_SESSION["salldisp"])){
     $alldisp = $_SESSION["salldisp"];
     if($alldisp == 0){
      foreach($pmapdas_id_nick as $nick_val){
     if($nick_val[0] == $_SESSION["sdispid"]){
       $nick_to_disp = $nick_val[2];
     }
    }
    }
   }
   else {
     $alldisp = 1; 
   }


  }
  else{
   header("location:index.php");
  }
  //$devid="865879020110426";
  $devid = $dispid;


  $mapkey_sql = "SELECT MAP_KEY FROM devid WHERE id='$devid'";
   $mapkey_rslt = mysqli_query($conn,$mapkey_sql);
   $row_map_assc = mysqli_fetch_assoc($mapkey_rslt);
    $map_api_key = $row_map_assc["MAP_KEY"];



   //pull the if ignition is ON
    $alldev_cnt = count($pdevid_arr);
    $ign_sql = "SELECT Ign FROM $sam_events_tbl WHERE IMEI = '$devid'";
    $ign_sql_rslt = mysqli_query($conn,$ign_sql);
    $ign_sql_row = mysqli_fetch_assoc($ign_sql_rslt);
    $veh_ign_is = $ign_sql_row["Ign"];
    if(empty($veh_ign_is)){
      $veh_ign_is = 0;
    }

  //xml reading for displaying distance travelled, Time Spent & Avg Speed
    $xml='feeds/demo_feed.xml';
    $dom = new DOMDocument;
    $dom->Load($xml); // use if loading separate xml file
    $xpath = new DOMXPath($dom);
    //Sami commented this for taking only latest time stamp instead of putting new entry for new date
    //$xquery = "//item[id='".$did."' and time='".$timeSent."']";
    $xquery = "//item[id='".$devid."']";
    $xresult = $xpath->query($xquery);
    if(count($xresult->item(0)) > 0 ) { 
       $xdate   = $xresult->item(0)->getElementsByTagName('time')->item(0)->nodeValue;
       $xdist   = $xresult->item(0)->getElementsByTagName('distance')->item(0)->nodeValue;
       $xavgspd = $xresult->item(0)->getElementsByTagName('speed')->item(0)->nodeValue;       
       $current_latitude = $xresult->item(0)->getElementsByTagName('latitude')->item(0)->nodeValue;
       $current_longitude = $xresult->item(0)->getElementsByTagName('longitude')->item(0)->nodeValue;
       //$xtime   = $xresult->item(0)->getElementsByTagName('time')->item(0)->nodeValue;
       $xtime   = 18.5;
       $xvehname = $xresult->item(0)->getElementsByTagName('vehicle')->item(0)->nodeValue;       
    }
    else {
      $xdate = 0;
      $xdist = 0;
      $xavgspd = 0;
      $xtime = 0;
      $xvehname = "NULL";
    }

   //calculating distance travelled for the days, max_speed_of_day, max_viols_of_day
   $day_index=0;
   foreach ($date_array as &$darr_val){
      //calculating total distance travelled for the days
      $sql_dist = "SELECT SUM(DIST_TVL) FROM JTB_TEMP WHERE ST_DATE='$darr_val' AND IMEI = '$devid'";
      $res_sql_dist = mysqli_query($conn,$sql_dist);
      $res_sql_dist_cnt = mysqli_fetch_assoc($res_sql_dist);
      $daily_dist_tvl[$day_index] = (round($res_sql_dist_cnt["SUM(DIST_TVL)"],1)); 

      //extracting max speeds of the days
      $sql_mxspd = "SELECT MAX(MAXSPEED) FROM JTB_TEMP WHERE ST_DATE='$darr_val' AND IMEI = '$devid'";
      $res_sql_mxspd = mysqli_query($conn,$sql_mxspd);
      $res_sql_mxspd_cnt = mysqli_fetch_assoc($res_sql_mxspd);
      $max_speed_of_day[$day_index] = $res_sql_mxspd_cnt["MAX(MAXSPEED)"]; 

      //extracting max speeds violations of the days
      $sql_mxviols = "SELECT MAX(NUMOVRSP) FROM JTB_TEMP WHERE ST_DATE='$darr_val' AND IMEI = '$devid'";
      $res_sql_mxviols = mysqli_query($conn,$sql_mxviols);
      $res_sql_mxviols_cnt = mysqli_fetch_assoc($res_sql_mxviols);
      $max_viols_of_day[$day_index] = $res_sql_mxviols_cnt["MAX(NUMOVRSP)"]; 

      //echo "$daily_dist_tvl[$day_index]"."<br>";
      //echo "$max_speed_of_day[$day_index]"."<br>";
      //echo "$max_viols_of_day[$day_index]"."<br>";
      $day_index++;
   }

    
	
//Function for calculating distances travelld in km  ..	
  $unit = "K";
  $variable = calculate_distance_between_two_places($Lat_1, $Lon_1, $Lat_2 , $Lon_2 , $unit);
  function calculate_distance_between_two_places($Lat_1, $Lon_1, $Lat_2 , $Lon_2 , $unit){
		$theta = $Lon_1- $Lon_2 ;
		$dist = sin(deg2rad($Lat_1)) * sin(deg2rad($Lat_2 )) +  cos(deg2rad($Lat_1)) * cos(deg2rad($Lat_2 )) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);
		if ($unit == "K"){
			return ($miles * 1.609344);
		}                     
		   }
		 $Total_distances= (round($variable,1));
		// echo$Total_distances;
	
//Function for calculating time spend ..	
      $spend_time= gettotaltriptime($TIME_END,$time_start);
		 
function gettotaltriptime($TIME_END, $time_start){
$to_time = strtotime($TIME_END);
           $from_time = strtotime($time_start);
          // $Time_spend = round(abs($to_time - $from_time) / 60,2);
           // $Time_hr = round($Time_spend * 0.0166667,1) ." Hours"; 
              $Time_spend = ($to_time - $from_time);           
              $hours = floor($Time_spend/ 3600);
              $minutes = floor(($Time_spend / 60) % 60);
              $seconds = $Time_spend % 60;
              $Time_s = "$hours:$minutes:$seconds";
              $Time_hr = $Time_s;

               return $Time_hr;
		   }

function trip_time_calc($the_stdate, $the_dtdate){
  $split_date = explode(" ", $the_dtdate);
  if ($split_date[0] == "DATA"){
    return "DATA YET NOT RECEIVED";
  }
  else {
    $st_dt_time = new DateTime($the_stdate); 
    $dt_dt_time = new DateTime($the_dtdate); 
    $spend_time_interval = date_diff($st_dt_time, $dt_dt_time);
    return $spend_time_interval->format('%h:%i:%s');
  }
}

// Function for calculating location place based on lat. long.
	   $start_point= getaddress($lat,$long);
         function getaddress($lat,$long)
       {
		     $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($long).'&sensor=false';
		     $json = @file_get_contents($url);
		     $data=json_decode($json);
		     $status = $data->status;
		     if($status=="OK"){
		     
       				return $data->results[0]->formatted_address;
				     }else{
				      return false;
				      }
		 }
		 	 		 

$sql_sos = "SELECT Bnt_Status,Status_color FROM Emergency where Email_id = '$puname'";
   $result_sos = mysqli_query($conn,$sql_sos);
   $count_sos = mysqli_num_rows($result_sos);
 
   if ($count_sos ==1){
     $row_s = mysqli_fetch_assoc($result_sos);
     $pbnt_status = $row_s["Bnt_Status"]; 
     $pbnt_color_status = $row_s["Status_color"];   
        }	

   //showing or not showing certain fields
   //
   $showthis = 0;
   if ((strcmp($puname, "visitor@h99.com")==0) ){
     $showthis = 1;
   }
   $vehtype = "Live Track";
   


	
   
//getting limitted speed  from vehicles  table in DB...
	 $sqlsld = "Select vehicles.speed from vehicles INNER JOIN devid on 
   vehicles.email =  devid.assigndto and vehicles.mapdto = devid.mapdas where devid.id = '$devid'";
   $resultsld = mysqli_query($conn,$sqlsld);
   if($resultsld){
       while($rowsld = mysqli_fetch_array($resultsld)){      
        $speed_ltd=$rowsld[0];  
        //echo$valuep;            
       }
       }
                                         


$message = 'no records found for this day';				   
      
       ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>TRACKEASY.COM - Car Tracker System Car Device Vehicle Tracker GPS Location Fleet Management Auto Tracking Phone GPS Truck Automobile Tracking Cheap Tracking Car Insurance</title>
<meta name="description" content="Track your car with our GPS tracking device. How about free vehicle tracking with car insurance facility Free vehicle tracking report ">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="vehicle tracking system with GPS devices and car insurance facility in India">    <!-- Loading Bootstrap -->
<meta http-equiv="refresh" content="300">
    <link href="./dist/css/vendor/bootstrap.min.css" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="./dist/css/flat-ui.css" rel="stylesheet">
    <!--link href="docs/assets/css_mobile/demo.css" rel="stylesheet"-->
    <link rel="shortcut icon" href="./dist/img/favicon.ico">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]-->
      <script src="./dist/js/vendor/html5shiv.js"></script>
      <script src="./dist/js/vendor/respond.min.js"></script>




<!--Dynamic add-->
      <script src="./js/form.js" type="text/javascript"></script>
    <!--[endif]-->
<!-- javascript for internal use-->
       
    <style>
      #map_canvas {
        width: 100%;
        height: 700px;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo "$map_api_key";?>"></script>
    <script src="./js/mapDemoLayerv3.js"></script>
    <script src="./js/mapDemoLayerv4.js"></script>
    <script src="./js/label.js"></script>
    <script>
       var udevid;
       var uign;
       var udevs_arr;
       udevid      = '<?php echo "$devid";?>';
       uign        = '<?php echo "$veh_ign_is";?>'; 
       udevs_arr   = <?php echo json_encode($pdevid_arr) ;?>; 
       ucnt        = '<?php echo "$alldev_cnt";?>';
       ualldisp    = '<?php echo "$alldisp";?>';
    </script>

  </head>
  <!--body onload="load(<?php echo "$udevid";?>)"-->
  <body onload="load4(udevid, uign, udevs_arr, ucnt, ualldisp)">
  <!--body-->
    <style>
      body {
        padding-bottom: 0px;
      }
    </style>
    <!-- Static navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
          </button>
          <a class="navbar-brand" href="homeroute.php">TRACKEASY</a>
        </div>
        <div class="navbar-collapse collapse">
        <ul class="nav navbar-nav">
        <li><a href="locate_allveh_demo.php">Track ALL</a></li>
        </ul>
         <!-- <ul class="nav navbar-nav">
            <?php 
               if ((strcmp($puname, "shaiksamy@gmail.com") == 0) or (strcmp($puname, "dominic@timetravelodysseys.com")==0) or (strcmp($puname, "zingridersmail@gmail.com")==0) or (strcmp($puname, "nmani96@gmail.com")==0) or (strcmp($puname, "raja@jaigraphics.co.in")==0) ){
            ?>
            <li><a href="locate_allveh.php">View ALL Vehicles</a></li>
            <li><a href="phpgmap_geof.php">View On GeoFence</a></li>


			<?php 
               } elseif ((strcmp($puname, "admin@vikastradelinks.in") == 0)or (strcmp($puname, "shilpa@rtt.co.in")==0)or (strcmp($puname, "vreports@beecabs.in")==0)or (strcmp($puname, "beecabsmail@gmail.com")==0) or (strcmp($puname, "jaijmktg@gmail.com")==0)){
            ?>
            <li><a href="locate_allveh.php">View ALL Vehicles</a></li>
            


            <?php   }
               else {
            ?>
            <li><a href="howitworks.php">How it works</a></li>
            <li><a href="renewalinsurance.php">Renew Insurance</a></li>
            <li><a href="pur_deliv.php">Buy Officer&#153</a></li>

            <?php   }

            ?>
           
            <li><a href="myveh_details.php">My Vehicles</a></li>
			<li><a data-toggle="modal" data-target="#breakdownEntry" data-whatever="sumanth" href="#">Breakdown</a></li>
            <?php 
               if ((strcmp($puname, "shaiksamy@gmail.com") == 0) or (strcmp($puname, "dominic@timetravelodysseys.com")==0)  or (strcmp($puname, "zingridersmail@gmail.com")==0) or (strcmp($puname, "nmani96@gmail.com")==0) or (strcmp($puname, "admin@vikastradelinks.in")==0) or (strcmp($puname, "shilpa@rtt.co.in")==0)or (strcmp($puname, "vreports@beecabs.in")==0)or (strcmp($puname, "beecabsmail@gmail.com")==0)){
            ?>
                <li><a href="HistoryTrack.php">History Tracks</a></li>
            <?php   }

            ?>
       

          </ul>-->

          <form class="navbar-form navbar-right" role="form" action="pologout.php">
            <button type="submit" class="btn btn-danger" >Logout</button>
          </form>
          <p class="navbar-text navbar-right">Hello <a class="navbar-link" href="#"><font color="#1abc9c">Visitor</font></a>!!</p>
        </div><!--/.nav-collapse -->
      </div>
    </div>


    <div class="container">
      <div class="demo-type-example">
        <br></br>
        <p></p>
<?php 
      if ($showthis == 0 ){
?>
        <div class="col-xs-6">
      	        <div class="demo-type-example">
                  <blockquote class="pull-right">
                    <p>The image we carry is lot influenced by cars we drive</p>
                    <small>Mark Idar</small>
                  </blockquote>
      		</div>
        </div>
        <div class="col-xs-6" id="listveh">
<?php   }
      else {
?>
        <div class="col-xs-12" id="listveh">
<?php   }
?>
         <!---->
        </div>


     </div>


</div><!--container-->

<?php 


      if (( (strcmp($puname, "shaiksamy@gmail.com") == 0) or (strcmp($puname, "shadabhussain16051995@gmail.com") == 0) or (strcmp($puname, "dominic@timetravelodysseys.com")==0)  or (strcmp($puname, "zingridersmail@gmail.com")==0)or (strcmp($puname, "shilpa@rtt.co.in")==0)) and ($alldisp == 0) ){
?>
        <div class="col-lg-6 col-md-6 col-lg-offset-3 col-md-offset-3" style="text-align: -webkit-center;">
        <h4>Immobilize</h4>
   <p>(Current Vehicle Status - <a><?php echo $immo_message;?></a>)</p>
        <form class="form-inline" role="form" method="POST" action="immo_proc.php">
    <div class="form-group">
      <!--input type="hidden" name="sosValue" class="form-control" id="sos-pressed" value="<?php echo$current_latitude;?>">
      <input type="hidden" name="sosV" class="form-control" id="sos" value="<?php echo$current_longitude;?>"-->

    </div>
    
   <input type="submit" class="btn <?php echo$pimmo_but_col ?>" name="IMMO_STATUS" value="<?php echo$pimmo_but_cmd ?>"><br> 
   

   </form>
   <br>
   <!--<button type="submit" class="btn btn-success" onclick="myFunction()">SOS</button>-->
          </div> 
      
<?php   }
?>




<?php 
      if ($showthis == 0 ){
?>

        <div class="col-lg-6 col-md-6 col-lg-offset-3 col-md-offset-3" style="text-align: -webkit-center;">
        <h4>Emergency Help </h4>
        <?php 
                $current_latitude;
                $current_longitude;
        ?>
        <form class="form-inline" role="form" method="POST" action="sos_process.php">
    <div class="form-group">
      <input type="hidden" name="sosValue" class="form-control" id="sos-pressed" value="<?php echo$current_latitude;?>">
      <input type="hidden" name="sosV" class="form-control" id="sos" value="<?php echo$current_longitude;?>">

    </div>
    
   <input type="submit" class="btn <?php echo$pbnt_color_status ?>" name="SOS_status" value="<?php echo$pbnt_status ?>"><br> ( Please use this button judiciously )
   

   </form>
   <!--<button type="submit" class="btn btn-success" onclick="myFunction()">SOS</button>-->
          </div> 
<?php   }
?>
      



<div class="container">
      <!--div class="demo-type-example"-->
	<div class="row">
        <div class="col-xs-10" id="indi_veh-rem">
	 
        </div>

	<div class="col-xs-2">
           <!--<a href="#">ATTEND
          
	   <img src="./images/panic_but.png" alt="blackdot" style="width:80px;height:80px;" align="middle" >
          
           </a>-->

        </div> 
     </div>
</div><!--container-->

<div class="container">
<!--Div for mapping-->
      <div class="row">
        <div class="col-lg-12">
 <br>		  
            <h4>Fleet's Last Known Locations</h4>
            <!--img src="../images/blackdot_retina.png" alt="blackdot" data-src="holder.js/50%x50%" -->
            <!--div class="row"-->
            <!--div class ="row"-->
            <!--/div-->
	   <div id="map_canvas"></div>

            <div class ="col-ls-4">
              <img src="./images/poldot_retina.png" alt="blackdot" style="width:17px;height:17px;" >
              <a>In Motion</a>
              <img src="./images/reddot_retina.png" alt="blackdot" style="width:17px;height:17px;" >
              <a>Ignition OFF</a>
              <img src="./images/orangedot_retina.png" alt="blackdot" style="width:17px;height:17px;" >
              <a>Battery Removed</a>
              <img src="./images/blackdot_retina.png" alt="blackdot" style="width:17px;height:17px;" >
              <a>Weak Network</a>
            </div>

        </div> 
      </div>


<!--Div for commute metrics-->
      <br>
 


<?php
   if($alldisp == 0) {
?>
    <div class="row">
    <div class="col-lg-10">
	<div class="container">
      <h4>Your Reports</h4>
      <ul class="nav nav-tabs">
		 <li class="active"><a data-toggle="tab" href="#home">Movement</a></li>
		 <li><a data-toggle="tab" href="#menu2">MaxSpeed</a></li>
		 <li><a data-toggle="tab" href="#menu1">OverSpeeding</a></li>
    
       </ul>

     <div class="tab-content">
    <div id="home" class="tab-pane fade in active">
	<div class="col-lg-4">
       <h4>Movement</h4>
	   <svg width="16px" height="12px"><rect width="16" height="12" style="fill:#1abc9c;"> <span style="font-size:17px; margin-left:2%;">Distances Traveled in km</span></svg><br> 
	   <svg width="16px" height="12px"><rect width="16" height="12" style="fill:#ff9900;"> <span style="font-size:17px; margin-left:2%;">Distances Traveled in km <span style = "color:#ff9900;">(Today)</span></span></svg>  
	   </div>
	   
	   <div class="col-lg-6">
     <div id="container"  style="max-width:600px; height: 400px; margin: 0 auto; "></div>

    <table id="datatable" style="display:none;">
    <thead>
        <tr>
	      <!--<th disabled="disabled" >Distances Traveled in km</th>
           <th>Distances Traveled in km (Today)</th>-->
        </tr>
    </thead>
    <tbody>
         <tr>
           <th>Today</th>           
            <!-- <?php echo "<th>".$current_dt."</th>";?> -->                       
            <?php echo "<td>".$daily_dist_tvl[0]."</td>";?>
                
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[1]."</th>";?>
            <?php echo "<td>".$daily_dist_tvl[1]."</td>";?>
            
        </tr>
       
        <tr>
             <?php echo "<th>".$date_array_display[2]."</th>";?>
            <?php echo "<td>".$daily_dist_tvl[2]."</td>";?>
		</tr>
        <tr>
            <?php echo "<th>".$date_array_display[3]."</th>";?>
             <?php echo "<td>".$daily_dist_tvl[3]."</td>";?>           
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[4]."</th>";?>
             <?php echo "<td>".$daily_dist_tvl[4]."</td>";?>           
        </tr>
		 <tr>
           <?php echo "<th>".$date_array_display[5]."</th>";?>
                     <?php echo "<td>".$daily_dist_tvl[5]."</td>";?>
           
        </tr>
		 <tr>
            <?php echo "<th>".$date_array_display[6]."</th>";?>
            <?php echo "<td>".$daily_dist_tvl[6]."</td>";?>
           
        </tr>
    </tbody>
    </table><!--table-->
    </div>  
    </div><!--tab-->
    <div id="menu1" class="tab-pane fade">
     	<div class="col-lg-4">
      <h4>OverSpeeding</h4>
	      <svg width="16px" height="12px"><rect width="16" height="12" style="fill:#ff9900;"> <span style="font-size:17px; margin-left:2%;">Over Speeding <span style = "color:#ff9900;">(limited speed   <?php echo " $speed_ltd ";?>)</span></span></svg><br>
	   
	   
	   </div>
	   <div class="col-lg-6">
       <div id="container1"  style="max-width:600px; height: 400px; margin: 0 auto; "></div>

<table id="datatable1" style="display:none;">
   <thead>
        <tr>
	      <!--<th disabled="disabled" >Distances Traveled in km</th>
           <th>Distances Traveled in km (Today)</th>-->
        </tr>
    </thead>
    <tbody>
        <!--here i need to put the code for getting total distances-->
        <?php

        ?>
 
        <tr>
            <th>Today</th>
            <?php echo "<td>".$max_viols_of_day[0]."</td>";?>
                
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[1]."</th>";?>
            <?php echo "<td>".$max_viols_of_day[1]."</td>";?>
            
        </tr>
       
        <tr>
             <?php echo "<th>".$date_array_display[2]."</th>";?>
            <?php echo "<td>".$max_viols_of_day[2]."</td>";?>  
		</tr>
        <tr>
            <?php echo "<th>".$date_array_display[3]."</th>";?>
             <?php echo "<td>".$max_viols_of_day[3]."</td>";?>           
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[4]."</th>";?>
             <?php echo "<td>".$max_viols_of_day[4]."</td>";?>           
        </tr>
		 <tr>
           <?php echo "<th>".$date_array_display[5]."</th>";?>
                     <?php echo "<td>".$max_viols_of_day[5]."</td>";?>
           
        </tr>
		 <tr>
            <?php echo "<th>".$date_array_display[6]."</th>";?>
            <?php echo "<td>".$max_viols_of_day[6]."</td>";?>
           
        </tr>
    </tbody>
</table>
</div>    </div><!--tab-->
    <div id="menu2" class="tab-pane fade">
      <div class="col-lg-4">
      <h4>MaxSpeed</h4>
	     <svg width="16px" height="12px"><rect width="16" height="12" style="fill:#ff9900;"> <span style="font-size:17px; margin-left:2%;">Max Speed Reached <span style = "color:#ff9900;">(limited speed   <?php echo " $speed_ltd ";?>
)</span></span></svg><br> 
	   
	   
	   </div>
        <div class="row">
	   <div class="col-lg-6">
       <div id="container2"  style="max-width:600px; height: 400px; margin: 0 auto; "></div>
  
<table id="datatable2" style="display:none;">
   <thead>
        <tr>
	      <!--<th disabled="disabled" >Distances Traveled in km</th>
           <th>Distances Traveled in km (Today)</th>-->
        </tr>
    </thead>
    <tbody>
           <tr>
            <th>Today</th>
            <?php echo "<td>".$max_speed_of_day[0]."</td>";?>
                
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[1]."</th>";?>
            <?php echo "<td>".$max_speed_of_day[1]."</td>";?>
            
        </tr>
       
        <tr>
             <?php echo "<th>".$date_array_display[2]."</th>";?>
            <?php echo "<td>".$max_speed_of_day[2]."</td>";?> </tr>
        <tr>
            <?php echo "<th>".$date_array_display[3]."</th>";?>
             <?php echo "<td>".$max_speed_of_day[3]."</td>";?>           
        </tr>
        <tr>
            <?php echo "<th>".$date_array_display[4]."</th>";?>
             <?php echo "<td>".$max_speed_of_day[4]."</td>";?>           
        </tr>
		 <tr>
           <?php echo "<th>".$date_array_display[5]."</th>";?>
           <?php echo "<td>".$max_speed_of_day[5]."</td>";?>
           
        </tr>
		 <tr>
            <?php echo "<th>".$date_array_display[6]."</th>";?>
            <?php echo "<td>".$max_speed_of_day[6]."</td>";?>
           
        </tr>
    </tbody>
</table>
</div>
    </div><!--tab-->
</div><!--of row-->





  
      </div><!--tabs_content -->
     </div><!--/.container-->
    </div>
</div><!-- row -->
<!--end of not showing on all display-->
<?php   }

?>
<br><br>	  
<!-- Trip Reports -->
<br><br>	  

<?php
   if($alldisp == 0) {
?>
 <div class="row">
 <div class="col-lg-10">
 <div class="container">
 <h4>Trip Sheet Reports</h4>
 <ul class="nav nav-tabs">
   	 <li class="active"><a data-toggle="tab" href="#today_rpt">Today</a></li>
   	 <li><a data-toggle="tab" href="#minus1">Yesterday</a></li>
   	 <li><a data-toggle="tab" href="#minus2"><?php echo $minus2_day;?></a></li>
   	 <li><a data-toggle="tab" href="#minus3"><?php echo $minus3_day;?></a></li>
   	 <li><a data-toggle="tab" href="#minus4"><?php echo $minus4_day;?></a></li>
   	 <li><a data-toggle="tab" href="#minus5"><?php echo $minus5_day;?></a></li>
   	 <li><a data-toggle="tab" href="#minus6"><?php echo $minus6_day;?></a></li>

 </ul>

 <div class="tab-content">

 <div id="today_rpt" class="tab-pane fade in active">
      <br>
      <p>Journey Report of <?php echo $today_dt ;?> 
      <?php 
	  $today_dt = "2022-12-17";
	  ?>
   <a href="TripDemo.php?tdate=<?php echo "$today_dt";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">   (View On Map)</a> </p>
      <div class="col-lg-10">
         
       <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$today_dt' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";


            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $today_dt;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>




                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
        
       </div>
      </div>

<!-- Reporting of the previous day-->
 <div id="minus1" class="tab-pane fade ">
      <br>
      <p>Journey Report of <?php echo $minus1_day ;?>
	  <?php 
	  $minus1_day = "2022-12-16";
	  ?>
      <a href="TripDemo.php?tdate=<?php echo "$minus1_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>"> (View On Map)</a>   </p>
      <div class="col-lg-10">
         
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus1_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";

            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus1_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>

                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>

<!-- Reporting of the day before yesterday -->
 <div id="minus2" class="tab-pane fade ">
      <br>
	  <?php 
	  $minus2_day = "2022-12-15";
	  ?>
      <p><a href="TripDemo.php?tdate=<?php echo "$minus2_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">View On Map</a>   </p>
      <div class="col-lg-10">
          
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus2_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";
            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus2_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>

                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>

<!-- Reporting of the day  minus3 -->
 <div id="minus3" class="tab-pane fade ">
      <br>
	  <?php 
	  $minus3_day = "2022-12-14";
	  ?>
      <p> <a href="TripDemo.php?tdate=<?php echo "$minus3_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">View On Map</a>   </p>
      <div class="col-lg-10">
          
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus3_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";
            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus3_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>
                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>

<!-- Reporting of the day  minus4 -->
 <div id="minus4" class="tab-pane fade ">
      <br>
	  <?php 
	  $minus4_day = "2022-12-13";
	  ?>
      <p><a href="TripDemo.php?tdate=<?php echo "$minus4_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">View On Map</a> </p>
      <div class="col-lg-10">
         
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus4_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";
            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus4_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>
                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>

<!-- Reporting of the day  minus5 -->
 <div id="minus5" class="tab-pane fade ">
      <br>
	  <?php 
	  $minus5_day = "2022-12-12";
	  ?>
      <p> <a href="TripDemo.php?tdate=<?php echo "$minus5_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">View On Map</a>   </p>
      <div class="col-lg-10">
         
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus5_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";

            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus5_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>

                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>

<!-- Reporting of the day  minus6 -->
 <div id="minus6" class="tab-pane fade ">
      <br>
	  <?php 
	  $minus6_day = "2022-12-11";
	  ?>
      <p> <a href="TripDemo.php?tdate=<?php echo "$minus6_day";?>&timei=<?php echo "$devid";?>&vehname=<?php echo "$xvehname";?>&map_load_key=<?php echo "$map_api_key";?>">View On Map</a>  </p>
      <div class="col-lg-10">
         
        <table class="table table-bordered" style="font-size: 14px;">
       <thead>
         <tr  class="success">
           <th>Journey</th>
           <th>Started At</th>
           <th>Reached At</th>
           <th>Distances Travelled</th>
          <th>Duration Travelled</th>
          <th>Time Spent Here</th>
          <th>Purpose</th>
         </tr>
       </thead>
       <tbody>
           <?php
              $sql_r = "select * from JTB_TEMP where ST_DATE = '$minus6_day' and IMEI = '$devid' order by SLNO ASC";
              $result_r = mysqli_query($conn,$sql_r);
              $num_rows = mysqli_num_rows($result_r);
              if($num_rows !== 0){ 
                 $journey_counter = 0;
                 //echo "inside if condition & num of rows = $num_rows & minus one day is $minus1_day";
                 while ($row_rep = mysqli_fetch_array($result_r, MYSQLI_ASSOC)) {
                     //echo "inside while condition";
                     $journeys = "Journey".$row_rep["SLNO"];
                     $started_journey_at = $row_rep["ST_TIME"]."<b>"." From "."</b>"."<br>".$row_rep["ST_LOC"];
                     $chk_end_jrny = $row_rep["DT_TIME"];
                     if($chk_end_jrny == "DATA NOT AVAILABLE"){
                       $ended_journey_at = "DATA YET NOT RECEIVED";
                     }
                     else {
                       $ended_journey_at = $row_rep["DT_TIME"]."<b>"." @ "."</b>"."<br>".$row_rep["DT_LOC"];
                     }
           	  $distances_travelled = $row_rep["DIST_TVL"];
           	  $Total_travelled= (round($distances_travelled,1));
                     
                     $the_stdate = $row_rep["ST_DATE"]." ".$row_rep["ST_TIME"];
                     $the_dtdate = $row_rep["DT_DATE"]." ".$row_rep["DT_TIME"];
                     $spend_time = trip_time_calc($the_stdate, $the_dtdate);
                     $on_spot_time = $row_rep["TIME_SPENT"];
                     $purpose = "BUSINESS";

            ?>	
                     <tr>
                       <td><a href="TripDemoSingle.php?tdate=<?php echo $minus6_day;?>&timei=<?php echo $devid;?>&vehname=<?php echo $xvehname;?>&stat_time=<?php echo $row_rep["ST_TIME"];?>&end_time=<?php echo $row_rep["DT_TIME"];?>&map_load_key=<?php echo $map_api_key;?>&trip_no=<?php echo $journeys;?>&dist_tvd=<?php echo $Total_travelled;?>&onspot_time=<?php echo $on_spot_time;?>&trip_stat_loc=<?php echo $started_journey_at;?>&trip_end_loc=<?php echo $ended_journey_at;?>&time_trvd=<?php echo $spend_time;?>"><?php echo $journeys;?></a></td>




                       <?php echo "<td>".$started_journey_at."</td>";?>
                       <?php echo "<td>".$ended_journey_at."</td>";?>
                       <?php echo "<td>".$Total_travelled." ".(Km)."</td>";?>
                       <?php echo "<td>".$spend_time."</td>";?>
                       <?php echo "<td>".$on_spot_time."</td>";?>
                       <?php echo "<td>".$purpose."</td>";?>
                     </tr>		 	  
           <?php
                    }}else{
           ?>  
                    <tr>
                      <?php echo "<td colspan = '7' style='text-align: center;'>".$message."</td>";?>
           
                    </tr>	
           <?php
           }
           ?> 
       </tbody>
       </table>
       
       </div>
       </div>




 </div> <!--tab content-->
  
</div><!--container-->
</div><!--lg10-->
</div><!--row-->
<?php   }

?>

<!-- end of trip reports-->

</div><!-- body_container-->
 
 <br></br>

<div class="modal fade" id="breakdownEntry" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Break Down Help</h5>
        <button type="button" class="close" style="margin-top: -5% !important;"data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	   <form id="breakdown_entry_form" role="form" method="post" action="breakdownSub.php">
      <div class="modal-body">
       <div class="form-group">
            <label for="ser-company-name" class="form-control-label">Please let us know your issue:</label>
            <input type="text" class="form-control" id="service_cost_total" placeholder="Typically tyre puncture or Engine breakdown" name="serTotalCost">
          </div>
		  <input type="hidden" name="imei_unq" id="imei_unq" value="<?php echo $devid; ?>"/>
		  
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Send</button>
      </div>
	   </form>
    </div>
  </div>
</div>

     <?php include("footer.html");?>


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="./dist/js/vendor/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="./dist/js/flat-ui.min.js"></script>
	<script src="./dist/js/vendor/jquery.validate.min.js"></script>


    <script src="./docs/assets/js/application.js"></script>
    <script src="dist/js/vendor/video.js"></script>
    <script src="js/highcharts.js"></script>
    <script src="js/data.js"></script>
    <!-- jQuery Knob -->
    <script src="./js/plugins/jqueryKnob/jquery.knob.js" type="text/javascript"></script>

<script type="text/javascript">
$(function () {

     $('#container').highcharts({
        data: {
            table: 'datatable'
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Distances Traveled',
            color : '#1abc9c'
        },
        yAxis: {
            allowDecimals: false,
            title: {
                text: 'Distances Traveled in km',
                color : '#1abc9c'
            }
        },
      series: [{
         name : 'Distances Traveled',
             color : '#1abc9c'
        
        }],
    
        tooltip: {
             //valueSuffix: 'KM'
              formatter: function () {
                return '<b>' + this.series.name + '</b><br/>' +
                    this.point.y + ' ' + 'Km on '+ this.point.name.toLowerCase();
            }
        }
    }); 
    
     $('#container1').highcharts({
        data: {
            table: 'datatable1'
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Over Speed Report'
        },
        yAxis: {
            allowDecimals: false,
            title: {
                text: 'Number of Violations'
            }
        },
      series: [{
        name : 'Number of Violations',
              color : '#ff9900'
        
        }]
    
       
    });
    
      $('#container2').highcharts({
        data: {
            table: 'datatable2'
        },
        chart: {
            type: 'column'
        },
        title: {
            text: 'Max Speed of the day'
        },
        yAxis: {
            allowDecimals: false,
            title: {
                text: 'Max Speed'
            }
        },
      series: [{
        name : 'Max Speed',
              color : '#ff9900'
        
        }]
    
       
    });

    
});
    </script>
    <!--jknob script-->
        <script type="text/javascript">






            $(function() {
                /* jQueryKnob */

                $(".knob").knob({
                    /*change : function (value) {
                     //console.log("change : " + value);
                     },
                     release : function (value) {
                     console.log("release : " + value);
                     },
                     cancel : function () {
                     console.log("cancel : " + this.value);
                     },*/
                    draw: function() {

                        // "tron" case
                        if (this.$.data('skin') == 'tron') {

                            var a = this.angle(this.cv)  // Angle
                                    , sa = this.startAngle          // Previous start angle
                                    , sat = this.startAngle         // Start angle
                                    , ea                            // Previous end angle
                                    , eat = sat + a                 // End angle
                                    , r = true;

                            this.g.lineWidth = this.lineWidth;

                            this.o.cursor
                                    && (sat = eat - 0.3)
                                    && (eat = eat + 0.3);

                            if (this.o.displayPrevious) {
                                ea = this.startAngle + this.angle(this.value);
                                this.o.cursor
                                        && (sa = ea - 0.3)
                                        && (ea = ea + 0.3);
                                this.g.beginPath();
                                this.g.strokeStyle = this.previousColor;
                                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false);
                                this.g.stroke();
                            }

                            this.g.beginPath();
                            this.g.strokeStyle = r ? this.o.fgColor : this.fgColor;
                            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false);
                            this.g.stroke();

                            this.g.lineWidth = 2;
                            this.g.beginPath();
                            this.g.strokeStyle = this.o.fgColor;
                            this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false);
                            this.g.stroke();

                            return false;
                        }
                    }
                });
                /* END JQUERY KNOB */
                //INITIALIZE SPARKLINE CHARTS
                $(".sparkline").each(function() {
                    var $this = $(this);
                    $this.sparkline('html', $this.data());
                });

                /* SPARKLINE DOCUMENTAION EXAMPLES http://omnipotent.net/jquery.sparkline/#s-about */
                //drawDocSparklines();
                //drawMouseSpeedDemo();

            });

    </script>

    <script type="text/javascript">
     var val_DC = <?php echo json_encode(round($xdist, 1, PHP_ROUND_HALF_UP)); ?>;
     var val_AS = <?php echo json_encode(round($xavgspd, 1, PHP_ROUND_HALF_UP)); ?>;
     var val_TR = <?php echo json_encode(round($xtime, 1, PHP_ROUND_HALF_UP)); ?>;
     var val_FB = 50;
     var val_FC = 30;
     var val_CE = 40;
     document.getElementById("DistCov").value=val_DC;
     document.getElementById("AvgSpd").value=val_AS;
     document.getElementById("TimeRoad").value=val_TR;
     document.getElementById("FuelBurnt").value=val_FB;
     document.getElementById("FuelCost").value=val_FC;
     document.getElementById("CarbEmi").value=val_CE;
    </script>

    <script>
      var dt = new Date();
      var string_var2 = <?php echo json_encode($xdate); ?>;//dt.toDateString();
      var string_var1 = "Commute Metrics as on ";
      var res = string_var1.concat(string_var2);
      document.getElementById("sysdate").innerHTML = res;
    </script>

    <script>
     ///$("#cars li").click(function(){
     ///   var url = location.href= "mycardetails.php?cars=" +$(this).find('a').attr("data-val");
     ///   location-href = url;
     /// });
    </script>

    <script>
      var jmapid_len = <?php echo $pidmapd_len;?>;
      var jmapdasid_arr = <?php echo json_encode($pmapdas_id_nick); ?>;
      var jdispid = <?php echo json_encode($dispid);?>;
      var jdevidcnt = <?php echo $devid_cnt; ?>;
      var jdevcnt = <?php echo $dev_cnt; ?>;
      var jvehtype = <?php echo json_encode($vehtype);?>;
      var jalldisp = <?php echo $alldisp; ?>;
      //var jdebug_array = <?php echo json_encode($debug_array);?>;
      //var jdevid = <?php echo $pindevidtbl;?>;
      
      //creating H6 header
      var h6 = document.createElement("h6");
      //var h6t = document.createTextNode("Spot My Car/Bike");
      var h6t = document.createTextNode(jvehtype);
          h6.appendChild(h6t);
      var div_ele = document.getElementById("listveh");
          div_ele.appendChild(h6);

      for(jrowi = 0; jrowi < jmapid_len; jrowi++){
        //creating a div class
        var divf = document.createElement("div");
            divf.setAttribute("class","col-lg-2");

        //form created
        var form_id = "addform".concat(jrowi);
        var f = document.createElement("form");
            f.setAttribute("id", form_id);
            f.setAttribute("class", "form-inline");
            f.setAttribute("role", "form");
            //f.setAttribute("method", "post");
            f.setAttribute("method", "get");
            f.setAttribute("action", "locate_veh.php");

        //create div class for input and button
        var div_ib = document.createElement("div");
            div_ib.setAttribute("class", "form-group");

        //creating hidden input field
        var idv = jmapdasid_arr[jrowi][0];
        var inp = document.createElement("input");
            inp.setAttribute("type","hidden");
            inp.setAttribute("class", "form-control"); 
            inp.setAttribute("value", idv);
            inp.setAttribute("name", "vehid");

        //creating the button 
        var but_sub = document.createElement("button");
            but_sub.setAttribute("type","submit");
            but_sub.setAttribute("class", "btn btn-link");
            but_sub.setAttribute("name","subbut");
            but_sub.setAttribute("value", form_id);
	    if(jmapdasid_arr[jrowi][0].localeCompare(jdispid) == 0  || jalldisp == 1){
	      but_sub.setAttribute("style", "background-color:#F7F3F2");
	    }
	                ///but_sub.setAttribute("value", "form_id");
        var buttxt = document.createTextNode(jmapdasid_arr[jrowi][2]);
            but_sub.appendChild(buttxt);

        //append inp and button
            div_ib.appendChild(inp);
            div_ib.appendChild(but_sub);

        //now bring under form
            f.appendChild(div_ib);

        //appending the form to the div class
            divf.appendChild(f);

        //linking to the main div
        //  var div_ele = document.getElementById("listveh");
              div_ele.appendChild(divf);
      }   

    </script>
	
					<script>
		$('#breakdownEntry').on('show.bs.modal', function (event) {
  var srv_cost_button = $(event.relatedTarget) // Button that triggered the modal
  var srv_cost_recipient_data = srv_cost_button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.

});
</script>
<script>
    jQuery.validator.setDefaults({
      //debug: true,
      success: "valid"
    });
	
	$( "#breakdown_entry_form" ).validate({
      rules: {
		serTotalCost: "required"
      }
    });
    </script>
		<style>
#highcharts-0 > svg > g.highcharts-series-group > g.highcharts-series.highcharts-series-0.highcharts-tracker > rect:nth-child(1){
fill: #ff9900;
}
.highcharts-yaxis-title{
fill:#ff9900!important;
}
</style>
  </body>
</html>
