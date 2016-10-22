<?php

function new_upload($image, 
					$plate, 
					$state, 
					$time, 
					$gps_latitude, 
					$gps_longitude, 
					$street1, 
					$street2, 
					$description)
{
	require 'admin/config_pointer.php';

	//error_log($time);
	
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

	//Ensure unix timestamp	
	if (!is_timestamp($time))
	{ error_log('Trying to convert time string: ' . $time . ' to unix...'); $time = strtotime($time); error_log('And now its: ' . $time);}
	if (!$time)
	{ return array('error' => 'Unacceptable time value.'); }

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
	
	$stmt = $connection->prepare("INSERT INTO cibl_queue (
		increment, url, plate, state, date_occurrence, gps_lat, gps_long, street1, street2, description)
		VALUES (?,?,?,?,?,?,?,?,?,?)");
	$stmt->bind_param('isssiddsss', $increment, $url, $plate, $state, $time, $gps_latitude, $gps_longitude, $street1, $street2, $description);
	$result = $stmt->execute();
	$stmt->close();
	
	if (!$result) {
		error_log($connection->error);
		return array('error' => 'Server error, please alert the site administrator.');
	}
	
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