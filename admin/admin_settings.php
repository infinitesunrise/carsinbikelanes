<div class='settings_box'>
<div class='settings_group'>
<h3>New User</h3>
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
<h3>Users</h3>
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
<h3>Site Identity</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_users' value='true'>
<span>site name: </span><input type='text' class='wide' name='site_name' value='<?php echo $config['site_name'];  ?>'/><br>
<p class="tinytext">
Displays on the main page and anywhere else the site name is referenced.</p>
<input type='submit' class='wide' name='update_identity' value='Update Site Identity'/>
</form>
</div>
</div>

<script type="text/javascript">
function calculate_centers() {
	var north = parseFloat(document.getElementById("north_bounds").value);
	var south = parseFloat(document.getElementById("south_bounds").value);
	var east = parseFloat(document.getElementById("east_bounds").value);
	var west = parseFloat(document.getElementById("west_bounds").value);
	var center_lat = (north + south) / 2;
	var center_long = (east + west) / 2;
	document.getElementById("center_lat").value = center_lat.toFixed(4);
	document.getElementById("center_long").value = center_long.toFixed(4);
}
</script>
<div class='settings_box'>
<div class='settings_group'>
<h3>Project Bounds</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_coords' value='true'>
<p class="tinytext">GPS coordinates representing the maximum north, south, east and west boundaries for user submissions. 
Submissions outside of these bounds will be rejected with an error message.</p>

<?php
echo "<div class='flex_container_oneline'>\n";
echo "<span class='left-align'>north: </span>";
echo "<div style='width:10px'></div>";
echo "<span class='left-align'>east: </span>";
echo "</div>";

echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' id='north_bounds' class='wide' name='north_bounds' value='" . $config['north_bounds'] . "' onChange='calculate_centers()'/><br>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' id='east_bounds' class='wide' name='east_bounds' value='" . $config['east_bounds'] . "' onChange='calculate_centers()'/><br>\n";
echo "</div>\n";

echo "<div class='flex_container_oneline'>\n";
echo "<span class='left-align'>south: </span>";
echo "<div style='width:10px'></div>";
echo "<span class='left-align'>west: </span>";
echo "</div>";

echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' id='south_bounds' class='wide' name='south_bounds' value='" . $config['south_bounds'] . "' onChange='calculate_centers()'/><br>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' id='west_bounds' class='wide' name='west_bounds' value='" . $config['west_bounds'] . "' onChange='calculate_centers()'/><br>\n";
echo "</div>\n";

echo "<span>center map at: </span>\n";
echo "<br>\n";
echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' id='center_lat' class='wide' name='center_lat' value='" . $config['center_lat'] . "'/>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' id='center_long' class='wide' name='center_long' value='" . $config['center_long'] . "'/>\n";
echo "</div>";
?>

<input type='submit' class='wide' name='update_coords' value='Update Coordinates'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>About Text</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_about' value='true'>
<p class="tinytext">Text to display when the "about" link on the main page is clicked. HTML is OK, but try not to go too nuts.</p>
<?php
echo "<textarea class='settings' name='about_text'>" . $config['about_text'] . "</textarea><br>\n";
?>
<input type='submit' class='wide' name='update_about' value='Update About Box'/>
</form>
</div>
</div>

<script src="../scripts/leaflet-providers.js"></script>
<script type="text/javascript">
var providers = L.TileLayer.Provider.providers;
for (var provider in providers){
	document.writeln(provider + " </br>");	
	if (providers[provider].hasOwnProperty("variants")){
		for (var variant in providers[provider].variants){
			document.writeln(provider + "." + variant + " </br>");
		}
	}
}
</script> 
<div class='settings_box'>
<div class="settings_group">
<h3>Map Data</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_map' value='true'>
<span>tile provider: </span>
<select name='provider'>
  <option value="volvo">Volvo</option>
  <option value="saab">Saab</option>
  <option value="fiat">Fiat</option>
  <option value="audi">Audi</option>
</select>
<input type='text' class='wide' name='map_url' value='<?php echo $config['map_url']; ?>'/><br>
<span>api url: </span><input type='text' class='wide' name='map_url' value='<?php echo $config['map_url']; ?>'/><br>
<p class="tinytext"> CIBL currently utilizes Leaflet.js to display <a href="https://en.wikipedia.org/wiki/Tiled_web_map">tiled web maps</a> 
using an API URL supplied from tile map services. The default map is the unaltered version of OpenStreetMaps. 
A collection of free tile provider previews and their URLs can be viewed 
<a href="http://leaflet-extras.github.io/leaflet-providers/preview/index.html">here</a>.</p>
<input type='submit' class='wide' name='update_map_api' value='Update Map'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>MySQL</h3>
<form action='update_config.php' method='post'>
<input type='hidden' name='update_database' value='true'>
<?php
echo "<span>hostname: </span><input type='text' class='wide' name='sqlhost' value='" . $config['sqlhost'] . "'/><br>\n";
echo "<span>username: </span><input type='text' class='wide' name='sqluser' value='" . $config['sqluser'] . "'/><br>\n";
echo "<span>password: </span><input type='text' class='wide' name='sqlpass' value='" . $config['sqlpass'] . "'/><br>\n";
echo "<span>database: </span><input type='text' class='wide' name='database' value='" . $config['database'] . "'/><br>\n";
?>
<input type='submit' class='wide' name='update_database' value='Update Database'/>
</form>
</div>
</div>