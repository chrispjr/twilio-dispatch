<?php

// header("content-type: text/xml");
// 
 
$prompt_1 = "Hello! What's your address? No city or state, please. \nExample: 1500 W Baltimore St";

$prompt_2 = "I'm sorry. We could not match the information you supplied with a valid address. Please try again.";

$prompt_3 = "Please confirm that your pickup address is: \n";

$prompt_unrecognizedInput = "Your input is not recognized as an address. Please reply the street address where you would like to be picked up. Example: \nExample: 1500 W Baltimore St";

// $userResponse_2b = build_confirm_message();

// $userResponse_3 = "Thank you. We'll text you once we've booked you a ride. Thanks for using MyRide!";

$TwiMLResponse = "Undefined Response";

function cookie_reset_to_nil($cookie_name) {
	unset($cookie_name);
}

function cookie_set_to_nil($cookie_name) {
	setcookie($cookie_name, "nil");
}

function cookie_remove($cookie_name) {
    unset($_COOKIE[$cookie_name]);
    setcookie($cookie_name, '', time() - 3600, '/'); // empty value and old timestamp	
}

function cookie_destroy_all() {
	if (isset($_SERVER['HTTP_COOKIE'])) {
	    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
	    foreach($cookies as $cookie) {
	        $parts = explode('=', $cookie);
	        $name = trim($parts[0]);
	        setcookie($name, '', time()-1000);
	        setcookie($name, '', time()-1000, '/');
	    }
	}
}

function safe_serialize($string_to_serialize) {

	$serialized_string = base64_encode(serialize($string_to_serialize));

	return $serialized_string;

}

function safe_unserialize($string_to_unserialize) {

	$unserialized_string = unserialize(base64_decode($string_to_unserialize));

	return $unserialized_string;

}

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

function conversation_one() {

	$prompt_1 = "Hello! What's your address? No city or state, please. \nExample: 1500 W Baltimore St";

	$TwiMLResponse = $prompt_1;

	setcookie("initiation", $_REQUEST["Body"]);
	cookie_set_to_nil("userResponse_1");
	cookie_set_to_nil("userResponse_2");
	cookie_set_to_nil("userResponse_3");

	return $TwiMLResponse;

}

function conversation_two() {

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
			
			cookie_reset_to_nil("userResponse_1");

			$prompt_2 = "I'm sorry. We could not match the information you supplied with a valid address. Please try again.";

			$TwiMLResponse = $prompt_2;

		} elseif ($countValidatedAddress === 1) {

			$street = $validatedAddress[0]->delivery_line_1;

			$city = $validatedAddress[0]->last_line;

			$compiledValidAddress = array($street, $city);

			$serializedValidatedAddress = safe_serialize($compiledValidAddress);

			setcookie("serializedValidatedAddress", $serializedValidatedAddress);

			// $TwiMLResponse = "Please confirm that your pickup address is:\n $street\n $city";

			$prompt_3 = "Please confirm that your pickup address is: \n";

			$TwiMLResponse = $prompt_3."$street\n $city";

		} elseif($countValidatedAddress > 1) {

			$TwiMLResponse = "We found more than one address matching the information you supplied.\n";

				$i = 0;

				foreach ($validatedAddress as $address) {

					$street = $address->delivery_line_1;

					$city = $address->last_line;

					$compiledValidAddress[$i]['street'] = $street;

					$compiledValidAddress[$i]['city'] = $city;

					$i++;

				}

				$i = 1;

				foreach ($compiledValidAddress as $address) {
					
					$TwiMLResponse .= "Reply \"$i\" to select\n";

					$TwiMLResponse .= "$i: ".$address['street'].", ".$address['city']."\n";

					$i++;

				}

				$serializedValidatedAddress = safe_serialize($compiledValidAddress);

				setcookie("multiple_addresses", 1);

				setcookie("serializedValidatedAddress", $serializedValidatedAddress);

		} else {

			// otherwise, respond letting the user know their response was unaccepted, reiterate the expected responses, and reset the current cookie

			cookie_reset_to_nil("userResponse_1");

			$TwiMLResponse = $prompt_unrecognizedInput;

		}

	}

	else {

		// otherwise, respond letting the user know their response was unaccepted, reiterate the expected responses, and reset the current cookie

		cookie_reset_to_nil("userResponse_1");

		$TwiMLResponse = $prompt_unrecognizedInput;

	}

	return $TwiMLResponse;

}

function conversation_three() {

	// what was the previous prompt?

	$previousPrompt = $_COOKIE['TwiMLResponse'];

	// what was the userResponse?

	$userResponse = $_REQUEST["Body"];

	setcookie("userResponse_2", $userResponse);

	// was userResponse an expected response?

	function isExpectedResponse($userResponse) {

		// regex check
		
		$regex_yes_response = '/yes/i';

		$regex_no_response = '/no/i';

		$yes_response = preg_match($regex_yes_response, $userResponse);

		$no_response = preg_match($regex_yes_response, $userResponse);
		
		if ($yes_response) {
			
			return true;

		} elseif ($no_response) {

			return true;

		} else {

			return false;

		}

	}

	if (is_expected_response($userResponse)) {
		
		$prompt_4 = "Thanks. We’ll text you when a driver is on the way. You can reply \"Cancel\" at any time to cancel your request.";

		$TwiMLResponse = $prompt_4;
		
	}

	return $TwiMLResponse;

}

function conversation_three_multiple_addresses_one() {

	// what was the previous prompt?

	$previousPrompt = $_COOKIE['TwiMLResponse'];

	// what was the userResponse?

	$userResponse = $_REQUEST["Body"];

	setcookie("userResponse_2", $userResponse);

	// was userResponse an expected response?
	
	$expectedResponse = '/^[0-9- ]+$/';

	$isExpectedResponse = preg_match($expectedResponse, $userResponse);

	if ($isExpectedResponse) {

		if (isset($_COOKIE['serializedValidatedAddress']) && !empty($_COOKIE['serializedValidatedAddress'])) {

			$validatedAddress = unserialize($_COOKIE['serializedValidatedAddress']);

		}

		$TwiMLResponse = var_dump($validatedAddress);

	}

	return $TwiMLResponse;

}

function pickup_conversation() {

	if (!isset($_COOKIE["userResponse_1"])) {
		
		$TwiMLResponse = conversation_one();

	}

	elseif ($_COOKIE["userResponse_1"] == "nil") {

		$TwiMLResponse = conversation_two();

	}

	elseif ($_COOKIE["userResponse_2"] == "nil") {
		
		if ($_COOKIE["multiple_addresses"] === 1) {
			
			$TwiMLResponse = conversation_three_multiple_addresses_one(); 
			
		} else {

			$TwiMLResponse = conversation_three();

		}

	}

	setcookie("TwiMLResponse", $TwiMLResponse);

	return $TwiMLResponse;

}


function new_conversation() {

	$userResponse = $_REQUEST["Body"];

	$customerNumber = $_REQUEST['From'];

	if (isset($_COOKIE['serializedValidatedAddress']) && !empty($_COOKIE['serializedValidatedAddress'])) {

		$validatedAddress = safe_unserialize($_COOKIE['serializedValidatedAddress']);

	}

	$regex_address = '/^[a-z0-9- ]+$/i';
	$regex_pickup = '/pickup/i';

	if (preg_match("/reset/i", $userResponse)) {

		cookie_set_to_nil("initiation");
		cookie_set_to_nil("userResponse_1");
		cookie_set_to_nil("userResponse_2");
		cookie_set_to_nil("userResponse_3");
			
		cookie_remove("serializedValidatedAddress");
		cookie_remove("TwiMLResponse");
		cookie_remove("initiation");
		cookie_remove("userResponse_1");
		cookie_remove("userResponse_2");
		cookie_remove("userResponse_3");

		cookie_destroy_all();

		$TwiMLResponse = "Cookies reset";

	} elseif (preg_match($regex_address, $userResponse)) {
		
		$TwiMLResponse = pickup_conversation();

	}

	else {

		$TwiMLResponse = "Bad input";

	}

	return $TwiMLResponse;
}


$TwiMLResponse = new_conversation();

// function dispatchGetToken() {

// 	$url = "https://api-stg.dispatch.me/oauth/token?";

// 	$grant_type = "client_credentials";

// 	$client_id = "e458e4297864482bbd7cce0697d33770cbc17e10a19ab5d3a90005e0ee247154";

// 	$client_secret = "94611d4be20d70eaaca3f1fe6062b7ce135977662acf5e82163a48743226f4c4";

// 	$query_data_array = array(
// 		'grant_type' => $grant_type,
// 		'client_id' => $client_id,
// 		'client_secret' => $client_secret
// 	);

// 	$query_data = http_build_query($query_data_array);

// 	$url .= $query_data;

// 	$ch = curl_init();

// 	$ss_options = array(
// 		CURLOPT_URL => $url,
// 		CURLOPT_POST => true,
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded', 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5'),
// 	);

// 	curl_setopt_array($ch, $ss_options);

// 	$ss_results = curl_exec($ch);

// 	curl_close($ch);

// 	$ss_results = json_decode($ss_results);

// 	return $ss_results;

// 	// return $url;

// }

// function dispatchGetCustomer($customerNumber) {
	
	



// 	$clientID = "";

	

// 	$dispatch_options = array(
// 		CURLOPT_URL => $url,
// 		CURLOPT_POST => false,
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_HTTPHEADER => array('Content-Type: application/json','Accept: application/json')
// 	);
// 	curl_setopt_array($ch, $dispatch_options);

// }

?>
<Response>
    <Message><?php  echo $TwiMLResponse; ?></Message>
</Response>