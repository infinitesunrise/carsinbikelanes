<?php
require 'auth.php';
require 'config.php';
?>

<html>
<head>

<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="../css/style.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- leaflet -->
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />

<!-- google fonts -->
<link href="http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One" rel="stylesheet" type="text/css"/>

</head>
<body class="non_map">

<div class='flex_container_scroll'>
<div class='settings_container'>

<?php include 'nav.php'; ?>

<?php
if (isset($_GET['message'])){
	$message = $_GET['message'];
	echo "<div class='settings_box' style='background-color: green'>\n";
	echo "<div class='settings_group'>\n";
	echo "<h3>Success:</h3>";
	echo "<p>" . $message . "</p>\n";
	echo "</div>";
	echo "</div>";
}
if (isset($_GET['error'])){
	$error = $_GET['error'];
	echo "<div class='settings_box' style='background-color: red'>\n";
	echo "<div class='settings_group'>\n";
	echo "<h3>Error:</h3>";
	echo "<p>" . $error . "</p>\n";
	echo "</div>";
	echo "</div>";
}
?>

<div class='settings_box'>
<div class='settings_group'>
<h3>Reset Password</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='reset_password' value='true'>
<span>old password: </span><input type='password' class='wide' name='oldpass' /><br>
<span>new password: </span><input type='password' class='wide' name='newpass1' /><br>
<span>type it again: </span><input type='password' class='wide' name='newpass2' /><br>
<input type='submit' class='wide' name='reset_password' value='Reset Password'/>
</form>
</div>
</div>

<?php
if (isset($_SESSION['admin'])){
	if ($_SESSION['admin'] == true){
		include 'admin_settings.php';
	}
}
?>

</div>
</div>

</body>
</html>