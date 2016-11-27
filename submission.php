<?php

function new_upload($image, 
					$plate, 
					$state,
					$date_occurrence,
					$date_added, 
					$gps_latitude, 
					$gps_longitude, 
					$street1, 
					$street2,
					$council_district,
					$precinct,
					$community_board,
					$description)
{
	require 'admin/config_pointer.php';
	date_default_timezone_set('UTC');
	
	error_log('in submission.new_upload()');
	
	//error_log('submission.php - $date_occurrence inbound: ' . $date_occurrence);
	//error_log('submission.php - $date_added inbound: ' . $date_added);
	//error_log('new upload: ' . $council_district . ' / ' . $precinct . ' / ' . $community_board);
	
	if (empty($image))
	{ return array('error' => 'Submissions without an image attached are currently not accepted.'); }

	if (!getimagesize($image['tmp_name']))
	{ return array('error' => 'Image attachment must be an image file.'); }

	if (empty($plate))
	{ return array('error' => 'Plate field cannot be empty.'); }

	if (!is_string($plate))
	{ return array('error' => 'Plate field must be a string'); }

	$plate = preg_replace("/[^a-zA-Z0-9]+/", "", $plate); //a-z, A-Z, 0-9 only
	$plate = substr($plate, 0, 8); //Max plate length 8 characters

	if (empty($state))
	{ return array('error' => 'State field cannot be empty.'); }
	
	if (!is_string($state))
	{ return array('error' => 'State field must be a string'); }

	$occurrence_check = new DateTime($date_occurrence);
	if (!$occurrence_check){
		return array('error' => 'Did not understand the date of occurrence value ' . $date_occurrence . '. Dates must be valid ISO8601 strings.');
	}
	$added_check = new DateTime($date_added);
	if (!$added_check){
		return array('error' => 'Did not understand the date added value ' . $date_added . '. Dates must be valid ISO8601 strings.');
	}
	
	//if (!is_timestamp($date_occurrence))
	//{ $date_occurrence = strtotime($date_occurrence); }
	//if (!$date_occurrence)
	//{ return array('error' => 'Unacceptable date value.'); }
	//$date_occurrence = date('Y-m-d H:i:s', $occurrence_check);
	//$date_added = date('Y-m-d H:i:s', $added_check);
	
	//error_log('submission.php - $date_occurrence conversion: ' . $date_occurrence);
	//error_log('submission.php - $date_added conversion: ' . $date_added);
	
	//error_log($date_occurrence);

	if (empty($gps_latitude) || empty($gps_longitude))
	{ return array('error' => 'Both latitude and longitude are required.'); }

	if (!is_numeric($gps_latitude) || !is_numeric($gps_longitude))
	{ return array('error' => 'GPS values must be numeric.'); }
	
	//Optional fields
	if ($street1){
		if (!is_string($street1))
		{ return array('error' => 'Street fields must be strings.'); }
	}
	if ($street2){
		if (!is_string($street2))
		{ return array('error' => 'Street fields must be strings.'); }
	}
	
	//error_log(is_int($council_district));
	
	//if (!is_int($council_district) && $council_district != '')
	//{ return array('error' => 'Council district must be a whole number.'); }

	//if (!is_int($precinct * 1) && $precinct != '')
	//{ return array('error' => 'Precinct must be a whole number.'); }

	if ($description){
		if (!is_string($description))
		{ return array('error' => 'Description field must be a string.'); }
	}
	
	//Validate GPS coordinates against project area
	if (!verify_coordinates($gps_latitude, $gps_longitude, $config)){
		return array('error' => 'GPS coordinates are not within the project zone. Latitude must be between ' 
		. $config['south_bounds'] . ' and ' . $config['north_bounds'] 
		. ', longitude must be between ' . $config['west_bounds'] . ' and ' . $config['east_bounds']);
	}
	
	//Get the next entry ID number
	$increment = get_increment($connection);
	if (!$increment){ 
		return array('error' => 'Server error, please alert the site administrator.');
	}
	
	//Create image directory, save out image and thumbnail
	$url = save_images($image, $increment);
	if (!$url){
		return array('error' => 'Server error, please alert the site administrator.');
	}
	
	if($stmt = $connection->prepare("INSERT INTO cibl_queue (
		increment, url, plate, state, date_occurrence, date_added, gps_lat, gps_long, street1, street2, council_district, precinct, community_board, description)
		VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")){
		$stmt->bind_param('isssssddssiiss', $increment, $url, $plate, $state, $date_occurrence, $date_added, $gps_latitude, $gps_longitude, $street1, $street2, $council_district, $precinct, $community_board, $description);
		$result = $stmt->execute();
	}
	else {
		$error = $mysqli->errno . ' ' . $mysqli->error;
		error_log($error);
	}
	error_log($result);
	$stmt->close();
	
	if (!$result) {
		error_log($connection->error);
		return array('error' => 'Server error, please alert the site administrator.');
	}
	
	$submission_details = array(
		'id' => $increment,
		'plate' => $plate,
		'state' => $state,
		'date_occurrence' => $date_occurrence,
		'date_added' => $date_added,
		'lat' => $gps_latitude,
		'lon' => $gps_longitude,
		'street1' => $street1,
		'street2' => $street2,
		'council_district' => $council_district,
		'precinct' => $precinct,
		'community_board' => $community_board,
		'description' => $description
	);
	$email_op = 'new_submission';
	include 'email_notify.php';
	
	return array('success' => 
		'Thank you for contributing! All submissions require moderator approval before being added to the map. Expect yours to show up within 24 hours.');
}

function verify_coordinates($gps_latitude, $gps_longitude, $config){
	if ( $gps_latitude > $config['north_bounds'] || 
		 $gps_latitude < $config['south_bounds'] || 
		 $gps_longitude > $config['east_bounds'] || 
		 $gps_longitude < $config['west_bounds'] ){
		return false;		
	}
	return true;
}

function get_increment($connection){
	try{
		$target_increment1 = mysqli_fetch_array(mysqli_query($connection, "SELECT MAX(increment) AS increment FROM cibl_data"))[0] + 1;
		$target_increment2 = mysqli_fetch_array(mysqli_query($connection, "SELECT MAX(increment) AS increment FROM cibl_queue"))[0] + 1;
		$target_increment = ($target_increment1 > $target_increment2) ? $target_increment1 : $target_increment2;
		return $target_increment;
	}
	catch (Exception $e){
		return false;
	}
}

function save_images($image, $increment){
	try{
		$now = getdate();
		//DETERMINE TARGET FILE NAME
		$target_dir = $now['year'] . "/" . $now['mon'] . "/" . $now['mday'] . "/";
		$target_extension = pathinfo(basename($image['name']), PATHINFO_EXTENSION);
		$url = $target_dir . "queue_" . $increment . "." . $target_extension;
		$target_image = __DIR__ . "/images/" . $url;
		$target_thumb = __DIR__ . "/thumbs/" . $url;
		//CREATE DIRECTORIES
		$image_dir = __DIR__ . "/images/" . $target_dir;
		$thumbs_dir = __DIR__ . "/thumbs/" . $target_dir;
		if (!is_dir($image_dir)){ mkdir($image_dir, 0777, true); }
		if (!is_dir($thumbs_dir)){ mkdir($thumbs_dir, 0777, true); }
		//RESIZE AND MOVE RENAMED IMAGE INTO PLACE
		$imagick = new Imagick($image['tmp_name']);
		$imagick->writeImage($target_image);
		$imagick->scaleImage(200, 200, true);
		$imagick->writeImage($target_thumb);
	}
	catch (Exception $e){ 
		return false; 
	}
	return $url;
}

function is_timestamp($timestamp) //thanks, stackoverflow... http://stackoverflow.com/questions/4123541/var-is-valid-unix-timestamp
{
   return ((string) (int) $timestamp === $timestamp)
   && ($timestamp <= PHP_INT_MAX)
   && ($timestamp >= ~PHP_INT_MAX)
   && (!strtotime($timestamp));
}

?>