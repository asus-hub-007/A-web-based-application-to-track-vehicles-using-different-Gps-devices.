<?php

function execute_url ($service_url){

  $ch=curl_init();
  curl_setopt($ch, CURLOPT_URL,$service_url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
  $transactionID =curl_exec($ch);
  curl_close($ch);

}

function degree2decimalTCP($deg_coord,$direction){
  //$degree=(int)($deg_coord/100); //simple way
  //$minutes= $deg_coord-($degree*100);
  //$dotdegree=$minutes/60;
  //$decimal=$degree+$dotdegree;
  $decimal=$deg_coord/60;
  //South latitudes and West longitudes need to return a negative result
  if (($direction=="S") or ($direction=="W"))
   {
     $decimal=$decimal*(-1);
   }
  $decimal=number_format($decimal,6,'.',''); //truncate decimal to 6 places
  return $decimal;
}


function updateLiveFeed($did , $lat , $lng , $speed , $timeSent,$server_time, $dist,$latArr,$lonArr,$vehicle,
    $validgps, $immo, $bat, $sos, $ac, $ign, $gprs, $bstrng){
    $latt=array();$lon=array();    

    if (strcmp($gprs, "NA")){
      $gprs = $gprs."%";
    }
    if (strcmp($bstrng, "NA")){
      $bstrng = $bstrng/1000;
    }
    $xml='/home/trackeasycom/public_html/feeds/location_feed.xml';
    $xml_bk = '/home/trackeasy/public_html/feeds/location_feed.xml.bk';
    $dom = new \DOMDocument;
   // $dom->Load($xml); // use if loading separate xml file
	if(@$dom->load($xml)){
		copy($xml, $xml_bk);
		$xpath = new DOMXPath($dom);
    //Sami commented this for taking only latest time stamp instead of putting new entry for new date
    //$query = "//item[id='".$did."' and time='".$timeSent."']";
    $query = "//item[id='".$did."']";
    $result = $xpath->query($query);

    $tquery =  "//item[time='".$timeSent."']";
    $tresult = $xpath->query($tquery);

    $wr_file = 0;

    //if(count($result->item(0)) > 0 ) { 
    if($result->length > 0 ) { 
     if($validgps == 1 ){
       $latt=array($result->item(0)->getElementsByTagName('latitude')->item(0)->nodeValue,$latArr);
       $lon=array($result->item(0)->getElementsByTagName('longitude')->item(0)->nodeValue,$lonArr);
       $result->item(0)->getElementsByTagName('vehicle')->item(0)->nodeValue =$vehicle;

       $result->item(0)->getElementsByTagName('latitude')->item(0)->nodeValue =$lat;
       $result->item(0)->getElementsByTagName('longitude')->item(0)->nodeValue =$lng;
       $result->item(0)->getElementsByTagName('time')->item(0)->nodeValue =$timeSent;

       //Adding the new fields into the document
       $result->item(0)->getElementsByTagName('validgps')->item(0)->nodeValue 	=$validgps;
       $result->item(0)->getElementsByTagName('immo')->item(0)->nodeValue 	=$immo;
       $result->item(0)->getElementsByTagName('battery')->item(0)->nodeValue 	=$bat;
       $result->item(0)->getElementsByTagName('sos')->item(0)->nodeValue 	=$sos;
       $result->item(0)->getElementsByTagName('ac')->item(0)->nodeValue 	=$ac;
       $result->item(0)->getElementsByTagName('ign')->item(0)->nodeValue 	=$ign;
       $result->item(0)->getElementsByTagName('gprs')->item(0)->nodeValue 	=$gprs;
       $result->item(0)->getElementsByTagName('bstrng')->item(0)->nodeValue 	=$bstrng;



/////////////////////////////////////////Daily Graphs updated/////////////////////////////////////////////////////////////////////////////////
       //Sami commented this because he feels the distanc() function is called twice for the same coordinates and removed in the newer one
       //$dist = $dist + distance($latt,$lon)+$result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue;

       //this is fresh code to accomodate same vehicle but for next date - new distance, speed and time travelled info instead of adding with the previous day
       //if(count($tresult->item(0)) > 0 ){
       if($tresult->length > 0 ) { 
         $dist = $dist +$result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue;
         //$result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue =$dist;
       }
       //else {
         //$dist = $dist +$result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue;
         //$result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue =$dist;
       //}
       $result->item(0)->getElementsByTagName('distance')->item(0)->nodeValue =$dist;

       //Sami putting the avg speed details
       $vdontcare = "SPEEDDONTCARE";
       $cspeed =   $result->item(0)->getElementsByTagName('speed')->item(0)->nodeValue;

       //will not conider average till the speed 10 is attended
       if($cspeed < 10) {$avgit = 0; }
       else             {$avgit = 1; }

       if (strcmp($speed, $vdontcare) == 0) {
          //Do Nothing;
       }
       else {
         if ($speed > 10) {
           //if ((count($tresult->item(0)) > 0)  and ($avgit == 1)){
           if ($tresult->length > 0  and $avgit == 1){
             $speed = ($speed + $result->item(0)->getElementsByTagName('speed')->item(0)->nodeValue )/2;
           }
           $result->item(0)->getElementsByTagName('speed')->item(0)->nodeValue =$speed;
        }
       }

    }//check for valid gps

    else {
      $result->item(0)->getElementsByTagName('validgps')->item(0)->nodeValue 	=$validgps;
      $result->item(0)->getElementsByTagName('immo')->item(0)->nodeValue 	=$immo;
      $result->item(0)->getElementsByTagName('battery')->item(0)->nodeValue 	=$bat;
      $result->item(0)->getElementsByTagName('sos')->item(0)->nodeValue 	=$sos;
      $result->item(0)->getElementsByTagName('ac')->item(0)->nodeValue 	        =$ac;
      $result->item(0)->getElementsByTagName('ign')->item(0)->nodeValue 	=$ign;
      $result->item(0)->getElementsByTagName('gprs')->item(0)->nodeValue 	=$gprs;
      $result->item(0)->getElementsByTagName('bstrng')->item(0)->nodeValue 	=$bstrng;

    }
	
   $result->item(0)->getElementsByTagName('stime')->item(0)->nodeValue = $server_time;
    $wr_file = 1;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    }else { 
       $vehicleName = ((!empty($vehicle))? $vehicle :$did." [DID]");

       $dummy_date = "05/09/16";
       $library = $dom->documentElement;
       $item = $dom->createElement('item');
       $itemAttr = $dom->createAttribute("time");
       $item->appendChild($itemAttr);
       $itemAttrValue = $dom->createTextNode($dummy_date);
       $itemAttr->appendChild($itemAttrValue);

       $prop = $dom->createElement('id',$did);
       $item->appendChild($prop);

       $prop = $dom->createElement('vehicle',$vehicleName);
       $item->appendChild($prop);
       $prop = $dom->createElement('latitude',$lat);
       $item->appendChild($prop);
       $prop = $dom->createElement('longitude',$lng);
       $item->appendChild($prop);

       $nvdontcare = "SPEEDDONTCARE";
       if (strcmp($speed, $nvdontcare) == 0) {
          $speed = 0;
       }
       $prop = $dom->createElement('speed',$speed);
       $item->appendChild($prop);

       $prop = $dom->createElement('time',$timeSent);
       $item->appendChild($prop);
	   
	   $prop = $dom->createElement('stime',$server_time);
       $item->appendChild($prop);

       $prop = $dom->createElement('distance',$dist);
       $item->appendChild($prop);

       $prop = $dom->createElement('validgps',$validgps);
       $item->appendChild($prop);

       $prop = $dom->createElement('immo',$immo);
       $item->appendChild($prop);

       $prop = $dom->createElement('battery',$bat);
       $item->appendChild($prop);

       $prop = $dom->createElement('sos',$sos);
       $item->appendChild($prop);

       $prop = $dom->createElement('ac',$ac);
       $item->appendChild($prop);

       $prop = $dom->createElement('ign',$ign);
       $item->appendChild($prop);

       $prop = $dom->createElement('gprs',$gprs);
       $item->appendChild($prop);

       $prop = $dom->createElement('bstrng',$bstrng);
       $item->appendChild($prop);


       //final entry
       $library->appendChild($item);
       $wr_file = 1;
    }
    if ($wr_file == 1 ){
	    file_put_contents($xml, $dom->saveXML());
    }
	//error_log("Error testing passed While Writing the Feed ".date("Y-m-d H:i:s")."\n", 3, "feeds/feedError.log");
	}else{
		copy($xml_bk, $xml);
		//error_log("Error Occured While Writing the Feed ".date("Y-m-d H:i:s")."\n", 3, "feeds/feedError.log");
	}
    
}

function sos_xml ($sdid , $slat , $slng , $ssos) {
    $sxml='feeds/sos_nscont.xml';
    $sdom = new DOMDocument;
    $sdom->Load($sxml); // use if loading separate xml file
    $xpath = new DOMXPath($sdom);
    $squery = "//item[id='".$sdid."']";
    $sresult = $xpath->query($squery);

    $swr_file = 0;

    if(count($sresult->item(0)) > 0 ) { 
      if ($ssos == 1 ) {
         $sresult->item(0)->getElementsByTagName('latitude')->item(0)->nodeValue =$slat;
         $sresult->item(0)->getElementsByTagName('longitude')->item(0)->nodeValue =$slng;
         $sresult->item(0)->getElementsByTagName('sos')->item(0)->nodeValue	=$ssos;

	 $swr_file = 1;
      }
    }

    if ($swr_file == 1 ){
	    file_put_contents($sxml, $sdom->saveXML());
    }


}//End of func sos_xml


function sos_ack_xml ($sdid , $slat , $slng , $ssos) {
    $axml='feeds/sos_ack.xml';
    $adom = new DOMDocument;
    $adom->Load($axml); // use if loading separate xml file
    $axpath = new DOMXPath($adom);
	$axquery = "//item[id='".$sdid."']";
	$axresult = $axpath->query($axquery);
	$awr_file = 0;

	if(count($axresult->item(0)) > 0 ) { 
	
	    $asos   = $axresult->item(0)->getElementsByTagName('sos')->item(0)->nodeValue;
		 if(($ssos== 1) && ($asos== 0)){
			$axresult->item(0)->getElementsByTagName('sos')->item(0)->nodeValue = 1;
			$axresult->item(0)->getElementsByTagName('latitude')->item(0)->nodeValue =$slat;
			$axresult->item(0)->getElementsByTagName('longitude')->item(0)->nodeValue =$slng;
			$awr_file = 1;
		 }
			 
          
       }       

       if ($awr_file){
         file_put_contents($axml, $adom->saveXML());
       }


}//End of func sos_ack_xml

//for calculating distances travelld in km  ..	
  $unit = "K";
  //$variable = calculate_distance_between_two_places($Lat_1, $Lon_1, $Lat_2 , $Lon_2 , $unit);
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
   
function getGMTtime ($time , $date, $imei_present_timezone_func){
	$time1=(substr($time,0,2).':'.substr($time,2,2).':'.substr($time,4,2));
   $date1=('20'.substr($date,4,2).'-'.substr($date,2,2).'-'.substr($date,0,2));
	$datetime_pass =$date1.' '.$time1;
   $date_obj = new DateTime($datetime_pass, new DateTimeZone('UTC'));
   $date_obj->setTimezone(new DateTimeZone($imei_present_timezone_func));
   return $expired = $date_obj->format('Y-m-d H:i:s');
}

function bwtDistance($lat1, $lon1 ,$lat2, $lon2){
   $radius = 6372.797;

   //$diff_lat2_lat1 = $lat2 - $lat1 ; 
   //if ($diff_lat2_lat1 != 0 ) {
   //echo "what value here $lat2 | $lat1  | $lon1 | $lon2<br>";
   $lat1 = floatval($lat1);
   $lat2 = floatval($lat2);
   $lon1 = floatval($lon1);
   $lon2 = floatval($lon2);
   if ($lat2 != $lat1 and $lon1 != $lon2) {
   $delta_Rad_Lat = deg2rad($lat2 - $lat1);  //Latitude delta in radians
   $delta_Rad_Lon = deg2rad($lon2 - $lon1);  //Longitude delta in radians
   $rad_Lat1 = deg2rad($lat1);  //Latitude 1 in radians
   $rad_Lat2 = deg2rad($lat2);  //Latitude 2 in radians
   $sq_Half_Chord = sin($delta_Rad_Lat / 2) * sin($delta_Rad_Lat / 2) + cos($rad_Lat1) * cos($rad_Lat2) * sin($delta_Rad_Lon / 2) * sin($delta_Rad_Lon / 2);  //Square of half the chord length
   $ang_Dist_Rad = 2 * asin(sqrt($sq_Half_Chord));  //Angular distance in radians
   return number_format((float)($ang_Dist_Rad*$radius), 6, '.', '');
   }
   else {
    return 0;
   }
}
          
function distance($lat, $lon) {
   $count =count($lat); $distance =0;
   //approximate mean radius of the earth in miles, can change to any unit of measurement, will get results back in that unit
   if ($count >= 2){
     for ($i=0 ; $i < $count-1 ; $i++){
       $rtDist = bwtDistance($lat[$i],$lon[$i],$lat[$i+1],$lon[$i+1]);
       $distance = $distance + number_format((float)($rtDist), 6, '.', '');
     }
   }
   return $distance;
}

function journey_dist($src_lat, $src_lon, $dst_lat, $dst_lon, $cur_dist) {
  $rtDist = bwtDistance($src_lat, $src_lon, $dst_lat, $dst_lon);
  $cur_dist = $cur_dist + number_format((float)($rtDist), 6, '.', '');
  return $cur_dist;
}
	 
/*
 * @param $deg_coord string  , coordinate from statilite
 * @param $direction , E || S || N || W
 * return void double
*/
function degree2decimal($deg_coord,$direction){
  $degree=(int)($deg_coord/100); //simple way
  $minutes= $deg_coord-($degree*100);
  $dotdegree=$minutes/60;
  $decimal=$degree+$dotdegree;
  //South latitudes and West longitudes need to return a negative result
  if (($direction=="S") or ($direction=="W"))
   {
     $decimal=$decimal*(-1);
   }
  $decimal=number_format($decimal,6,'.',''); //truncate decimal to 6 places
  return $decimal;
}

function speedAVG($cords=array()) {
   $val = floor(array_sum($cords)/(count($cords)));
   //Sami commented this - because this one was calculating distance travelled even when vehicle is standing idle. Hence from just 1kmph speed Sami increased to 5kmph
   //return ($val >= 1 ? 1 : 0 );

   //before returnig write this value in a todayavgspd file

   return ($val >= 5 ? 1 : 0 );
}

function todayspdAVG($cords=array()){
  $avgspd = floor(array_sum($cords)/(count($cords)));
  return ($avgspd);
}
	
function translateLatLngtoAddress($lat,$long,$key)
{
	//old code of geo coding
	/*
  $lat=mb_convert_encoding($lat, 'UTF-8',mb_detect_encoding($lat, 'UTF-8, ISO-8859-1', true));
  $long=mb_convert_encoding($long, 'UTF-8',mb_detect_encoding($long, 'UTF-8, ISO-8859-1', true));
  $url="http://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=false";//key=".$key;
  $result = file_get_contents($url);
  $json = json_decode($result,true);
  return $json['results'][0]['formatted_address'];
  */
  $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$long.'&key='.$key;
  $ch = curl_init();
			
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: en-us'));
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$json = curl_exec($ch);
			
			curl_close($ch);
			
			$data=json_decode($json);
		      
		     $status = $data->status;
		     if($status=="OK"){
		     
		     
       				return $data->results[0]->formatted_address;
       				
				     }else{
				      return false;
				      }
  
  
}
 //function for report module 
 function repeatingloop($newFunctime,$Lat_1,$Lon_1,$Start_ti,$IMEI_VALUE){
	
  $sql_func = "SELECT LATITUDE , LONGITUDE , TIME_STAMP, IGNITION_STAT, DATE FROM gps_logs where IGNITION_STAT = 0 and TIME_STAMP > '$newFunctime' 
  and DATE = CURDATE() and   IMEI = '$IMEI_VALUE' order by TIME_STAMP ASC";
  $result_offset = mysql_query($sql_func); 						  
  $rowpop = mysql_fetch_row($result_offset,MYSQL_ASSOC);
  if($rowpop){
  $loti_locD = $rowpop['LATITUDE'];	
   $longi_locD = $rowpop['LONGITUDE'];
   $testing_2 = $rowpop['TIME_STAMP'];	
   $End_ti = substr($testing_2,10);
   $TodaY_date = date("Y-m-d");
   $Accu_DATE = date("Y-m-d");
   $sql_igd = "SELECT LATITUDE , LONGITUDE , TIME_STAMP, IGNITION_STAT, DATE FROM gps_logs where IGNITION_STAT = 1 
   and TIME_STAMP > '$testing_2'  and DATE = CURDATE()  and IMEI = '$IMEI_VALUE' order by TIME_STAMP ASC";		          
   $result_sd = mysql_query($sql_igd);
   $row_strd = mysql_fetch_row($result_sd);                       					  
   $time_igni_ond = $row_strd[2];//t4						 
   $num_rows = mysql_num_rows($result_s);	if($num_rows == 0){						 
 $sql_ENDs = "INSERT INTO testing (IMEI,latitute_starting,longitute_starting,STARTING_time,latitute_ending,longitute_ending,ENDING_time,TODAY_DATE) 
 VALUES ('$IMEI_VALUE','$Lat_1','$Lon_1','$Start_ti','$loti_locD','$longi_locD','$testing_2','$Accu_DATE')";                                                  
 $resultENDs  = mysql_query($sql_ENDs);	 
  }else{
  //time differences calculation 					  
	$to_time = strtotime($time_igni_ond);
	$from_time = strtotime($testing_2);
	$Time_differentes = round(abs($to_time - $from_time) / 60,2);
	$Accu_DATE = date("Y-m-d");
  if($Time_differentes > 8){
 $sql_ENDp = "INSERT INTO testing (IMEI,latitute_starting,longitute_starting,STARTING_time,latitute_ending,longitute_ending,ENDING_time,TODAY_DATE) 
 VALUES ('$IMEI_VALUE','$Lat_1','$Lon_1','$Start_ti','$loti_locD','$longi_locD','$testing_2','$Accu_DATE')";                                                  
 $resultENDp  = mysql_query($sql_ENDp);									
 }else{												
 $Lat_1= $rowb[0];
 $Lon_1= $rowb[1];
 $time = $rowb[2];//t2
 $Start_ti = substr($time,10);	
 repeatingloop($time_igni_ond,$Lat_1,$Lon_1,$Start_ti,$IMEI_VALUE);
	} 
 }						   
 }  
}        
//EOF if datacnt>0 


function hotspotrange($lat1, $lon1, $lat2, $lon2, $unit) {
  $theta = $lon1 - $lon2;

  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  print "<br> distance is $dist";

  //$val = acos($dist);
  $val = floatval($dist);
  $val = $dist;
  $val = acos($val);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  print "<br>Miles is $miles\n";
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "M") {
    return ($miles * 1.609344);
}else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

//END OF MAIN PROGRAM
?>
