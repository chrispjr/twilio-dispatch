<?php

$body = $_REQUEST['Body'];

$ss['auth_id'] = "f0630cb2-1a6e-a4c9-df41-2d1192123666";
$ss['auth_token'] = "eRQJMxOomkO0ksHbLPNS";
$ss['street'] = urlencode($body);
$ss['candidates'] = "10";
$ss_query = http_build_query($ss);
$url = "https://api.smartystreets.com/street-address?$ss_query";
$ch = curl_init();
$ss_options = array(
	CURLOPT_URL => $url,
	CURLOPT_POST => false,
	CURLOPT_RETURNTRANSFER => true
);
curl_setopt_array($ch, $ss_options);
$ss_results = curl_exec($ch);
curl_close($ch);
var_dump($ss_results); 

?>