<!--
 * Copyright 2012 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Sample code for authenticating to Gmail with OAuth2. See
 * https://code.google.com/p/google-mail-oauth2-tools/wiki/PhpSampleCode
 * for documentation.
 * -->
<html>
<head>
  <title>OAuth2 IMAP example with Gmail</title>
</head>
<body>

<?php
// require_once 'Zend/Mail/Protocol/Imap.php';
// require_once 'Zend/Mail/Storage/Imap.php';

/**
 * Builds an OAuth2 authentication string for the given email address and access
 * token.
 */
function constructAuthString($email, $accessToken) {
  return base64_encode("user=$email\1auth=Bearer $accessToken\1\1");
}

/**
 * Given an open IMAP connection, attempts to authenticate with OAuth2.
 *
 * $imap is an open IMAP connection.
 * $email is a Gmail address.
 * $accessToken is a valid OAuth 2.0 access token for the given email address.
 *
 * Returns true on successful authentication, false otherwise.
 */
function oauth2Authenticate($imap, $email, $accessToken) {
  $authenticateParams = array('XOAUTH2',
      constructAuthString($email, $accessToken));
  $imap->sendRequest('AUTHENTICATE', $authenticateParams);

  while (true) {
    $response = "";
    $is_plus = $imap->readLine($response, '+', true);
    if ($is_plus) {
      error_log("got an extra server challenge: $response");
      // Send empty client response.
      $imap->sendRequest('');
    } else {
      if (preg_match('/^NO /i', $response) ||
          preg_match('/^BAD /i', $response)) {
        error_log("got failure response: $response");
        return false;
      } else if (preg_match("/^OK /i", $response)) {
        return true;
      } else {
        // Some untagged response, such as CAPABILITY
      }
    }
  }
}

function refreshAccessToken()
{
	$refresh_token = "1/-P2gMyEYU2VB8PZgIaAkZKpA6YMGUt1hmbeXbdk5JVs";
	$client_id = '377216642309-nr2p87j4dedsct4ehbgm1qg1gcfecfoq.apps.googleusercontent.com';
	$client_secret = 'LqybEsT3nzxQbvRQgUZy4EPo';
	$token_provider = 'https://accounts.google.com/o/oauth2/token';
	
	$fields = array(
						'refresh_token' => urlencode($refresh_token),
						'client_id' => urlencode($client_id),
						'client_secret' => urlencode($client_secret),
						'grant_type' => 'refresh_token',
				);


	//url-ify the data for the POST
	 $fields_string = "";
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	//open connection
	$ch = curl_init();

	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, $token_provider);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

	//execute post
	$result = curl_exec($ch);
	curl_close($ch);

	$res_obj = json_decode($result);
	
	
	return $res_obj->access_token;
}

/**
 * Given an open and authenticated IMAP connection, displays some basic info
 * about the INBOX folder.
 */
function showInbox($inbox) {
  /**
   * Print the INBOX message count and the subject of all messages
   * in the INBOX
   */
  //$storage = new Zend_Mail_Storage_Imap($imap);
  //$emails = imap_search($imap,'RECENT');
 /* grab emails */
  $emails = imap_search($inbox,'UNSEEN');
  var_dump($emails);
}
  //include 'header.php';
  //echo '<h1>Total messages: ' . $storage->countMessages() . "</h1>\n";

  /**
   * Retrieve first 5 messages.  If retrieving more, you'll want
   * to directly use Zend_Mail_Protocol_Imap and do a batch retrieval,
   * plus retrieve only the headers
   */
  // echo 'First five messages:';
  // for ($i = $storage->countMessages() ; $i >= $storage->countMessages() - 5 && $i ; $i-- ){
  // 	echo '<ul>';
  	
  // 		echo '<li>' . json_encode($storage->getMessage($i)->getFlags()) . "</li>\n";
  	
  // 	echo '</ul>';
  // 	echo '</br><-- End Msg: $i';
    
  //}
  
//}

/**
 * Tries to login to IMAP and show inbox stats.
 */
// function tryImapLogin($email, $accessToken) {
//   /**
//    * Make the IMAP connection and send the auth request
//    */
//   // $imap = new Zend_Mail_Protocol_Imap('imap.gmail.com', '993', true);
//   // if (oauth2Authenticate($imap, $email, $accessToken)) {
//   //   echo '<h1>Successfully authenticated!</h1>';
//   //   showInbox($imap);
//   // } else {
//   //   echo '<h1>Failed to login</h1>';
//   // }
// /* try to connect */
// $inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: '
// }

function tryImapLogin() {
  $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
  $username = 'ed@rentcollectors.ie';
  $password = '3498uzma!';

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ');
showInbox($inbox );
}

/**
 * Displays a form to collect the email address and access token.
 */
function displayForm($email, $accessToken) {
  echo <<<END
<form method="POST" action="oauth2.php">
  <h1>Please enter your e-mail address: </h1>
  <input type="text" name="email" value="$email"/>
  <p>
  <h1>Please enter your access token: </h1>
  <input type="text" name="access_token" value="$accessToken"/>
  <input type="submit"/>
</form>
<hr>
END;
}
tryImapLogin();
// $email = $_POST['email'];
// //$accessToken = refreshAccessToken();

// displayForm($email, $accessToken);

// if ($email && $accessToken) {
//   tryImapLogin($email, $accessToken);
// }


?>
</body>
</html>
