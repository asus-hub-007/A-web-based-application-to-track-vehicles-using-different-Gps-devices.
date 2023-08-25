<?php
if($_POST['remail']){
   session_start();
  
 //creating power domain
 include_once('./dbconfig.php');
 $tbl_name="devreq"; //Table Name
 $devid_tbl="devid"; //Table Name
 
 
//Connect to server and select database.
 //mysql_connect("$host", "$username", "$password") or die("cannot connect the database");
 //mysql_select_db("$db_name")or dies("cannot select DB");
 

 $myusernamer = $_POST['remail'];

 //write userid to devreq table and set his/her request
    $duname = stripslashes($puname);
    $duname = mysqli_real_escape_string($conn,$duname);
    $active = 'ACTIVE';
   //regenerating new password for user - forgot password...
    $sqlfp = "select email from registration where email = '$myusernamer' and status = '$active'";                      
       $resultfp = mysqli_query($conn,$sqlfp);
       	$num_rows = mysqli_num_rows($resultfp );      	
        //print_r($num_rows);
        if($num_rows > 0){
        
                      $upassword = "";
		      $charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		  
                      for($i = 0; $i < 8; $i++)
		     {
		      $random_int = mt_rand();
		      $upassword .= $charset[$random_int % strlen($charset)];
		      }		        
		     // echo $upassword, "\n";	
		      $uupassword = md5($upassword);
		      // echo $uupassword, "\n";			     
		      $sqlfu = "update registration set password = '$uupassword' where email = '$myusernamer'";
                      $resultfu = mysqli_query($conn,$sqlfu);
                      // echo "recored created";
                      $toEmail = "$myusernamer";
	              $subject = "Forgot password generated new password";
	      	      $headers  = 'MIME-Version: 1.0' . "\r\n";
		      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			 
		      // Create email headers
		      $headers .= 'From: Admin';
		      //'Reply-To: '.$user_email."\r\n" .
		      'X-Mailer: PHP/' . phpversion();
			 
		      // Compose a simple HTML email message
		      $message = '<html><body>';
		      $message .='<center><div><img src="http://hyway99.com/dist/img/logo.png">
			</div>
			<table width="600" background="#FFFFFF" style="text-align:left;" cellpadding="0" cellspacing="0">
			<tr>
			<td background="" width="31" bgcolor="#ff9900" style="border-top:1px solid #FFF; border-bottom:1px solid #FFF;" height="113">
			<div style="line-height: 0px; font-size: 1px; position: absolute;">&nbsp;</div>
			</td>
			<td width="131" bgcolor="#FFFFFF" style="border-top:1px solid #FFF; text-align:center;" height="113" valign="middle">
			<span style="font-size:25px; font-family:Trebuchet MS, Verdana, Arial; color:red;">Success!</span>
			</td>
			<td background="" bgcolor="#ff9900" style="border-top:1px solid #FFF; border-bottom:1px solid #FFF; padding-left:15px;"    height="113">
			<span style="color:#FFFFFF; font-size:18px; font-family:Trebuchet MS, Verdana, Arial;">Temporary password has been generated</span>
			</td>
			</tr>
			<tr>
			<td colspan="3">	
			<br />
			<table cellpadding="0" cellspacing="0">
			<tr>
			<td width="400" style="padding-right:10px; font-family:Trebuchet MS, Verdana, Arial; font-size:12px;" valign="top">
			<span style="font-family:Trebuchet MS, Verdana, Arial; font-size:17px; font-weight:bold;">Dear User!</span>
			<br />
			
			<p>INFO:THIS MAIL IS TO INFORM YOU THAT NEW TEMPORARY PASSWORD HAS BEEN GENERATED FOR <br> EMAIL ID :'.$myusernamer.' <br> PASSWORD : '.$upassword.'</p>
				<br><p> Please <a href ="http://hyway99.com/index.php">Login </a> with temporary password to change your password.</P>
			  Best Regards,<br/>
			  POLICE99 TEAM<br/>
			  <br/>
			  
			</td>
			</tr>
			</table>
			</td>
			</tr>					
			</table>
			</td>
			</tr>					
			</table>
			</center>';			
		     $message .= '</body></html>';
	             if(mail($toEmail , $subject, $message, $headers)){
			 // $success  = "User has registered in Police99 websit";				
					}  
		     $alertresult= '<div class="alert alert-success"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
<strong>SUCCESS! </strong>New password sent to your email</div>';						  		    
			// header("location:newuser_relog.php");			            
		       // echo" email send";
		        }else{
		     $alertresult='<div class="alert alert-danger"><a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
<strong>ALERT! </strong>Entered email not yet registered with us or not activated still !!</div>';	         
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
<meta name="keywords" content="vehicle tracking system with GPS devices and car insurance facility in India">
    <!-- modal Bootstrap -->
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	
    <!-- Loading Bootstrap -->
    <link href="./dist/css/vendor/bootstrap.min.css" rel="stylesheet">

    <!-- Loading Flat UI -->
    <link href="./dist/css/flat-ui.css" rel="stylesheet">
    <!--link href="docs/assets/css/demo.css" rel="stylesheet"-->
    <link href="./dist/css/custom.css" rel="stylesheet">



    <link rel="shortcut icon" href="./dist/img/favicon.ico">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
    <!--[if lt IE 9]-->
      <script src="./dist/js/vendor/html5shiv.js"></script>
      <script src="./dist/js/vendor/respond.min.js"></script>
<!-- javascript for internal use-->

  </head>

  <body>

    <!-- Static navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
          </button>
          <a class="navbar-brand" href="index.php">TRACKEASY</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <!--li><a href="#newcarslaunch">New Cars Launched</a></li>
            <li><a href="#newbykelaunch">New Bykes Launched</a></li-->
          </ul>
        </div><!--/.navbar-collapse -->

      </div>
    </div>

<!--putting up the product video over here-->
 <br></br>
 <p></p>
    <div class="container"> 
			<?php if(isset($alertresult)) { ?>
		<div class="message"><?php echo $alertresult; ?></div>
		<?php } ?>
	
	
    <p class="text-danger" align="center"><em>Either username or password entered is incorrect. Please try again.</em><br>or<br>you might not activated your account through email !!</p>
   <!--sign in for purchase-->

        <div class="col-lg-6 col-lg-offset-3">
          <p><em>Re-Sign in</em></p>
          <div class="tile">
          <h3 class="tile-title">Sign in</h3>
          <form role="form" method="post" action="logcheck.php">
            <div class="form-group">
             <input type="email" class="form-control" id="loguid" placeholder="Email" name="uid">
             <br>
             <input type="password" class="form-control" id="logpwd" placeholder="Enter Password" name="pwd">
          </div>
            <button type="submit" class="btn btn-danger" name="signin">Sign In</button>
		      
         </form>
          <p><a class="forgot-password" data-toggle="modal" href="#myModal">Forgot Password?</a></p>
          </div>
        </div> <!-- /Buy -->


         <!-- /Buy -->	
       		       	
     </div><!--container-->

     <?php include("footer.html");?>
       <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <p class="modal-title font-for-forgot">Please enter your registered email</p>
        </div>
        <form method="post" id="submit-email" data-toggle="validator" action="">
			<div class="modal-body">        
			<div class="form-group-email">
			<label class="email-input-verification"for="inputName" name="remail">Email</label>
			<input type="email" class="form-control"  name="remail" id="inputName" required>
			</div>
							   
			</div>
			<div class="modal-footer">		                    
			<button type="button"  class="btn btn-danger model-bt" data-dismiss="modal">Cancel</button>
			<button type="submit" id="forgot-send" name="submit" class="btn btn-success model-bt">Submit</button>
			</div>
		</form>
      </div>
      
    </div>
  </div>

 
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="./dist/js/vendor/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="./dist/js/flat-ui.min.js"></script>

    <script src="./docs/assets/js/application.js"></script>
    <script src="dist/js/vendor/video.js"></script>


    <!--<script>
      var dt = new Date();
      document.getElementById("sysdate").innerHTML = dt.toDateString();
    </script>-->

    <script src="./dist/js/vendor/jquery.validate.min.js"></script>

    <script>
    jQuery.validator.setDefaults({
      //debug: true,
      success: "valid"
    });
    
    $( "#regform" ).validate({
      rules: {
        RNAME: "required",
        EMAIL1: "required",
        PASSWORDR: "required",
        PHONENUMBER: "required",
        EMAIL2: {
          equalTo: "#emailidR"
        }
      }
    });
    </script>    
  </body>
  <style>
 .font-for-forgot{  font-size: 18px;
      font-family: inherit;
      font-weight: 500;
      line-height: 1.1;
      color: inherit;
	}
.model-bt{
	padding: 6px 12px;
	}
.custom-close-bnt{
   margin-top: 2%;
   margin-right: 1%;
    }
.form-group-email{
    position: relative;
    margin-bottom: 15px;
	}
.forgot-password{
  font-size:18px;
}	
	
.email-input-verification{
   display: inline-block;
    max-width: 100%;
    margin-bottom: 5px;
    font-weight: 700;
    font-size: 14px;
    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
		}
</style>
</html> 