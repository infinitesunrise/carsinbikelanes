<?php

require 'scripts/PasswordHash.php';

//VALUES PASSED FROM SETUP FORM
$username = $_POST["username"];
$password = $_POST["password"];
$hostname = $_POST["hostname"];
$sqluser = $_POST["sqluser"];
$sqlpass = $_POST["sqlpass"];
$database = $_POST["database"];
$api_key = $_POST["api_key"];

//CREATE MYSQL CONNECTION
$connection = new mysqli($hostname, $sqluser, $sqlpass);
if ($connection->connect_error) {
    die("MySQL connection failed: " . $connection->connect_error);
    echo "MySQL connection failed: " . $connection->connect_error;
} 

//CREATE MYSQL DATABASE
$query = "CREATE DATABASE " . $database . " CHARACTER SET utf8 COLLATE utf8_general_ci;";
if ($connection->query($query) === TRUE) {
	$query = "USE " . $database;
	if ($connection->query($query) === TRUE) {
		echo "Database " . $database . " created successfully.<br>";
	}
} else {
	die("MySQL connection successful but error creating database: " . $connection->error);
    echo "MySQL connection successful but error creating database: " . $connection->error;
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
    echo "Records table populated successfully.<br>";
} else {
	die("MySQL error populating database: " . $connection->error);
    echo "MySQL error populating database: " . $connection->error;
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
    echo "Submission queue table populated successfully.<br>";
} else {
	die("MySQL error populating database: " . $connection->error);
    echo "MySQL error populating database: " . $connection->error;
}

//CREATE MYSQL LOGINS TABLE
$query = "CREATE TABLE cibl_users (
username CHAR(30) NOT NULL,
hash CHAR(60) NOT NULL
)";
if ($connection->query($query) === TRUE) {
    echo "Logins table populated successfully.<br>";
} else {
	die("MySQL error populating database: " . $connection->error);
    echo "MySQL error populating database: " . $connection->error;
}

//SAVE ADMIN CREDENTIALS
$hasher = new PasswordHash(8, false);
$hash = $hasher->HashPassword($password);
$query = "INSERT INTO `cibl_users` (`username`, `hash`) VALUES ('" . $username . "', '" . $hash . "');";
if ($connection->query($query) === TRUE) {
    echo "Admin credentials saved.<br>";
} else {
	die("MySQL error saving admin credentials: " . $connection->error);
    echo "MySQL error saving admin credentials: " . $connection->error;
}

$connection->close();

//CREATE CONFIG FILE
$config_file = fopen("admin/config.php", "w") or die("PHP Error: Issues creating config file. Are permissions set correctly?");
$text = "<?php\n\n" .
"//----------------------------------------------//\n" .
"// CONFIGURATION\n" .
"//----------------------------------------------//\n\n" .
"//MySQL\n" .
"\$hostname = \"" . $hostname . "\";\n" .
"\$username = \"" . $sqluser . "\";\n" .
"\$password = \"" . $sqlpass . "\";\n" .
"\$database = \"" . $database . "\";\n\n" .
"//Mapbox\n" .
"\$api_key = \"" . $api_key . "\";\n\n" .
"//Preferences\n" .
"\$max_view = 50;\n\n" .
"//----------------------------------------------//\n\n" .
"\$connection = mysqli_connect(\$hostname,\$username,\$password,\$database);\n\n" .
"?>";

fwrite($config_file, $text);
fclose($config_file);

mkdir("images");
mkdir("thumbs");

rename('index.php', 'index_old.php');
rename('index_actual.php', 'index.php');

echo "<script>location.href = 'index.php?setup_success_dialog=true';</script>";

echo $text . "<br>";

?>