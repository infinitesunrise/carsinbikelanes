<?php
if ($email_op == 'new_submission'){
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
	$query = 'SELECT email, submit_notify FROM cibl_users WHERE email IS NOT null AND submit_notify=1 ';
	$result = $connection->query($query);
	while($address = mysqli_fetch_row($result)){
		$status = mail($address[0], $subject, $message, 'From: ' . $config['site_name'] . '<noreply@' . $_SERVER['SERVER_NAME'] . '>');
	}
}
if ($email_op == 'new_user'){
	$subject = 'Welcome to the moderation team at ' . $config['site_name'];
	$message = "Hi! User " . $_SESSION['username'] . " has just created a moderation account for you at " . $_SERVER['SERVER_NAME'] . ".\n" .
				"Log in at " . $_SERVER['SERVER_NAME'] . "/admin with the following credentials: \n" .
				"\n" .
				"username: " . $newusername . "\n" .
				"password: " . $password . "\n" .
				"\n" .
				"Happy moderating!";
	$sent = mail($email, $subject, $message, 'From: ' . $config['site_name'] . '<noreply@' . $_SERVER['SERVER_NAME'] . '>');
}
if ($email_op == 'edit_email'){
	$subject = 'Your email address for ' . $config['site_name'] . ' has been updated';
	$message = "This email is to confirm that your address on record at " . $_SERVER['SERVER_NAME'] . " has just been updated. \n" .
				"That is all!";
	$sent = mail($email, $subject, $message, 'From: ' . $config['site_name'] . '<noreply@' . $_SERVER['SERVER_NAME'] . '>');
}
?>