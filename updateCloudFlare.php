<?php

/*
Author: Scott Helme
Site: https://scotthelme.co.uk
License: CC BY-SA 4.0
*/

// Check the calling client has our auth key. Insert your own random key here.
// Use this link to generate a key: http://scotthel.me/v1n0
// Key example: Kqt9TH4qBEOfNSGWfPM0
if(empty($_GET['auth']) || ($_GET['auth'] != "***Insert Random Key Here***")) die;

// Update these values with your own information.
$apiKey       = "CloudFlareApiKey";                         // Your CloudFlare API Key.
$myDomain     = "example.com";                              // Your domain name.
$ddnsAddress  = "ddns.example.com";                         // The subdomain you will be updating.
$emailAddress = "CloudFlareAccountEmailAddress";            // The email address of your CloudFlare account.

//These values do not need to be changed.
$ip           = $_SERVER['REMOTE_ADDR'];                    // The IP of the client calling the script.
$id           = 0;                                          // The CloudFlare ID of the subdomain, used later.
$url          = 'https://www.cloudflare.com/api_json.html'; // The URL for the CloudFlare API.

// Build the initial request to fetch the record ID.
// https://www.cloudflare.com/docs/client-api.html#s3.3
$fields = array(
	'a' => urlencode('rec_load_all'),
        'tkn' => urlencode($apiKey),
	'email' => urlencode($emailAddress),
	'z' => urlencode($myDomain)
);

$fields_string="";
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

// Send the request to the CloudFlare API.
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

// Extract the record ID for the subdomain we want to update.
$data = json_decode($result);
foreach($data->response->recs->objs as $rec){
	if($rec->name == $ddnsAddress){
		$id = $rec->rec_id;
		break;
	}
}

// Build the request to update the DNS record with our new IP.
// https://www.cloudflare.com/docs/client-api.html#s5.2
$fields = array(
	'a' => urlencode('rec_edit'),
        'tkn' => urlencode($apiKey),
	'id' => urlencode($id),
	'email' => urlencode($emailAddress),
	'z' => urlencode($myDomain),
	'type' => urlencode('A'),
	'name' => urlencode($ddnsAddress),
	'content' => urlencode($ip),
	'service_mode' => urlencode('0'),
	'ttl' => urlencode ('1')
);

$fields_string="";
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

// Send the request to the CloudFlare API.
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
