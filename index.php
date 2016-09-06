<html>
<head>
<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="css/style.css" />

<!-- google fonts -->
<link href="http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One" rel="stylesheet" type="text/css"/>
</head>

<body class='non_map'>

<div class='flex_container_scroll'>
<div class='settings_container'>

<div class='settings_box'>
<div class='settings_group' style='width:600px'>
<h2>WELCOME TO CARSINBIKELANES</h2>
<br>
<p class='wide' style='width:400px; margin: 0 auto;'>The open source web app for crowdsourced geo-located traffic violation reporting!
Please fill in the fields below to set up.</p>
</div>
</div>

<?php
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

<form action="setup.php" method="post">

<div class='settings_box'>
<div class='settings_group'>
<h3>Administrator:</h3>
<span>username: </span><input class='wide' type="text" name="username"/><br>
<span>password: </span><input class='wide' type="password" name="password1"/><br>
<span>type it again: </span><input class='wide' type="password" name="password2"/><br>
<span>email (optional): </span><input class='wide' type="text" name="email"/><br>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>MySQL:</h3>
<span>hostname: </span><input class='wide' type="text" name="sqlhost" placeholder="localhost"/><br>
<span>username: </span><input class='wide' type="text" name="sqluser"/><br>
<span>password: </span><input class='wide' type="password" name="sqlpass"/><br>
<span>database: </span><input class='wide' type="text" name="database" placeholder="carsinbikelanes"/><br>
<p class="tinytext">Note: Database must not already exist.</p>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Configuration files:</h3>
<span>folder location: </span><input class='wide' type="text" name="config_folder" value="<?php
echo dirname($_SERVER['DOCUMENT_ROOT']) . '/cibl_config';
?>"/><br>
<p class="tinytext">
The config folder contains all credentials and settings used by CIBL and as such should not allowed access by the web server. 
By default CIBL will create this folder one directory level above http root. 
Please enter an alternate location if the above path is not secure.</p>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<input class='wide' style='margin-top:10px' type="submit" value="SET UP"/>
</div>
</div>

</form>

</div>
</div>

</body>

</html>