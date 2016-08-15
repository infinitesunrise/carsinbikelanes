<?php

require 'auth.php';
require 'config.php';

if (isset($_POST['reset_password'])){ update_password($connection); }
if (isset($_POST['new_user'])){ new_user($connection); }

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

function new_user($connection) {
	$newusername = $_POST['newusername'];
	$newpass1 = $_POST['newpass1'];
	$newpass2 = $_POST['newpass2'];
	$email = $_POST['email'];
	$password = "";
	if ($newpass1 !== $newpass2){
		return_error("Password fields did not match!");
	}
	else { $password = $newpass1;
		$hasher = new PasswordHash(8, false);
		$hash = $hasher->HashPassword($password);
		$query = "INSERT INTO cibl_users VALUES ('" . $newusername . "', '" . $hash . "', FALSE, '" . $email . "');";
		if ($connection->query($query) === TRUE) {
    		return_message("New user " . $newusername . " created.");
		} else {
			return_error("MySQL error: " . $connection->error);
		}
	}
}

function return_message($message){
	$message_parsed = rawurlencode($message);
	$url = 'Location: settings.php?message=' . $message_parsed;
	header($url);
}

function return_error($error){
	$error_parsed = rawurlencode($error);
	$url = 'Location: settings.php?error=' . $error_parsed;
	header($url);
}

?>