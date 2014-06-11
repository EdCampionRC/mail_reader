<?php
require 'PHPMailer/class.phpmailer.php';
$mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->IsSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug  = 0;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
$mail->Host       = 'smtp.gmail.com';
$mail->Port       = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth   = true;
$mail->Username   = "ed@rentcollectors.ie";
$mail->Password   = "3498uzma!";
$mail->SetFrom('ed@rentcollectors.ie', 'Ed Campion');
$mail->Subject = 'Tenant Enquiry';
$mail->AddAddress("ed@rentcollectors.ie", 'Ed Campion');

$server = "rvproduction.coomapi9xlts.eu-west-1.rds.amazonaws.com";
$user = "rvproduser";
$pass = "rntPRD*5-vw";
$db = "rentview_production";

$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	$data = file_get_contents('php://input');
    $post_data =  json_decode($data);
	$prop_link_beg = strpos($post_data->tenant_message ,"Link:");
	$prop_link_beg += 25;//Link: www.daft.ie/id/21451728 only extract digits
    $prop_link_end = strpos($post_data->tenant_message,"\n",$prop_link_beg);
    $prop_link = substr($post_data->tenant_message ,$prop_link_beg,($prop_link_end - $prop_link_beg));
    $prop_link = trim($prop_link);
	$msg = "Tenant Email: ".$post_data->tenant_email . " / ".
    "Property: ". $prop_link. "\nPlease fill out your profile";
   
    if(count($prop_link) > 0)
    {
	   	$con = mysqli_connect($server, $user, $pass, $db);
	    $result = mysqli_query($con, "SELECT a.name,a.email FROM agency a
	    INNER JOIN tenant_search_property tsp ON tsp.agency_id = a.id
	    WHERE tsp.daft_url LIKE '%".substr($prop_link,1)."%'");
	     
	     while($row = mysqli_fetch_array($result))
	    {
	      $msg .= "\nAgency: ".$row["name"] ."\nAgency Mail: " .$row["email"]  ;
	    }

	     $mail->Body= $msg;

	    if(!$mail->Send()) {
	      //echo "Mailer Error: " . $mail->ErrorInfo;
	    }
	    else {
	      //echo "Message sent!";
	      $success = true;
	    }
	    mysqli_close($con);
	}
}
	
if($success)
{
header('HTTP/1.1 200 OK', true, 200);
}

else
{
header('HTTP/1.1 500 Server Error', true, 500);
}
?>

