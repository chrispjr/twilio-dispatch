<?php

$body = $_REQUEST['Body'];

if ($body == "pickup") {
		header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>
		<Response>
		    <Message>What's your address?</Message>
		</Response>
    <?php } elseif($body == "debug") {
		header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>
	    <Response>
	        <Message><?php var_dump($_REQUEST); ?></Message>
	    </Response>
    <?php } else {
    	$url = "https://api.smartystreets.com/street-address";
    	$ss['auth_id'] = "f0630cb2-1a6e-a4c9-df41-2d1192123666";
    	$ss['auth_token'] = "eRQJMxOomkO0ksHbLPNS";
    	$ss['street'] = urlencode($body);
    	$ss['candidates'] = "10";
    	$ss_query = http_build_query($ss);
    	// $ch = curl_init();
    	// $ss_options = array(
    	// 	CURLOPT_URL => $url,
    	// 	CURLOPT_POST => false,
    	// 	CURLOPT_POSTFIELDS => $ss_query,
    	// 	CURLOPT_RETURNTRANSFER => true
    	// );
    	// curl_setopt_array($ch, $ss_options);
    	// $ss_results = curl_exec($ch);
    	// curl_close($ch);
    	header("content-type: text/xml");
	    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	    ?>

	    <Response>
	        <Message><?php var_dump($ss_query); ?></Message>
	    </Response>

	    <?php
    }

?>