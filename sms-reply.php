<?php

$body = $_REQUEST['Body'];

function send_validation_response($ss_results) {
	
	// if (count($ss_results) === 1) {
	// 	$street = $ss_results[0]->delivery_line_1;
	// 	$city = $ss_results[0]->last_line;
	// 	echo "Please confirm that your pickup address is:\n $street\n $city";
	// }
	
	$i = 1;

	foreach ($ss_results as $ss_result) {

		$street = $ss_result->delivery_line_1;

		$city = $ss_result->last_line;

		$addresses[$i] = "$street\n $city";

		$i++;

	}

	echo build_confirm_message($addresses);
}

function build_confirm_message($addresses) {

	$response = "We found more than one address matching the information you supplied.\n";

	$i = 1;

	foreach ($addresses as $address) {
		
		$response += "Reply \"$i\" to select:\n";

		$response += "$i: $address\n";

		$i++;

	}

	return $response;

}

function debug($request, $addresses) {
	

	$response = "Debug output:\n";

	if (!empty($request)) {

		$response += "var_dump(\$request):\n";

		$response += var_dump($request);

	}
}

	if ($body == "pickup") {
		header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>
		<Response>
		    <Message>What's your address? No city or state, please. Example: 1500 W Baltimore St</Message>
		</Response>
    <?php } elseif($body == "debug") {
		header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>
	    <Response>
	        <Message><?php var_dump($_REQUEST); ?></Message>
	    </Response>
    <?php } else {
    	header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>

	    <Response>
	        <Message>Debug:
	        	<?php 
	        	$street = $_REQUEST['Body'];
	        	$street = urlencode($street);
	        	$city = $_REQUEST['FromCity'];
	        	$state = $_REQUEST['FromState'];

	        	// $ss['auth_id'] = "f0630cb2-1a6e-a4c9-df41-2d1192123666";
	        	// $ss['auth_token'] = "eRQJMxOomkO0ksHbLPNS";
	        	// $ss['street'] = urlencode($body);
	        	// $ss['candidates'] = "10";
	        	// $ss_query = http_build_query($ss);
	        	// $url = "https://api.smartystreets.com/street-address?$ss_query";
	        	// $url = "https://api.smartystreets.com/street-address?auth-id=f0630cb2-1a6e-a4c9-df41-2d1192123666&auth-token=eRQJMxOomkO0ksHbLPNS&street=".$street."&city=".$city."&state=".$state;
	        	$url = "https://api.smartystreets.com/street-address?auth-id=f0630cb2-1a6e-a4c9-df41-2d1192123666&auth-token=eRQJMxOomkO0ksHbLPNS&street=$street&city=$city&state=$state";
	        	$ch = curl_init();
	        	$ss_options = array(
	        		CURLOPT_URL => $url,
	        		CURLOPT_POST => false,
	        		CURLOPT_RETURNTRANSFER => true
	        	);
	        	curl_setopt_array($ch, $ss_options);
	        	$ss_results = curl_exec($ch);
	        	curl_close($ch);
	        	$ss_results = json_decode($ss_results);
	        	// var_dump($ss_results);
	        	send_validation_response($ss_results);
	        	?>
	        </Message>
	    </Response>

	    <?php
    }

?>