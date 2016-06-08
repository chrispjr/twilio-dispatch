<?php

//header("content-type: text/xml");


function ss_validate_address() {
	$street = $_REQUEST['Body'];
	$street = urlencode($street);
	$city = $_REQUEST['FromCity'];
	$state = $_REQUEST['FromState'];
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

	$prompt_2 = "I'm sorry. We could not match the information you supplied with a valid address. Please try again.";

	$prompt_3 = "Please confirm that your pickup address is: \n";

	$prompt_unrecognizedInput = "Your input is not recognized as an address. Please reply the street address where you would like to be picked up. Example: \nExample: 1500 W Baltimore St";

	// $userResponse_2b = build_confirm_message();

	// $userResponse_3 = "Thank you. We'll text you once we've booked you a ride. Thanks for using MyRide!";

	$TwiMLResponse = "Undefined Response";

	if (!isset($_COOKIE["userResponse_1"])) {
		
		$TwiMLResponse = $prompt_1;

		setcookie("initiation", $_REQUEST["Body"]);
		setcookie("userResponse_1", "nil");
		setcookie("userResponse_2", "nil");
		setcookie("userResponse_3", "nil");

	}

	elseif ($_COOKIE["userResponse_1"] == "nil") {

		// userResponse: Hello! What's your address? No city or state, please. \nExample: 1500 W Baltimore St

		// what was the question we asked?

		$previoususerResponse = $_COOKIE['initiation'];

		// what was the userResponse?

		$userResponse = $_REQUEST["Body"];

		setcookie("userResponse_1", $userResponse);

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
				
				setcookie("userResponse_1", "nil");

				$TwiMLResponse = $prompt_2;

			} elseif ($countValidatedAddress === 1) {

				$street = $validatedAddress[0]->delivery_line_1;

				$city = $validatedAddress[0]->last_line;

				// $TwiMLResponse = "Please confirm that your pickup address is:\n $street\n $city";

				$TwiMLResponse = $prompt_3."$street\n $city";

			} elseif($countValidatedAddress > 1) {

				setcookie("validatedAddress", $validatedAddress);

				$TwiMLResponse = "We found more than one address matching the information you supplied.\n";

					$i = 1;

					foreach ($validatedAddress as $address) {

						$street = $address->delivery_line_1;

						$city = $address->last_line;
						
						$TwiMLResponse .= "Reply \"$i\" to select\n";

						$TwiMLResponse .= "$i: $street $city\n";

						$i++;

					}

			} else {

				// otherwise, respond letting the user know their response was unaccepted, reiterate the expected responses, and reset the current cookie

				setcookie("userResponse_1", "nil");

				$TwiMLResponse = $prompt_unrecognizedInput;

			}

		}

		else {

			// otherwise, respond letting the user know their response was unaccepted, reiterate the expected responses, and reset the current cookie

			setcookie("userResponse_1", "nil");

			$TwiMLResponse = $prompt_unrecognizedInput;

		}

	}

	elseif ($_COOKIE["userResponse_2"] == "nil") {


		setcookie("userResponse_2", $userResponse);

		if (isset($_COOKIE['validatedAddress']) && !empty($_COOKIE['validatedAddress'])) {

			$validatedAddress = $_COOKIE['validatedAddress'];

		}

		// what was the previous prompt?

		$previousPrompt = $_COOKIE['TwiMLResponse'];

		// what was the userResponse?

		$userResponse = $_REQUEST["Body"];

		// was userResponse an expected response?

		function isExpectedResponse($userResponse) {

			// regex check

		}

		// Expected responses:

		// Yes

		// No

		// 1-10

	}

	setcookie("TwiMLResponse", $TwiMLResponse);

	return $TwiMLResponse;

}

$TwiMLResponse = pickup_conversation();

?>
<Response>
    <Message><?php echo $TwiMLResponse; ?></Message>
</Response>