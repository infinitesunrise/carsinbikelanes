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
<link href="http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One" rel="stylesheet" type="text/css"/>

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
<h3>Reset Password:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='reset_password' value='true'>
<span>old password: </span><input type='password' class='wide' name='oldpass' /><br>
<span>new password: </span><input type='password' class='wide' name='newpass1' /><br>
<span>type it again: </span><input type='password' class='wide' name='newpass2' /><br>
<input type='submit' class='wide' name='reset_password' value='Reset Password'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>New User:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='new_user' value='true'>
<span>username: </span><input type='text' class='wide' name='newusername' /><br>
<span>password: </span><input type='text' class='wide' name='newpass1' /><br>
<span>type it again: </span><input type='text' class='wide' name='newpass2' /><br>
<span>email (optional): </span><input type='text' class='wide' name='email' /><br>
<input type='submit' class='wide' name='create_user' value='Create User'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Users:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_users' value='true'>
<p class="tinytext">
Note: Users do not have access to this settings page unless their admin box below is checked. 
Non-admin users only have access to the submission queue.</p>
<div class="user_list_row">
<div class='user_list_name'>NAME</div>
<div class='user_list_admin'>ADMIN</div>
<div class='user_list_email'>EMAIL</div>
<div class='user_list_delete'>DELETE</div>
</div>
<?php
$altbg = TRUE;
$query = "SELECT * FROM cibl_users";
$user_list = mysqli_query($connection, $query);
$counter = 0;
while ($row = mysqli_fetch_array($user_list)) {
	if ($altbg) { echo '<div class="user_list_row" style="background-color:rgba(0,0,0,0.2)">'; }
	else { echo '<div class="user_list_row" style="background-color:rgba(0,0,0,0.1)">'; }
	echo "<div class='user_list_name'>" . $row[0] . "</div>";
	echo "<div class='user_list_admin'>";
	echo "<input type='hidden' name='admin[" . $counter . "]' />";
	if ($row[2] == TRUE) { echo "<input type='checkbox' name='admin[" . $counter . "]' checked='checked'/>"; }
	else { echo "<input type='checkbox' name='admin[" . $counter . "]' />"; }
	echo "</div>";
	echo "<div class='user_list_email'>" . $row[3] . "</div>";
	echo "<input type='hidden' name='delete[" . $counter . "]' />";
	echo "<div class='user_list_delete'><input type='checkbox' name='delete[" . $counter . "]'/></div>";
	echo "</div>\n";
	$altbg = !$altbg;
	$counter++;
}
?>
<input type='submit' class='wide' name='update_users' value='Update Users'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Project Bounds:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_coords' value='true'>
<p class="tinytext">GPS coordinates representing the maximum north, south, east and west boundaries for user submissions. 
Submissions outside of these bounds will be rejected with an error message.</p>
<?php
echo "<span>north: </span><input type='text' class='wide' name='north' value='" . $north_bounds . "'/><br>\n";
echo "<span>south: </span><input type='text' class='wide' name='south' value='" . $south_bounds . "'/><br>\n";
echo "<span>east: </span><input type='text' class='wide' name='east' value='" . $east_bounds . "'/><br>\n";
echo "<span>west: </span><input type='text' class='wide' name='west' value='" . $west_bounds . "'/><br>\n";
echo "<br>\n";
echo "<span>center map at: </span>\n";
echo "<br>\n";
echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' class='wide' name='center_lat' value='" . $center_lat . "'/>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' class='wide' name='center_long' value='" . $center_long . "'/>\n";
echo "</div>";
?>
<input type='submit' class='wide' name='update_coords' value='Update Coordinates'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>About Text:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_about' value='true'>
<p class="tinytext">Text to display when the "about" link on the main page is clicked. HTML is OK, but try not to go too nuts.</p>
<?php
echo "<textarea class='settings' name='about_text'>" . $about_text . "</textarea><br>\n";
?>
<input type='submit' class='wide' name='update_about' value='Update About Box'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class="settings_group">
<h3>Map Service:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_map' value='true'>
<?php
echo "<span>api key: </span><input type='text' class='wide' name='api_key' value='" . $api_key . "'/><br>\n";
?>
<p class="tinytext">Currently CIBL only utilizes Mapbox for map display. 
Sign up for a Mapbox account <a href="https://www.mapbox.com/signup/">here</a>, 
create an API key in your Mapbox settings, and paste it into the field above. 
Visit the admin page after setup if you wish to specify a background map other than the Mapbox default.</p>
<input type='submit' class='wide' name='update_map' value='Update Map'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>MySQL:</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_database' value='true'>
<?php
echo "<span>hostname: </span><input type='text' class='wide' name='hostname' value='" . $sqlhost . "'/><br>\n";
echo "<span>username: </span><input type='text' class='wide' name='sqluser' value='" . $sqluser . "'/><br>\n";
echo "<span>password: </span><input type='text' class='wide' name='sqlpass' value='" . $sqlpass . "'/><br>\n";
echo "<span>database: </span><input type='text' class='wide' name='database' value='" . $database . "'/><br>\n";
?>
<input type='submit' class='wide' name='update_database' value='Update Database'/>
</form>
</div>
</div>

</div>
</div>

</body>
</html>