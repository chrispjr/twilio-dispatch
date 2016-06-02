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
    	curl -v 'https://api.smartystreets.com/street-address?auth-id=f0630cb2-1a6e-a4c9-df41-2d1192123666&auth-token=eRQJMxOomkO0ksHbLPNS&street='.$body.'&candidates=10';
    }

?>