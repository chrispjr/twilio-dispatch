<?php

function ss_validate_address() {
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
	$url = "https://api.smartystreets.com/street-address?auth-id=f0630cb2-1a6e-a4c9-df41-2d1192123666&auth-token=eRQJMxOomkO0ksHbLPNS&street=$street&city=$city&state=$state&candidates=10";
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
	return $ss_results;
}

function pickup_conversation() {

	$prompt_1 = "Hello! What's your address? No city or state, please. \nExample: 1500 W Baltimore St";

	// $prompt_2a = "Please reply \"Yes\" or \"No\". Is the address where you would like to be picked up? \n $street\n $city";

	// $prompt_2b = build_confirm_message();

	// $prompt_3 = "Thank you. We'll text you once we've booked you a ride. Thanks for using MyRide!";

	if (!isset($_COOKIE["prompt_1"])) {
		
		$TwiMLResponse = $prompt_1;

		setcookie("initiation", $_REQUEST["Body"]);
		setcookie("prompt_1", "nil");
		setcookie("prompt_2", "nil");
		setcookie("prompt_3", "nil");

	}

	elseif ($_COOKIE["prompt_1"] == "nil") {

		// Prompt: Hello! What's your address? No city or state, please. \nExample: 1500 W Baltimore St

		// what was the question we asked?

		$previousTwiMLResponse = $_COOKIE['initiation'];

		// what was the userResponse?

		$userResponse = $_REQUEST["Body"];

		setcookie("prompt_1", $userResponse);

		// what are the expected responses?

		$expectedResponse = '/^[a-z0-9- ]+$/i';

		// was the userResponse an expected user response (an address in this case)?

		$isExpectedResponse = preg_match($expectedResponse, $userResponse);

		// does it match a (quick, free) regex match? $add_check = ;

		if ($isExpectedResponse) {
			
			// if so, proceed to check against the smartystreets API

			$validatedAddress = ss_validate_address();

			$countValidatedAddress = count($validatedAddress);

			if ($countValidatedAddress === 0) {
				
				setcookie("prompt_1", "nil");

				$TwiMLResponse = "I'm sorry. We could not match the information you supplied with a valid address. Please try again.";

			} elseif ($countValidatedAddress === 1) {

				$street = $validatedAddress[0]->delivery_line_1;

				$city = $validatedAddress[0]->last_line;

				$TwiMLResponse = "Please confirm that your pickup address is:\n $street\n $city";

			} elseif($countValidatedAddress > 1) {

				$TwiMLResponse = "We found more than one address matching the information you supplied.\n";

					$i = 1;

					foreach ($addresses as $address) {

						var_dump("---ADD--");
						var_dump($address);


						foreach ($address as $key) {
							// var_dump("$key");
						}

						// var_dump($address);

						var_dump($address[0]);

						$street = $address['delivery_line_1'][0];

						$city = $address['last_line'][0];
						
						$TwiMLResponse .= "Reply \"$i\" to select\n";

						$TwiMLResponse .= "$i: $street $city\n";

						$i++;

					}

		} else {

			// otherwise, respond letting the user know their response was unaccepted, reiterate the expected responses, and reset the current cookie

			$TwiMLResponse = "Your input is not recognized as an address. Please reply the street address where you would like to be picked up. Example: \nExample: 1500 W Baltimore St";

		}


	}

	// return $TwiMLResponse;

}

header("content-type: text/xml");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

$TwiMLResponse = "pickup_conversation()";

?>
<Response>
    <Message><?php echo $TwiMLResponse; ?></Message>
</Response>