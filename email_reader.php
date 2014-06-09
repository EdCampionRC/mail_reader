<?php


// $server = "127.0.0.1";
// $user = "root";
// $pass = "root";
// $db = "wordpress";

if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
function showInbox($inbox) {
  /**
   * Print the INBOX message count and the subject of all messages
   * in the INBOX
   */
  //$storage = new Zend_Mail_Storage_Imap($imap);
  //$emails = imap_search($imap,'RECENT');
 /* grab emails */

require 'PHPMailer/class.phpmailer.php';
 $mail = new PHPMailer();
//Tell PHPMailer to use SMTP
$mail->IsSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug  = 2;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
$mail->Host       = 'smtp.gmail.com';
//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port       = 587;
//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'tls';
//Whether to use SMTP authentication
$mail->SMTPAuth   = true;
//Username to use for SMTP authentication - use full email address for gmail
$mail->Username   = "ed@rentcollectors.ie";
//Password to use for SMTP authentication
$mail->Password   = "3498uzma!";
//Set who the message is to be sent from
$mail->SetFrom('ed@rentcollectors.ie', 'Ed Campion');
//Set an alternative reply-to address
$mail->AddReplyTo('ed@rentcollectors.ie','Ed Campion');
//Set who the message is to be sent to
$mail->Subject = 'PHPMailer GMail SMTP test';
$server = "rvproduction.coomapi9xlts.eu-west-1.rds.amazonaws.com";
$user = "rvproduser";
$pass = "rntPRD*5-vw";
$db = "rentview_production";
$con = mysqli_connect($server, $user, $pass, $db);
//Set the subject line
  $emails = imap_search($inbox,'SUBJECT "Enquiry from Daft.ie" ');
  rsort($emails);
  $page_emails = $emails;
  $offset = 0;
  $page_emails = array_slice($emails,$offset,5);
  $output = "";
  echo '<ul>';
  foreach($page_emails as $email_number) {
    /* get information specific to this email */
    $overview = imap_fetch_overview($inbox,$email_number,0);
    $header = imap_headerinfo($inbox,$email_number);
    
    $tenant_mail_arr = $header->reply_to[0];
    $tenant_email = addslashes($tenant_mail_arr->mailbox).'@'.$tenant_mail_arr->host;
    $message = imap_fetchbody($inbox,$email_number,1,FT_PEEK);
    $prop_link_beg = strpos($message,"Link:");

    if($prop_link_beg)
    {
        $prop_link_beg += 25;//Link: www.daft.ie/id/
        $prop_link_end = strpos($message,PHP_EOL,$prop_link_beg);
        $prop_link = substr($message,$prop_link_beg,($prop_link_end - $prop_link_beg));
         echo '<li>'.$tenant_email . " / ".
        $prop_link.'</li>';
        $msg = "Tenant Email: ".$tenant_email . " / ".
        "Property: ". $prop_link. "Please fill out profile";
    
        $mail->AddAddress("ed@rentcollectors.ie", 'Ed Campion');
       
        $result = mysqli_query($con, "SELECT a.name,a.email FROM agency a
         INNER JOIN tenant_search_property tsp ON tsp.agency_id = a.id
         WHERE tsp.daft_url LIKE '%".substr($prop_link,1)."%'");
         while($row = mysqli_fetch_array($result))
        {
          $msg .= "Agency: ".$row["name"] ."\n" . "Agency Mail: " .$row["email"]  ;
        }

         $mail->Body= $msg;

        if(!$mail->Send()) {
          echo "Mailer Error: " . $mail->ErrorInfo;
        }
        else {
          echo "Message sent!";
        }
    }
     else
    {
      echo '<li>'.$tenant_email.'</li>';
    }
  }
   echo '</ul>';
  mysqli_close($con);
  //echo $output;
  // while(count($page_emails) > 0)
  // {
  //   $page_emails = array_slice($emails,$offset,50);
  //   $offset += count($page_emails);
  // }

}

function tryImapLogin() {
  $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
  $username = 'info@smithbutlerestates.com';
  $password = 'LettingsIreland1';

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ');
showInbox($inbox );
}


tryImapLogin();

