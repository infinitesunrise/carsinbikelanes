<?php

require 'admin/config_pointer.php';

//VERIFY ATTACHMENT
if (empty($_FILES["image_submission"]["name"])){
	error("noimage");
}

//CHECK IF ATTACHMENT IS AN IMAGE
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["image_submission"]["tmp_name"]);
    if($check !== false){
    	$target_extension = pathinfo(basename($_FILES["image_submission"]["name"]), PATHINFO_EXTENSION);
    	if($target_extension == "jpg" ||
    		$target_extension == "JPG" ||
    		$target_extension == "png" ||
    		$target_extension == "PNG" ||
    		$target_extension == "jpeg" ||
    		$target_extension == "JPEG" ||
    		$target_extension == "gif" ||
    		$target_extension == "GIF")
    	{	
        	echo "File is good: " . basename($_FILES["image_submission"]["name"]) . " (". $check["mime"] . "). <br>";
        }
		else
		{
    		error("badimage");
    	}     
    } else {
    	error("badimage");
    }
}
//VERIFY COORDINATES WITHIN PROJECT AREA
if ( $_POST["lat"] > 40.9168 || $_POST["lat"] < 40.490617 || $_POST["lng"] > -73.6619 || $_POST["lng"] < -74.2655 ){
	if ( $_POST["lat"] > 40.9168 ){
		echo $_POST["lat"] . " > 40.9168<br>";
	}
	if ( $_POST["lat"] < 40.490617 ){
		echo $_POST["lat"] . " < 40.490617<br>";
	}
	if ( $_POST["lng"] > -73.6619 ){
		echo $_POST["lng"] . " > -73.6619<br>";
	}
	if ( $_POST["lng"] < -74.2655 ){
		echo $_POST["lng"] . " < -74.2655<br>";
	}
	error("badlocation");
}

//VERIFY FOLDER TO UPLOAD INTO OR CREATE IT
$today = getdate();
//IMAGES DIRECTORY
if (!file_exists( "images/" . $today['year'] )){
	mkdir("images/" . $today['year'] . "/"); }
if (!file_exists( "images/" . $today['year'] . "/" . $today['mon'] )){
	mkdir("images/" . $today['year'] . "/" . $today['mon'] . "/"); }
if (!file_exists( "images/" . $today['year'] . "/" . $today['mon'] . "/" . $today['mday'] )){
	mkdir("images/" . $today['year'] . "/" . $today['mon'] . "/" . $today['mday'] . "/"); }
//THUMBS DIRECTORY
if (!file_exists( "thumbs/" . $today['year'] )){
	mkdir("thumbs/" . $today['year'] . "/"); }
if (!file_exists( "thumbs/" . $today['year'] . "/" . $today['mon'] )){
	mkdir("thumbs/" . $today['year'] . "/" . $today['mon'] . "/"); }
if (!file_exists( "thumbs/" . $today['year'] . "/" . $today['mon'] . "/" . $today['mday'] )){
	mkdir("thumbs/" . $today['year'] . "/" . $today['mon'] . "/" . $today['mday'] . "/"); }

//DETERMINE TARGET FILE NAME
$target_dir = $today['year'] . "/" . $today['mon'] . "/" . $today['mday'] . "/";
//$target_increment = mysqli_fetch_array(mysqli_query($connection, "SELECT COUNT( * ) FROM cibl_data"))[0] + 1;
$target_increment = mysqli_fetch_array(mysqli_query($connection, "SELECT MAX(increment) AS increment FROM cibl_queue"))[0] + 1;
$target_extension = pathinfo(basename($_FILES["image_submission"]["name"]), PATHINFO_EXTENSION);
$target_file = $target_dir . "queue_" . $target_increment . "." . $target_extension;
$target_image = "images/" . $target_file;
$target_thumb = "thumbs/" . $target_file;

//DETERMINE TIME
$time = date('Y-m-d H:i:s', strtotime($_POST["date"]));
//$time = strtotime($_POST["date"]);
//error_log("1: " . strtotime($_POST["date"]));
//error_log("2: " . $time);
//echo $time . "<br>";

//VALIDATE LICENSE PLATE
$plate = $_POST["plate"];
if (!ctype_alnum($plate)) { error("plate"); }
if (strlen($plate) > 7) { $plate = substr($plate,0,7); }
$plate = strtoupper($plate);

//VALIDATE STREETS
$street1 = mysqli_real_escape_string($connection, $_POST["street1"]);
$street2 = mysqli_real_escape_string($connection, $_POST["street2"]);

//ESCAPE CHARACTERS IN COMMENTS FIELD
$description_string = mysqli_real_escape_string($connection, $_POST["description"]);

//INSERT NEW RECORD INTO DATABASE
$row_added = "INSERT INTO cibl_queue (url, plate, state, date_occurrence, gps_lat, gps_long, street1, street2, description)
	VALUES ('" . $target_file . "', '" .
			$plate . "', '" .
			$_POST["state"] . "', '" .
			$time . "', " .
			$_POST["lat"] . ", " .
			$_POST["lng"] . ", '" .
			$street1 . "', '" .
			$street2 . "', '" .
			$description_string . "')";
//echo $row_added . "<br>";
if ($connection->query($row_added) === FALSE) {
    error("mysql");
}

//RESIZE AND MOVE RENAMED IMAGE INTO PLACE
$resized_image = resize_image($_FILES["image_submission"]["tmp_name"], 800, 800);
$save_image = imagejpeg($resized_image, $target_image, 90);
$resized_thumb = resize_image($_FILES["image_submission"]["tmp_name"], 200, 200);
$save_thumb = imagejpeg($resized_thumb, $target_thumb, 90);

if ($save_image == false){
	error("mysql");
}

success();

//IMAGE RESIZE FUNCTION	
function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $info = getimagesize($file);
    if ($info['mime'] == 'image/jpeg') 
		$src = imagecreatefromjpeg($file);
	elseif ($info['mime'] == 'image/gif') 
		$src = imagecreatefromgif($file);
	elseif ($info['mime'] == 'image/png') 
		$src = imagecreatefrompng($file);
    
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	
    return $dst;
}

function error($type) {
	echo "\n <div class=\"top_dialog_button\" id=\"close\">";
	echo "\n <span>&#x2A09</span>";
	echo "\n </div>";
	
	if ($type == "noimage"){
		echo "\n <h2>Error:</h2>";
		echo "\n <p class=\"submit_detail\">Submissions without an image attached are currently not accepted.</p>";
	}
	if ($type == "badimage"){
		echo "\n <h2>Error:</h2>";
		echo "\n <p class=\"submit_detail\">You must submit a JPG, JPEG, GIF or PNG image.</p>";
	}
	if ($type == "badlocation"){
		echo "\n <h2>Error:</h2>";
		echo "\n <p class=\"submit_detail\">No location marked within project area, please mark a valid location on the map.</p>";
	}
	if ($type == "mysql"){
		echo "\n <h2>Error:</h2>";
		echo "\n <p class=\"submit_detail\">Something is wrong with the server. Maybe try again later?</p>";
	}
	if ($type == "plate"){
		echo "\n <h2>Error:</h2>";
		echo "\n <p class=\"submit_detail\">License plates must only contain letters and numbers.</p>";
	}
	
	echo "\n\n<script>";
	echo "\n $('#close').click( function() {";
	echo "\n 	$(\"#results_form\").animate({opacity: 'toggle', right: '-565px'});";
	echo "\n	open_window('submit_view');";
	echo "\n });";
	echo "\n\n</script>";
	
	die();
}

function success() {
		echo "\n <div class=\"top_dialog_button\" id=\"close\">";
		echo "\n <span>&#x2A09</span>";
		echo "\n </div>";

		echo "\n <h2>Submission received!</h2>";
		echo "\n <p class=\"submit_detail\">Thank you for contributing!
		All submissions require moderator approval before being added to the map.
		Expect yours to show up within 24 hours.</p>";
		echo "\n <button class='wide' id='submit_another'>Submit Another</button></div>";
		
		echo "\n\n<script>";
		
		echo "\n $('#submit_another').click( function() {";
		echo "\n 	$(\"#results_form\").animate({opacity: 'toggle', right: '-565px'});";
		echo "\n 	document.getElementById(\"the_form\").reset();";
		echo "\n	open_window('submit_view');";
		echo "\n });";
		
		echo "\n $('#close').click( function() {";
		echo "\n 	$(\"#results_form\").animate({opacity: 'toggle', right: '-565px'});";
		echo "\n 	open_window('entry_list');";
		echo "\n });";
		
		echo "\n\n</script>";
}

?> 
