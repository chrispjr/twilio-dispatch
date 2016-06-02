<?php

$body = $_REQUEST['body'];

if ($body == "pickup") {
	header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    ?>
	<Response>
	    <Message>What's your address?</Message>
	</Response>
    <?php } else {
	header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    ?>
    <Response>
        <Message><?php var_dump($_REQUEST); ?></Message>
    </Response>
    <?php
}

?>