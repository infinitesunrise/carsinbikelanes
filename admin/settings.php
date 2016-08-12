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

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>

</head>
<body class="non_map">

<div class='flex_container_scroll'>
<div class='settings_container'>

<?php include 'nav.php'; ?>

<!-- <div class='settings_box'>
<div class='settings_group'>
<h2>Settings</h2>
</div>
</div> -->

<?php
if (isset($_POST['$error_message'])){
	echo "<div class='settings_box'>\n";
	echo "<div class='settings_group'>\n";
	echo "<h3>Error!</h3>";
	echo "<p>" . $error_message . "</p>\n";
	echo "</div>";
	echo "</div>";
}
?>

<div class='settings_box'>
<div class='settings_group'>
<h3>Reset Password:</h3>
<form>
<span>old password: </span><input type='text' class='wide' name='oldpass' /><br>
<span>new password: </span><input type='text' class='wide' name='newpass1' /><br>
<span>type it again: </span><input type='text' class='wide' name='newpass2' /><br>
<input type='submit' class='wide' name='submit_password_change' value='Reset Password'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>New User:</h3>
<span>username: </span><input type='text' class='wide' name='newusername' /><br>
<span>password: </span><input type='text' class='wide' name='newpassword1' /><br>
<span>type it again: </span><input type='text' class='wide' name='newpassword2' /><br>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Users:</h3>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Project Bounds:</h3>
<p class="tinytext">GPS coordinates representing the maximum north, south, east and west boundaries for user submissions. 
Submissions outside of these bounds will be rejected with an error message.</p>
<?php
echo "<span>north: </span><input type='text' class='wide' name='north' value='" . $north . "'/><br>\n";
echo "<span>south: </span><input type='text' class='wide' name='south' value='" . $south . "'/><br>\n";
echo "<span>east: </span><input type='text' class='wide' name='east' value='" . $east . "'/><br>\n";
echo "<span>west: </span><input type='text' class='wide' name='west' value='" . $west . "'/><br>\n";
echo "<br>\n";
echo "<span>center map at: </span>\n";
echo "<br>\n";
echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' class='wide' name='center_lat' value='" . $center_lat . "'/>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' class='wide' name='center_long' value='" . $center_long . "'/>\n";
echo "</div>";
?>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>About Text:</h3>
<p class="tinytext">Text to display when the "about" link on the main page is clicked. HTML is OK, but try not to go too nuts.</p>
<?php
echo "<textarea class='settings' name='abouttext' value='" . $abouttext . "'></textarea><br>\n";
?>
</div>
</div>

<div class='settings_box'>
<div class="settings_group">
<h3>Map Service:</h3>
<?php
echo "<span>api key: </span><input type='text' class='wide' name='api_key' value='" . $api_key . "'/><br>\n";
?>
<p class="tinytext">Currently CIBL only utilizes Mapbox for map display. 
Sign up for a Mapbox account <a href="https://www.mapbox.com/signup/">here</a>, 
create an API key in your Mapbox settings, and paste it into the field above. 
Visit the admin page after setup if you wish to specify a background map other than the Mapbox default.</p>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>MySQL:</h3>
<?php
echo "<span>hostname: </span><input type='text' class='wide' name='hostname' value='" . $sqlhost . "'/><br>\n";
echo "<span>username: </span><input type='text' class='wide' name='sqluser' value='" . $sqluser . "'/><br>\n";
echo "<span>password: </span><input type='text' class='wide' name='sqlpass' value='" . $sqlpass . "'/><br>\n";
echo "<span>database: </span><input type='text' class='wide' name='database' value='" . $database . "'/><br>\n";
?>
</div>
</div>

</div>
</div>

</body>
</html>