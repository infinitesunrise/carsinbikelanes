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

<div class='settings_box'>

<div class='settings_group'>
<h2>Settings</h2>
</div>

<form class="centered_form" action="setup.php" method="post">

<div class='settings_group'>
<h3>MySQL:</h3>
<span>hostname: </span><input type="text" class="wide" name="hostname" placeholder="localhost"/><br>
<span>username: </span><input type="text" class="wide" name="sqluser" placeholder="root"/><br>
<span>password: </span><input type="text" class="wide" name="sqlpass" placeholder="root"/><br>
<span>database: </span><input type="text" class="wide" name="database" placeholder="carsinbikelanes"/><br>
<p class="tinytext">Note: Database must not already exist.</p>
</div>

<div class="settings_group">
<h3>Mapbox:</h3>
<span>api key: </span><input type="text" class="wide" name="api_key" placeholder="paste API key here"/><br>
<p class="tinytext">Currently CIBL only utilizes Mapbox for map display. 
Sign up for a Mapbox account <a href="https://www.mapbox.com/signup/">here</a>, 
create an API key in your Mapbox settings, and paste it into the field above. 
Visit the admin page after setup if you wish to specify a background map other than the Mapbox default.</p>
</div>

</form>

</div>
</div>
</div>

</body>
</html>