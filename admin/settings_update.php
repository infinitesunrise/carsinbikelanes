<?php

require 'auth.php';
require 'config_pointer.php';
require 'config_write.php';

if (isset($_POST['reset_password'])){ update_password($connection); }
if (isset($_POST['update_email'])){ update_email($connection, $config); }
if (isset($_POST['new_user'])){ new_user($connection, $config); }
if (isset($_POST['update_users'])){ update_users($connection); }
if (isset($_POST['update_identity'])){ update_identity(); }
if (isset($_POST['update_coords'])){ update_coords(); }
if (isset($_POST['update_about'])){ update_about(); }
if (isset($_POST['update_comments'])){ update_comments(); }
if (isset($_POST['update_map'])){ update_map($config_folder); }
if (isset($_POST['update_database'])){ update_database(); }

function update_password($connection) {
	$oldpass = $_POST['oldpass'];
	$current_user = $_SESSION['username'];
	$hasher = new PasswordHash(8, false);
	$query = 'SELECT hash FROM cibl_users WHERE username=\'' . $current_user . '\' LIMIT 1';
	$result = "";
	try { $result = mysqli_query($connection,$query); }
	catch (Exception $e){ die("Error: User does not exist"); }
	$row = mysqli_fetch_assoc($result);
	$hash = $row['hash'];
	$check = $hasher->CheckPassword($oldpass, $hash);
	if ($check) {
		$newpass1 = $_POST["newpass1"];
		$newpass2 = $_POST["newpass2"];
		$newpass = "";
		if ($newpass1 !== $newpass2){
			return_error("New password fields did not match!");
		}
		else {
			$newpass = $newpass1;
			$hash = $hasher->HashPassword($newpass);
			$query = "UPDATE cibl_users SET hash='" . $hash . "' WHERE username='" . $current_user . "'";
			if ($connection->query($query) === TRUE) {
				return_message("Admin credentials updated for user " . $current_user);
			} else {
				return_error("MySQL error: " . $connection->error);
			}	
		}
	}
	else {
		return_error("Old password incorrect");
	}
}

function update_email($connection, $config) {
	if(isset($_POST['email'])){
		$email = $_POST['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL)){
			$current_user = $_SESSION['username'];
			$query = "UPDATE cibl_users SET email='" . $email . "' WHERE username='" . $current_user . "'";
			if ($connection->query($query) === TRUE) {
				$email_op = 'edit_email';
				include '../email_notify.php';
				if ($sent){
					return_message("Email address updated.");
				}
			} else {
				return_error("MySQL error: " . $connection->error);
			}	
		}
		else{
			return_error("Email address entered is invalid");
		}
	}
}

function new_user($connection, $config) {
	$newusername = $_POST['newusername'];
	$newpass1 = $_POST['newpass1'];
	$newpass2 = $_POST['newpass2'];
	$email = (filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) ? $_POST['email'] : '');
	if ($email != $_POST['email']){ return_error('Invalid email address.'); }
	$submit_notify = ($email != '' ? TRUE : FALSE);
	$password = "";
	$query = "SELECT EXISTS(SELECT username FROM cibl_users WHERE username = '" . $newusername . "')";
	$result = $connection->query($query);
	$exists = mysqli_fetch_array($result)[0];
	if ($exists == 1) {
    	return_error("Username already exists.");
	}
	if ($newpass1 !== $newpass2){
		return_error("Password fields did not match!");
	}
	else {
		$password = $newpass1;
		$hasher = new PasswordHash(8, false);
		$hash = $hasher->HashPassword($password);
		$query = "INSERT INTO cibl_users VALUES ('" . $newusername . "', '" . $hash . "', FALSE, " . $submit_notify . ", '" . $email . "');";
		if ($connection->query($query) === TRUE) {
			$email_op = 'new_user';
			include '../email_notify.php';
    		return_message("New user " . $newusername . " created.");
		} else {
			return_error("MySQL error: " . $connection->error);
		}
	}
}

function update_users($connection){
	$admin = $_POST['admin'];
	$delete = $_POST['delete'];
	$form = $_POST['update_users'];
	$number_of_admins = 0;
	
	foreach ($admin as $index => $checked){
		if ($checked && (!$delete[$index])){
			++$number_of_admins;
		}
		if ($checked) { $admin[$index] = "1"; }
		else { $admin[$index] = "0"; }
		if ($delete[$index]) { $delete[$index] = "1"; }
		else { $delete[$index] = "0"; }
		error_log($admin[$index] . ", " . $delete[$index]);
	}
	
	if ($number_of_admins < 1) {
		return_error("You did not assign any remaining users as administrator, there must be at least one.");
		return;
	}
	
	$query = "SELECT * FROM cibl_users";
	$user_list = mysqli_query($connection, $query);
	$index = 0;
	while ($row = mysqli_fetch_array($user_list)) {
		if ($admin[$index] != $row[2]){
			$query = "UPDATE cibl_users SET admin=" . $admin[$index] . " WHERE username='" . $row[0] . "'";
			$connection->query($query);
		}
		if ($delete[$index]){
			if ($row[0] == $_SESSION['username']){
				return_error("You cannot delete yourself.");
			}
			$query = "DELETE FROM cibl_users WHERE username='" . $row[0] . "'";
			$connection->query($query);
		}
		$index++;
	}
	return_message("User list updated.");
}

function update_identity(){
	if(isset($_POST['update_identity'])){
		$new_values = array(
			'site_name' => $_POST['site_name']
		);
		config_write($new_values);
		return_message("Site identity updated.");
	}
}

function update_coords(){
	if(isset($_POST['update_coords'])){
		$new_values = array(
			'north_bounds' => $_POST['north_bounds'],
			'south_bounds' => $_POST['south_bounds'],
			'east_bounds' => $_POST['east_bounds'],
			'west_bounds' => $_POST['west_bounds'],
			'center_lat' => $_POST['center_lat'],
			'center_long' => $_POST['center_long'],
			'mobile_center_lat' => $_POST['mobile_center_lat'],
			'mobile_center_long' => $_POST['mobile_center_long']
		);
		
		if($new_values['north_bounds'] <= 90 &&
			$new_values['south_bounds'] >= -90 &&
			$new_values['east_bounds'] <= 180 &&
			$new_values['west_bounds'] >= -180 &&
			$new_values['north_bounds'] > $new_values['south_bounds'] &&
			$new_values['east_bounds'] > $new_values['west_bounds'])
		{
			config_write($new_values);
			return_message("Project bounds updated.");
		}
		else { return_error("Unsuitable GPS bounds. 
		Latitude must be between -90 and 90, longitude must be between -180 and 180. 
		North must be greater than south and east must be greater than west."); }
	}
}

function update_about(){
	if(isset($_POST['update_about'])){
		$new_text = addslashes(htmlspecialchars($_POST['about_text']));
		$new_values = array( 'about_text' => $new_text) ;
		config_write($new_values);
		return_message("Updated about box text.");
	}
}

function update_comments(){
	if(isset($_POST['update_comments'])){
		$new_values = array(
			'comments' => $_POST['comments'],
			'disqus' => $_POST['disqus']
		);
		config_write($new_values);
		return_message("Comment settings updated.");
	}
}

function update_map($config_folder){
	if(isset($_POST['update_map'])){
		$new_values = array(
			'use_providers_plugin' => $_POST['use_providers_plugin'],
			'leaflet_provider' => $_POST['leaflet_provider'],
			'use_google' => $_POST['use_google'],
			'google_api_key' => $_POST['google_api_key'],
			'google_extra_layer' => $_POST['google_extra_layer'],
			'map_url' => $_POST['map_url'],
			'use_bing' => $_POST['use_bing'],
			'bing_api_key' => $_POST['bing_api_key'],
			'bing_imagery' => $_POST['bing_imagery'],
		);
		config_write($new_values);
		$new_styles = $_POST['google_style'];
		if($new_styles == ""){ $new_styles = "[\n\n]"; }
		if (file_exists($config_folder . '/google_style.php')){
			file_put_contents($config_folder . '/google_style.php', "");
		}
		$style_file = fopen($config_folder . '/google_style.php', "w")
			or return_error("PHP Error: Issues creating google map styles file. Are permissions set correctly?");
		fwrite($style_file, $new_styles);
		fclose($style_file);
		
		return_message("Updated map service.");
	}
}

function update_database(){
	if(isset($_POST['update_database'])){
		$new_values = array(
			'sqlhost' => $_POST['sqlhost'],
			'sqluser' => $_POST['sqluser'],
			'sqlpass' => $_POST['sqlpass'],
			'database' => $_POST['database']
		);
		$connection = mysqli_connect($new_values['sqlhost'],$new_values['sqluser'],$new_values['sqlpass'],$new_values['database']);
		if ($connection){
			config_write($new_values);
			return_message("Updated MySQL connection details.");
		}
		else { return_error("Bad MySQL connection settings, couldn't connect."); }
	}
}

function return_message($message){
	$message_parsed = rawurlencode($message);
	$url = 'Location: settings.php?message=' . $message_parsed;
	header($url);
	exit();
}

function return_error($error){
	$error_parsed = rawurlencode($error);
	$url = 'Location: settings.php?error=' . $error_parsed;
	header($url);
	exit();
}

?>