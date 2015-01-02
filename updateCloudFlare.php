<?php

// A key can be any alphanumeric string.
// Insert the appropriate "key" => "hostname" values below.
$hosts = array(
	"***Insert Random Key1 Here***" => "ddns1",
	"***Insert Random Key2 Here***" => "ddns2",
	"***Insert Random Key3 Here***" => "ddns3",
	"***Insert Random Keyn Here***" => "ddnsn"
);

// Check the calling client has a valid auth key.
if(empty($_GET['auth']) || (!array_key_exists($_GET['auth'], $hosts))) die;

// Update these values with your own information.
$apiKey		= "CloudFlareApiKey";				// Your CloudFlare API Key.
$myDomain	= "example.com";				// Your domain name.
$emailAddress	= "CloudFlareAccountEmailAddress";		// The email address of your CloudFlare account.

// These values do not need to be changed.
$hostname	= $hosts[$_GET['auth']];			// The hostname that will be updated.
$ddnsAddress	= $hostname.".".$myDomain;			// The fully qualified domain name.
$ip		= $_SERVER['REMOTE_ADDR'];			// The IP of the client calling the script.
$url		= 'https://www.cloudflare.com/api_json.html';	// The URL for the CloudFlare API.

// Sends request to CloudFlare and returns the response.
function send_request() {
	global $url, $fields;

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

	return $result;
}

// Determine protocol version and set record type.
if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
	$type = 'AAAA';
} else{
	$type = 'A';
}

// Build the initial request to fetch the record ID.
// https://www.cloudflare.com/docs/client-api.html#s3.3
$fields = array(
	'a' => urlencode('rec_load_all'),
	'tkn' => urlencode($apiKey),
	'email' => urlencode($emailAddress),
	'z' => urlencode($myDomain)
);

$result = send_request();

// Extract the record ID (if it exists) for the subdomain we want to update.
$rec_exists = False;						// Assume that the record doesn't exist.
$data = json_decode($result);
foreach($data->response->recs->objs as $rec){
	if(($rec->name == $ddnsAddress) && ($rec->type == $type)){
		$rec_exists = True;				// If this runs, it means that the record exists.
		$id = $rec->rec_id;
		$cfIP = $rec->content;				// The IP Cloudflare has for the subdomain.
		break;
	}
}

// Create a new record if it doesn't exist
if(!$rec_exists){
	// Build the request to create a new DNS record.
	// https://www.cloudflare.com/docs/client-api.html#s5.1
	$fields = array(
		'a' => urlencode('rec_new'),
		'tkn' => urlencode($apiKey),
		'email' => urlencode($emailAddress),
		'z' => urlencode($myDomain),
		'type' => urlencode($type),
		'name' => urlencode($hostname),
		'content' => urlencode($ip),
		'ttl' => urlencode ('1')
	);

	$result = send_request();
	
// Only update the entry if the IP addresses do not match.
} elseif($ip != $cfIP){
	// Build the request to update the DNS record with our new IP.
	// https://www.cloudflare.com/docs/client-api.html#s5.2
	$fields = array(
		'a' => urlencode('rec_edit'),
		'tkn' => urlencode($apiKey),
		'id' => urlencode($id),
		'email' => urlencode($emailAddress),
		'z' => urlencode($myDomain),
		'type' => urlencode($type),
		'name' => urlencode($hostname),
		'content' => urlencode($ip),
		'service_mode' => urlencode('0'),
		'ttl' => urlencode ('1')
	);

	$result = send_request();
}
