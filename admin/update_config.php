<?php

require 'auth.php';
require 'config.php';

if (isset($_POST['reset_password'])){ update_password($connection); }
if (isset($_POST['new_user'])){ new_user($connection); }
if (isset($_POST['update_users'])){ update_users($connection); }

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