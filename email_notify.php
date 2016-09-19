<?php
$subject = 'New submissision to ' . $config['site_name'] . ' awaiting approval';
$message = "Submission #" . $submission_details['id'] . " is awaiting moderation." .
			"\nPlate: " . $submission_details['plate'] .
			"\nState: " . $submission_details['state'] .
			"\nTime: " . $submission_details['date'];
			if($submission_details['street1'] != ''){ $message .= "\nStreets: " . $submission_details['street1'] . " & " . $submission_details['street2']; }
			$message .= "\nCoordinates: " . $submission_details['lat'] . ", " . $submission_details['lon'] .
			"\nDescription: " . $submission_details['description'] .
			"\n\n" .
			"Moderate: http://" . $_SERVER['SERVER_NAME'] . "/admin";
$query = 'SELECT email FROM cibl_users WHERE email IS NOT null';
$result = $connection->query($query);
while($address = mysqli_fetch_row($result)){
	$status = mail($address[0], $subject, $message, 'From: ' . $config['site_name'] . '<noreply@' . $_SERVER['SERVER_NAME'] . '>');
}
?>