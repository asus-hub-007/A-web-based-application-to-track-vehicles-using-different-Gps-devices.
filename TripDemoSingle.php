<?php

 include_once ('./all_settings.php');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

  session_start();
  date_default_timezone_set("Asia/Calcutta");


  $tdate    = $_REQUEST["tdate"];
  $timei    = $_REQUEST["timei"];
  $veh_name = $_REQUEST["vehname"];
  $st_tm   = $_REQUEST["stat_time"];
  $dt_tm   = $_REQUEST["end_time"];
  $trip_num   = $_REQUEST["trip_no"];
  $dist_tvd   = $_REQUEST["dist_tvd"];
  $onspot_time   = $_REQUEST["onspot_time"];
  $trip_stat_loc   = $_REQUEST["trip_stat_loc"];
  $trip_end_loc   = $_REQUEST["trip_end_loc"];
  $time_trvd = $_REQUEST["time_trvd"];
  
  $key_map_load = $_REQUEST["map_load_key"];



  //$st_tm   = "00:00:00";
  //$dt_tm   = "23:59:59";
  $start_date = $tdate." ".$st_tm;
  $end_date   = $tdate." ".$dt_tm;
  //$start_date = "2016-08-06 06:00:00";
  //$end_date   = "2016-08-08 06:00:00";


  $longitude_array = array();
  $latitude_array = array();

  $stoppage_lat = array();
  $stoppage_lon = array();
   $ststartlat = "";
   $ststartlon = "";
   $ststoplat = "";
   $ststoplon = "";

  $speed_knt_array = array();
  $date_array = array();
  $ignition_array = array();
  $devtime_array = array();

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




  $_SESSION["sbuyflg"]=0;
  if(isset($_SESSION["suname"])){
    $puname=$_SESSION["suname"];
    //Fetching First Name and Last Name
    include_once('./dbconfig.php');
    $tbl_name="gps_logs"; //Table Name
    $sam_events_tbl = "sam_events";//Events Table Name
 
    //Connect to server and select database.
    //mysqli_connect("$host", "$username", "$password") or die("cannot connect the database");
    //mysqli_select_db("$db_name")or dies("cannot select DB");

   //Collecting the journey points
   $sql = "SELECT LATITUDE, LONGITUDE, SPEED_KNOTS, DATE, IGNITION_STAT, Device_time FROM $tbl_name WHERE IMEI = '$timei' AND Device_time BETWEEN '$start_date' AND '$end_date' order by id";

   $result = mysqli_query($conn,$sql);
   $count = mysqli_num_rows($result);

   $dist_tvl = "NA";
   $max_spd  = "Maximum Speed is 60 KMPH";
   $avg_spd  = "Average Speed is 30 KMPH";
 
   if ($count > 0){
     for($rowi=0; $rowi<$count; $rowi++){
       $row = mysqli_fetch_assoc($result);
       $longitude_array[$rowi] = $row["LONGITUDE"];
       $latitude_array[$rowi] = $row["LATITUDE"];
       $speed_knt_array[$rowi] = $row["SPEED_KNOTS"]; 
       $date_array[$rowi] = $row["DATE"]; 
       $ignition_array[$rowi] = $row["IGNITION_STAT"]; 
       $devtime_array[$rowi] = $row["Device_time"]; 

     }
     
   //calculating the total distance travelled.
	 $dist_tvl = 0;
	/* for($i=0;$i<$count;$i++){
	 $j= $i +1;
	 if ($j == $count){$j = $i;}
      $caldist = bwtDistance($latitude_array[$i], $longitude_array[$i],$latitude_array[$j],$longitude_array[$j]);
      $dist_tvl = $dist_tvl + number_format((float)($caldist), 2, '.', '');
         //echo "<br><br><br>"."value of i is $i - Lat-i $latitude_array[$i] - Long-i $longitude_array[$i] - lat-i+1 $latitude_array[$j], long-i+1 $longitude_array[$j] - distance is $dist_tvl";
	 }*/
	 $dist_tvl="Total Distance Travelled:"." $dist_tvl"."Kms";
   }
   //End of collecting the journey points

   //Collect the stopage points from JTB table 
  $dummy_array = array();
   $stsql = "SELECT * FROM JTB_TEMP WHERE IMEI = '$timei' AND ST_DATE='$tdate' order by SLNO ASC ";
   $stres = mysqli_query($conn,$stsql);
   $stcnt = mysqli_num_rows($stres);
   if($stcnt >0){
     for($sti=0; $sti<$stcnt; $sti++){
       $strow = mysqli_fetch_assoc($stres);
       //Capturing the start point
	$stoppage_lat[$sti] = $strow["DT_LAT"];
	$stoppage_lon[$sti] = $strow["DT_LON"];
	//echo "<br>"."<br>"."<br>"."<br>"."<br>"."Lat is $stoppage_lat[$sti], lon $stoppage_lon[$sti]";
       $st1less = $stcnt - 1;
       if($sti == 0){
       //Capturing the start point
	//$stpoint = $strow["ST_LOC"];

	$ststartlat = $strow["DT_LAT"];
	$ststartlon = $strow["DT_LON"];
       }
       elseif($sti == $st1less ){
       //Capturing the end point
	//$stpoint = $strow["DT_LOC"];

	$ststoplat = $strow["DT_LAT"];
	$ststoplon = $strow["DT_LON"];
       }

     }
   }
   $stops_len = sizeof($stoppage_lat) - 1 ;
   if($stops_len < 0 ){
    $stops_len = "NA";	   
   }


   //Taking the vehicle name
     //xml reading for displaying distance travelled, Time Spent & Avg Speed
    $xml='feeds/demo_feed.xml';
    $dom = new DOMDocument;
    $dom->Load($xml); // use if loading separate xml file
    $xpath = new DOMXPath($dom);
    $xquery = "//item[id='".$timei."']";
    $xresult = $xpath->query($xquery);
    if(count($xresult->item(0)) > 0 ) { 
       $xvehname = $xresult->item(0)->getElementsByTagName('vehicle')->item(0)->nodeValue;       
    }
    else {
      $xvehname = "NULL";
    }

 }

       ?>
	   
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>TRACKEASY.COM - Car Tracker System Car Device Vehicle Tracker GPS Location Fleet Management Auto Tracking Phone GPS Truck Automobile Tracking Cheap Tracking Car Insurance</title>
<meta name="description" content="Track your car with our GPS tracking device. How about free vehicle tracking with car insurance facility Free vehicle tracking report ">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="keywords" content="vehicle tracking system with GPS devices and car insurance facility in India">    <!-- Loading Bootstrap -->

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

    <link rel="stylesheet" href="./docs/assets/css/jquery-ui.css" />    


    <!-- Load jQuery JS -->
    <script src="./js/jquery-1.9.1.js"></script>
    <!-- Load jQuery UI Main JS  -->
    <script src="./js/jquery-ui.js"></script>
    <script src="./dist/js/vendor/jquery.chained.js"></script>	



    <!-- Load SCRIPT.JS which will create datepicker for input field  -->
    <script src="./js/datepicker_normal.js"></script>

    
       
    <style>
      #JourneyMap {
        width: 100%;
        height: 800px;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo "$key_map_load";?>"></script>
    <script src="./js/mapLayerv3.js"></script>
    
    <script>
       var utimei;
       var uign;
  

       var ulat_coords = new Array();
       var ulon_coords = new Array();
       var ustplats    = new Array();
       var ustplons    = new Array();

       ulat_coords = <?php echo json_encode($latitude_array);?>;
       ulon_coords = <?php echo json_encode($longitude_array);?>; 
	   speed_array_data = <?php echo json_encode($speed_knt_array);?>;
       ustrtlat    = <?php echo json_encode($ststartlat);?>;
       ustrtlon    = <?php echo json_encode($ststartlon);?>; 
       ustplats    = <?php echo json_encode($stoppage_lat);?>; 
       ustplons    = <?php echo json_encode($stoppage_lon);?>; 
       ustoplat    = <?php echo json_encode($ststoplat);?>;
       ustoplon    = <?php echo json_encode($ststoplon);?>; 
       ucnt        = <?php echo json_encode($stcnt);?>; 
	   trip_stat_point = <?php echo json_encode($trip_stat_loc);?>;
	   trip_stop_point = <?php echo json_encode($trip_end_loc);?>;

    </script>

  </head>
  <body onload="segmentedJourney(ulat_coords, ulon_coords, trip_stat_point, trip_stop_point, speed_array_data)">
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
            <?php include("dashheader.html");?>
          </ul>

          <form class="navbar-form navbar-right" role="form" action="pologout.php">
            <button type="submit" class="btn btn-danger" >Logout</button>
          </form>
        </div><!--/.nav-collapse -->
      </div>
    </div>
          <!--p class="navbar-text navbar-right">Hello <a class="navbar-link" href="mydetails.php"><font color="#1abc9c"><?php echo "$pfname";?></font></a>!!</p-->

    <div class="container">
      <div class="demo-type-example">
        <br></br>
        <p></p>
        <div class="col-xs-6" id="listveh">
         <!---->
        </div>
     </div>
    </div><!--container-->


<div class="container">
<!--Div for mapping-->
<!-- Need 5 fields here to select the vehicle and data and time-->
  <div class = "row" id = "select_veh_date_NA">
    <!--Rest all comes from the code down in script-->
  </div>


      <div class="row">
        <div class="col-lg-12">
		  
            <h4>Stoppages Map View</h4>
	    <p> <b><?php echo $trip_num;?></b> - start point <img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png"> & end point <img src="http://maps.google.com/mapfiles/ms/icons/red-dot.png"> Distance Travelled - <b><?php echo $dist_tvd;?>  km</b>   Duration Travelled - <b><?php echo $time_trvd;?></b>   Time Spent - <b><?php echo $onspot_time;?></b></p>
            <!--img src="../images/blackdot_retina.png" alt="blackdot" data-src="holder.js/50%x50%" -->
            <!--div class="row"-->
			<!-- willhave table here-->

           <div id="JourneyMap"></div>
       </div>
       </div>
		 <br>  
</div>

<br>

 <?php include("footer.html");?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <!--script src="./dist/js/vendor/jquery.min.js"></script-->
    <script src="./dist/js/flat-ui.min.js"></script>
    <script src="./docs/assets/js/application.js"></script>
    <!--jknob script-->



 <script>
   var jmycnt = <?php echo json_encode($pidmapd_len);?>;
   var jmyveh_arr = <?php echo json_encode($pmapdas_id_nick);?>;
   //var jmyveh = ["XUV"];
   var jtime = ["00:00", "00:30", "01:00","01:30","02:00","02:30","03:00", "03:30", "04:00", "04:30",
                "05:00", "05:30", "06:00","06:30","07:00","07:30","08:00", "08:30", "09:00", "09:30",
                "10:00", "10:30", "11:00","11:30","12:00","12:30","13:00", "13:30", "14:00", "14:30",
                "15:00", "15:30", "16:00","16:30","17:00","17:30","18:00", "18:30", "19:00", "19:30",
                "20:00", "20:30", "21:00","21:30","22:00","22:30","23:00", "23:30", "23:45", "23:59"];

   
   var h4 = document.createElement("h4");
   var h4text = document.createTextNode("Please Select Your Vehicle and Date/Time");
   h4.appendChild(h4text);

   //Creating a form
   var f = document.createElement("form");
       f.setAttribute("id", "addform");
       f.setAttribute("class", "form-horizontal");
       f.setAttribute("role", "form");
       f.setAttribute("method", "get");
       f.setAttribute("action", "HistoryTrack.php");


   var fgu = document.createElement("div");
       fgu.setAttribute("class","form-group");
       f.appendChild(fgu);
   
   //Field to Show the vehicles

   //create div col-lg-10 for input select field
       var lg = document.createElement("div");
           lg.setAttribute("class","col-lg-3");
   //append it to la
       fgu.appendChild(lg);

   //Creeating Select Element
   var idv = "VEHICLE";
   var sl = document.createElement("select");
       sl.setAttribute("data-toggle","select");
       sl.setAttribute("class","form-control select select-primary");
       sl.setAttribute("id",idv);
       sl.setAttribute("name",idv);

   var slo1 = document.createElement("option");
   var slo1_txt = document.createTextNode("CHOOSE VEHICLE");
       slo1.appendChild(slo1_txt);
       sl.appendChild(slo1);

   //Here on the options will come from timei table which dev ids.
       for (jdevnick = 0; jdevnick < jmycnt; jdevnick++){ 
           var slo2 = document.createElement("option");
               //slo2.setAttribute("value", "anchor2");
           var slo2_txt = document.createTextNode(jmyveh_arr[jdevnick][0]);
               slo2.appendChild(slo2_txt);
               sl.appendChild(slo2);
        }//end of for

           lg.appendChild(sl);


   //Putting the calendar Day 1 Field

   //create div col-lg-10

   var fg = document.createElement("div");
       fg.setAttribute("class","form-group");
       f.appendChild(fg);
   
      var lg = document.createElement("div");
          lg.setAttribute("class","col-lg-3");
      //append it to la
          fg.appendChild(lg);

      //create input type
      var idv = "STDATE";
      //var idv = "insurance";
      var inp = document.createElement("input");
          inp.setAttribute("type","text");
          inp.setAttribute("class","form-control");
          inp.setAttribute("id",idv);
          inp.setAttribute("name",idv);
          inp.setAttribute("value","CHOOSE START-DATE");
      //append it to lg
          lg.appendChild(inp);



   //putting the Time Field

   //create div col-lg-10 for input select field
       var lgst = document.createElement("div");
           lgst.setAttribute("class","col-lg-3");
   //append it to la
       fg.appendChild(lgst);

   //Creeating Select Element
   var idvst = "ST_TIME";
   var slst = document.createElement("select");
       slst.setAttribute("data-toggle","select");
       slst.setAttribute("class","form-control select select-info");
       slst.setAttribute("id",idvst);
       slst.setAttribute("name",idvst);

   var slo1st = document.createElement("option");
   var slo1_txtst = document.createTextNode("CHOOSE START TIME");
       slo1st.appendChild(slo1_txtst);
       slst.appendChild(slo1st);

   //Here on the options will come from timei table which dev ids.
       for (jdevi = 0; jdevi < 50; jdevi++){ 
           var slo2st = document.createElement("option");
               //slo2.setAttribute("value", "anchor2");
           var slo2_txtst = document.createTextNode(jtime[jdevi]);
               slo2st.appendChild(slo2_txtst);
               slst.appendChild(slo2st);
        }//end of for

           lgst.appendChild(slst);



   //PUtting the Day 2 Date field 
   //create div col-lg-10
      var lg = document.createElement("div");
          lg.setAttribute("class","col-lg-3");
      //append it to la
          fg.appendChild(lg);

      //create input type
      var idv = "DTDATE";
      var inp = document.createElement("input");
          inp.setAttribute("type","text");
          inp.setAttribute("class","form-control");
          inp.setAttribute("id",idv);
          inp.setAttribute("name",idv);
          inp.setAttribute("value","CHOOSE END-DATE");
       //append it to lg
          lg.appendChild(inp);




   //putting the Time Field
   //create div col-lg-10 for input select field
       var lgst = document.createElement("div");
           lgst.setAttribute("class","col-lg-3");
   //append it to la
       fg.appendChild(lgst);

   //Creeating Select Element
   var idvst = "DT_TIME";
   var slst = document.createElement("select");
       slst.setAttribute("data-toggle","select");
       slst.setAttribute("class","form-control select select-info");
       slst.setAttribute("id",idvst);
       slst.setAttribute("name",idvst);

   var slo1st = document.createElement("option");
   var slo1_txtst = document.createTextNode("CHOOSE END TIME");
       slo1st.appendChild(slo1_txtst);
       slst.appendChild(slo1st);

   //Here on the options will come from timei table which dev ids.
       for (jdevi = 0; jdevi < 50; jdevi++){ 
           var slo2st = document.createElement("option");
               //slo2.setAttribute("value", "anchor2");
           var slo2_txtst = document.createTextNode(jtime[jdevi]);
               slo2st.appendChild(slo2_txtst);
               slst.appendChild(slo2st);
        }//end of for

       lgst.appendChild(slst);


////Putting the Submit Button
    //creating button div
    var but_sub = document.createElement("button");
        but_sub.setAttribute("type","submit");
        but_sub.setAttribute("class","btn btn-primary");
        but_sub.setAttribute("name","subbut");
        but_sub.setAttribute("value","showjourney");
    var sub_text = document.createTextNode("Show Journey");
        but_sub.appendChild(sub_text);
//        sub_div.appendChild(but_sub);

//        sub_div_tb.appendChild(sub_div);

        f.appendChild(but_sub);




  //This is the last line appending
   var div_ele = document.getElementById("select_veh_date");
   div_ele.appendChild(h4);
   div_ele.appendChild(f);



 </script>






	
  </body>
</html>
