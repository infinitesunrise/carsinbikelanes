<?php

require 'scripts/PasswordHash.php';
require 'admin/config_write.php';

//VALUES PASSED FROM SETUP FORM
$config = array(
'sqlhost' => $_POST["sqlhost"],
'sqluser' => $_POST["sqluser"],
'sqlpass' => $_POST["sqlpass"],
'database' => $_POST["database"]
);
$username = $_POST["username"];
$password1 = $_POST["password1"];
$password2 = $_POST["password2"];
$email = $_POST["email"];
$config_folder = $_POST["config_folder"];

$progress = "";

//CHECK THAT PASSWORDS WERE CORRECTLY TYPED
$password = "";
if ($password1 !== $password2){
	return_error("Passwords entered do not match.");
}
else { $password = $password1; }

//CREATE MYSQL CONNECTION
$connection = new mysqli($config['sqlhost'], $config['sqluser'], $config['sqlpass']);
if ($connection->connect_error) {
	return_error("MySQL connection failed: " . $connection->connect_error);
} 

//CREATE MYSQL DATABASE
$query = "CREATE DATABASE IF NOT EXISTS " . $config['database'] . " CHARACTER SET utf8 COLLATE utf8_general_ci;";
if ($connection->query($query) === TRUE) {
	$query = "USE " . $config['database'];
	if ($connection->query($query) === TRUE) {
		$progress .= "Database " . $config['database'] . " set up successfully.<br>";
	}
} else {
	return_error("MySQL connection successful but error setting up database: " . $connection->error);
}

//CREATE MYSQL RECORDS TABLE
$query = "CREATE TABLE cibl_data (
increment int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
url text NOT NULL,
plate text NOT NULL,
state tinytext NOT NULL,
date_occurrence timestamp DEFAULT '0000-00-00 00:00:00',
date_added timestamp DEFAULT CURRENT_TIMESTAMP,
gps_lat float(10,6) NOT NULL,
gps_long float(10,6) NOT NULL,
street1 text NOT NULL,
street2 text NOT NULL,
description text NOT NULL
)";
if ($connection->query($query) === TRUE) {
    $progress .= "Records table populated successfully.<br>";
} else {
	return_error("MySQL error populating database: " . $connection->error);
}

//CREATE SUBMISSION QUEUE TABLE
$query = "CREATE TABLE cibl_queue (
increment int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
url text NOT NULL,
plate text NOT NULL,
state tinytext NOT NULL,
date_occurrence timestamp DEFAULT '0000-00-00 00:00:00',
date_added timestamp DEFAULT CURRENT_TIMESTAMP,
gps_lat float(10,6) NOT NULL,
gps_long float(10,6) NOT NULL,
street1 text NOT NULL,
street2 text NOT NULL,
description text NOT NULL
)";
if ($connection->query($query) === TRUE) {
    $progress .= "Submission queue table populated successfully.<br>";
} else {
	return_error("MySQL error populating database: " . $connection->connect_error);
}

//CREATE MYSQL LOGINS TABLE
$query = "CREATE TABLE cibl_users (
username CHAR(30) NOT NULL,
hash CHAR(60) NOT NULL,
admin BOOLEAN NOT NULL,
email CHAR(255)
)";
if ($connection->query($query) === TRUE) {
    $progress .= "Logins table populated successfully.<br>";
} else {
	return_error("MySQL error populating database: " . $connection->connect_error);
}

//SAVE ADMIN CREDENTIALS
$hasher = new PasswordHash(8, false);
$hash = $hasher->HashPassword($password);
$query = "INSERT INTO cibl_users VALUES ('" . $username . "', '" . $hash . "', TRUE, '" . $email . "');";
if ($connection->query($query) === TRUE) {
    $progress .= "Admin credentials saved.<br>";
} else {
	return_error("MySQL error saving admin credentials: " . $connection->connect_error);
}

$connection->close();

//MAKE SURE CONFIG FOLDER PATH IS VALID AND DOES NOT ALREADY EXIST
$path_parts = explode('/', $config_folder);
array_pop($path_parts);
$config_parent = implode('/', $path_parts);
if (!file_exists($config_parent)){
	return_error("Config folder path not valid");
}
if (file_exists($config_folder)){
	return_error("Configuration folder already exists at specified location.");
}

//MOVE AND RENAME CONFIG FOLDER
if (!rename('config', $config_folder)){
	return_error("Problem setting up configuration folder.");
}

//CREATE POINTER TO CONFIG FILE
$config_pointer = fopen('admin/config_pointer.php', 'w');
$pointer_contents = 
	"<?php \n" . 
	"include ('" . $config_folder . "/config.php');\n" . 
	"\$config_folder = '" . $config_folder . "';\n" .  
	"\$config_location = '" . $config_folder . "/config.php';\n" .
	"?>";
fwrite($config_pointer, $pointer_contents);
fclose($config_pointer);

//CREATE CONFIG FILE, CREATE EMPTY DIRECTORIES, SWAP SETUP AND MAIN INDEX PAGE
config_write($config);
mkdir("images");
mkdir("thumbs");
rename('index.php', 'index_old.php');
rename('index_actual.php', 'index.php');

$progress .= "Setup complete!<br>";

$progress .= "<script>location.href = 'index.php?setup_success_dialog=true';</script>";

echo $progress;

function return_error($error){
	error_log($error);
	$error_parsed = rawurlencode($error);
	$url = 'Location: index.php?error=' . $error_parsed;
	header($url);
	exit();
}

?>