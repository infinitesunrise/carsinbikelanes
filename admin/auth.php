<?php

require '../scripts/PasswordHash.php';
require '../config/config.php';

session_start();

if (!isset($_SESSION['login']) && isset($_POST['username']) && isset($_POST['password'])){	
	$username = $_POST['username'];
	$password = $_POST['password'];
	$hasher = new PasswordHash(8, false);
	$query = 'SELECT * FROM cibl_users WHERE username=\'' . $username . '\' LIMIT 1';
	$result = "";
	try { $result = mysqli_query($connection,$query); }
	catch (Exception $e){ die("Error: User does not exist"); }
	$row = mysqli_fetch_assoc($result);
	$hash = $row['hash'];
	$admin = $row['admin'];
	$check = $hasher->CheckPassword($password, $hash);
	if ($check) {
		$_SESSION['login'] = true;
		$_SESSION['username'] = $username;
		if ($admin) { $_SESSION['admin'] = true; }
		else { $_SESSION['admin'] = false; }
	}
	else { header('Location: login.php'); }
}
else if (!isset($_SESSION['login'])) {
	header('Location: login.php');
}

if (isset($_GET['logout'])){
	session_unset();
	header('Location: ../index.php');
}

?>