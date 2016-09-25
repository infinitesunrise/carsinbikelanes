<div class='settings_box'>
<div class='settings_group'>
<h3>New User</h3>
<form action='settings_update.php' method='post'>
<input type='hidden' name='new_user' value='true'>
<span>username: </span><input type='text' class='wide' name='newusername' /><br>
<span>password: </span><input type='password' class='wide' name='newpass1' /><br>
<span>type it again: </span><input type='password' class='wide' name='newpass2' /><br>
<span>email (optional): </span><input type='text' class='wide' name='email' /><br>
<input type='submit' class='wide' name='create_user' value='Create User'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Users</h3>
<form action='settings_update.php' method='post'>
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
	echo "<div class='user_list_email'>" . $row[4] . "</div>";
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
<form action='settings_update.php' method='post'>
<input type='hidden' name='update_identity' value='true'>
<span>site name: </span><input type='text' class='wide' name='site_name' value='<?php echo $config['site_name'];  ?>'/><br>
<p class="tinytext">
Displays on the main page and anywhere else the site name is referenced.</p>
<input type='submit' class='wide' name='update_identity' value='Update Site Identity'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Project Bounds</h3>
<form action='settings_update.php' method='post'>
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

echo "<span>center desktop site map at: </span>\n";
echo "<br>\n";
echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' id='center_lat' class='wide' name='center_lat' value='" . $config['center_lat'] . "'/>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' id='center_long' class='wide' name='center_long' value='" . $config['center_long'] . "'/>\n";
echo "</div>";

echo "<span>center mobile site map at: </span>\n";
echo "<br>\n";
echo "<div class='flex_container_oneline'>\n";
echo "<input type='text' id='mobile_center_lat' class='wide' name='mobile_center_lat' value='" . $config['mobile_center_lat'] . "'/>\n";
echo "<div style='width:10px'></div>";
echo "<input type='text' id='mobile_center_long' class='wide' name='mobile_center_long' value='" . $config['mobile_center_long'] . "'/>\n";
echo "</div>";
?>

<input type='submit' class='wide' name='update_coords' value='Update Coordinates'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>About Text</h3>
<form action='settings_update.php' method='post'>
<input type='hidden' name='update_about' value='true'>
<p class="tinytext">Text to display when the "about" link on the main page is clicked. HTML is OK, but try not to go too nuts.</p>
<?php
echo "<textarea class='settings' name='about_text'>" . $config['about_text'] . "</textarea><br>\n";
?>
<input type='submit' class='wide' name='update_about' value='Update About Box'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>Comments</h3>
<form action='settings_update.php' method='post'>
<input type='hidden' name='update_comments' value='true'>
<?php
if ($config['comments'] == TRUE) {
	echo "<span>allow comments: </span><input type='checkbox' id='comments' name='comments' checked='checked'/>"; }
else {
	echo "<span>allow comments: </span><input type='checkbox' id='comments' name='comments'/>"; }
?>
<br>
<span>disqus shortname: </span><input type='text' class='wide' id='disqus' name='disqus' value='<?php echo $config['disqus']; ?>'/>
<p class="tinytext">Disqus comments are supported. Enter your Disqus site's shortname above to connect.</p>
<input type='submit' class='wide' name='update_comments' value='Update Comments'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class="settings_group">
<h3>Map Settings</h3>
<form id='map_form' name='map_form' action='settings_update.php' method='post'>
<input type='hidden' name='update_map' value='true'>
<input type='hidden' id='use_providers_plugin' name='use_providers_plugin' value='<?php echo $config['use_providers_plugin']; ?>'>
<input type='hidden' id='leaflet_provider' name='leaflet_provider' value='<?php echo $config['leaflet_provider']; ?>'>
<input type='hidden' id='use_google' name='use_google' value='<?php echo $config['use_google']; ?>'>
<input type='hidden' id='use_bing' name='use_bing' value='<?php echo $config['use_bing']; ?>'>

<span>default number of results shown: </span>
<input type='text' class='wide' id='max_view' name='max_view' value='<?php echo $config['max_view']; ?>'/>

<span>tile provider: </span>
<select name='provider' class="wide" id='provider_select' onChange='switch_map()' value='<?php echo $config['leaflet_provider']; ?>'></select>
<div id="settings_map"></div>

<div class="holder" id="map_options_google">
<span>google api key:</span>
<input type='text' class='wide' id='google_api_key' name='google_api_key' onChange='update_google_api()' value='<?php echo $config['google_api_key']; ?>'/><br>
<p class="tinytext">You'll need a Google Maps Javascript API key from Google in order to use Goolge Map tiles with CIBL.
Sign up for one <a href="https://developers.google.com/maps/documentation/javascript/get-api-key">here</a> and paste it into the box above.</p>
<span>additional layer: </span><br>
<input type='hidden' id='google_extra_layer' name='google_extra_layer' value='<?php echo $config['google_extra_layer']; ?>'>
<span>bicycling: </span><input type='checkbox' id='google_bicycling' name='google_bicycling' onChange='switch_map("google1")'/>
<span>transit: </span><input type='checkbox' id='google_transit' name='google_transit' onChange='switch_map("google2")'/>
<span>traffic: </span><input type='checkbox' id='google_traffic' name='google_traffic' onChange='switch_map("google3")'/>
<br/>
<span>map style:</span>
<textarea class="settings" id="google_style" name="google_style"><?php include $config_folder . '/google_style.php'; ?></textarea>
<p class="tinytext">Refer to <a href="https://developers.google.com/maps/documentation/javascript/styling">this page</a> for instructions on styling a Google map with JSON. Write all style objects between the [] square brackets.</p>
</div>

<div class="holder" id="map_options_bing">
<span>bing api key:</span>
<input type='text' class='wide' id='bing_api_key' name='bing_api_key' onChange='switch_map()' value='<?php echo $config['bing_api_key']; ?>'/><br>
<p class="tinytext">You'll need a Bing Maps API key from Microsoft in order to use Bing Maps tiles with CIBL.
Sign up for one <a href="https://www.microsoft.com/maps/create-a-bing-maps-key.aspx">here</a> and paste it into the box above.</p>
<span>imagery set: </span><br>
<input type='hidden' id='bing_imagery' name='bing_imagery' value='<?php echo $config['bing_imagery']; ?>'>
<select name='bing_imagery_select' class="wide" id='bing_imagery_select' onChange='switch_map()' value='<?php echo $config['bing_imagery']; ?>'>
<option id='Road' name='Road' value='Road'>Road</option>
<option id='Aerial' name='Aerial' value='Aerial'>Aerial</option>
<option id='AerialWithLabels' name='AerialWithLabels' value='AerialWithLabels'>AerialWithLabels</option>
</select>
</div>

<div class="holder" id="map_options_esri">
<p class="tinytext">Note: Esri/ArcGIS maps do not require any additional configuration to use, but ArcGIS requires that developers
<a href="https://developers.arcgis.com/sign-up/"> register for an account</a> and abide by their <a href="https://developers.arcgis.com/terms/">
terms of service</a>.
</div>

<div class="holder" id="map_options_custom">
<span>tiles url: </span>
<input type='text' class='wide' id='custom_url' name='map_url' onChange='switch_map()' value='<?php echo $config['map_url']; ?>'/><br>
<p class="tinytext"> If you have your own tile provider URL you may paste it above instead of using one of the presets.
Read the Wikipedia page on <a href="https://en.wikipedia.org/wiki/Tiled_web_map">tiled web maps</a> for more information about this schema.</p>
</div>

<input type='submit' class='wide' name='update_map' value='Update Map'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>OpenALPR</h3>
<form action='settings_update.php' method='post'>
<input type='hidden' name='update_openalpr' value='true'>
<span>openalpr api key: </span><input type='text' class='wide' name='openalpr_api_key' value='<?php echo $config['openalpr_api_key']; ?>'/>
<p class='tinytext'>OpenALPR is an web service that quickly parses license plate data from photographs. Obtain an <a href='//www.openalpr.com/demo-image.html'>OpenALPR API key</a> to enable.</p>
<select multiple name='openalpr_countries[]' class="wide multiple_select" id='openalpr_countries'>
<option value='us'<?php if(in_array('us',$config['openalpr_countries'])){ echo ' selected'; } ?>>us</option>
<option value='au'<?php if(in_array('au',$config['openalpr_countries'])){ echo ' selected'; } ?>>au</option>
<option value='auwide'<?php if(in_array('auwide',$config['openalpr_countries'])){ echo ' selected'; } ?>>auwide</option>
<option value='br'<?php if(in_array('br',$config['openalpr_countries'])){ echo ' selected'; } ?>>br</option>
<option value='eu'<?php if(in_array('eu',$config['openalpr_countries'])){ echo ' selected'; } ?>>eu</option>
<option value='fr'<?php if(in_array('fr',$config['openalpr_countries'])){ echo ' selected'; } ?>>fr</option>
<option value='gb'<?php if(in_array('gb',$config['openalpr_countries'])){ echo ' selected'; } ?>>gb</option>
<option value='kr'<?php if(in_array('kr',$config['openalpr_countries'])){ echo ' selected'; } ?>>kr</option>
<option value='kr2'<?php if(in_array('kr2',$config['openalpr_countries'])){ echo ' selected'; } ?>>kr2</option>
<option value='mx'<?php if(in_array('mx',$config['openalpr_countries'])){ echo ' selected'; } ?>>mx</option>
<option value='sg'<?php if(in_array('sg',$config['openalpr_countries'])){ echo ' selected'; } ?>>sg</option>
</select>
<p class='tinytext'>Country-specific training data used to identify license plates as described <a href='//github.com/openalpr/openalpr/tree/master/runtime_data/config'>here</a>. Multiple data can be selected, at least one must be active.</p>
<input type='submit' class='wide' name='update_openalpr' value='Update OpenALPR'/>
</form>
</div>
</div>

<div class='settings_box'>
<div class='settings_group'>
<h3>MySQL</h3>
<form action='settings_update.php' method='post'>
<input type='hidden' name='update_database' value='true'>
<?php
echo "<span>hostname: </span><input type='text' class='wide' name='sqlhost' value='" . "'/><br>\n";
echo "<span>username: </span><input type='text' class='wide' name='sqluser' value='" . "'/><br>\n";
echo "<span>password: </span><input type='password' class='wide' name='sqlpass' value='" . "'/><br>\n";
echo "<span>database: </span><input type='text' class='wide' name='database' value='" . "'/><br>\n";
?>
<input type='submit' class='wide' name='update_database' value='Update Database'/>
</form>
</div>
</div>

<script id="google_api_link" src="//maps.googleapis.com/maps/api/js?key=<?php echo $config['google_api_key']; ?>&v=3"></script>
<script id="leaflet_plugins_google" src="../scripts/leaflet-plugins-master/layer/tile/Google.js"></script>
<script id="leaflet_plugins_bing" src="../scripts/leaflet-plugins-master/layer/tile/Bing.js"></script>
<script src="../scripts/leaflet-providers.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	settings_map = L.map('settings_map');
	googleExtraLayer =  document.getElementById("google_extra_layer").value;
	if (googleExtraLayer == "BICYCLING") { document.getElementById("google_bicycling").checked = true; }
	if (googleExtraLayer == "TRANSIT") { document.getElementById("google_transit").checked = true; }
	if (googleExtraLayer == "TRAFFIC") { document.getElementById("google_traffic").checked = true; }
	bingImagery =  document.getElementById("bing_imagery").value;
	switch (bingImagery){
		case "Road":
			document.getElementById("bing_imagery_select").selectedIndex = 0; break;
		case "Aerial":
			document.getElementById("bing_imagery_select").selectedIndex = 1; break;
		case "AerialWithLabels":
			document.getElementById("bing_imagery_select").selectedIndex = 2; break;
	}

	var providers = L.TileLayer.Provider.providers;
	var providerOptions = "";
	providerOptions +=  "<option value='Custom'>Custom</option>\r\n";
	providerOptions +=  "<option value='Google'>Google</option>\r\n";
	providerOptions +=  "<option value='Bing'>Bing</option>\r\n";
	var providerString = "";
	for (var provider in providers){
		providerString = provider;
		selectedString = "";
		if (providerString == "NASAGIBS" ||
			providerString == "HERE" ||
			providerString == "OpenSeaMap" ||
			providerString == "OpenWeatherMap" ||
			providerString == "MapBox")
			{ continue; }
		providerOptions += "<option value=" + providerString + ">" + providerString + "</option>\r\n";
		if (providers[provider].hasOwnProperty("variants")){
			for (var variant in providers[provider].variants){
				providerString = provider + "." + variant;
				providerOptions +=  "<option value=" + providerString + ">" + providerString + "</option>\r\n";
			}
		}
	}

	var select = document.getElementById("provider_select");
	select.innerHTML = providerOptions;
	var leafletProvider = '<?php echo $config['leaflet_provider']; ?>';
	var options = select.options;
	for(var option, index = 0; option = options[index]; index++) {
		if(option.value == leafletProvider) {
			select.selectedIndex = index;
			break;
		}
	}

	switch_map();
});

function switch_map(option){
	var newProvider = document.getElementById("provider_select").options[provider_select.selectedIndex].value;

	console.log("Switched map to type: " + newProvider);

	if (newProvider == "Custom"){
		document.getElementById("map_options_custom").style.display = "block";
		document.getElementById("map_options_google").style.display = "none";
		document.getElementById("map_options_esri").style.display = "none";
		document.getElementById("map_options_bing").style.display = "none";
		document.getElementById("use_providers_plugin").value = 0;
		document.getElementById("use_google").value = 0;
		document.getElementById("use_bing").value = 0;
		document.getElementById("leaflet_provider").value = newProvider;
		settings_map.remove();
		document.getElementById("settings_map").innerHTML = "";
		var url = document.getElementById("custom_url").value;
		var tiles = L.tileLayer(url);
		settings_map = L.map('settings_map')
		.addLayer(tiles)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else if (newProvider == "Google"){
		document.getElementById("map_options_google").style.display = "block";
		document.getElementById("map_options_custom").style.display = "none";
		document.getElementById("map_options_esri").style.display = "none";
		document.getElementById("map_options_bing").style.display = "none";
		document.getElementById("use_providers_plugin").value = 0;
		document.getElementById("use_bing").value = 0;
		document.getElementById("use_google").value = 1;
		document.getElementById("leaflet_provider").value = newProvider;

		if (option == 'google1' || option == 'google2' || option == 'google3'){
			google_bicycling = document.getElementById("google_bicycling");
			google_transit = document.getElementById("google_transit");
			google_traffic = document.getElementById("google_traffic");
			switch (option){
				case ('google1'):
					if (google_bicycling.checked == false) { document.getElementById("google_extra_layer").value = "NONE"; }
					else {
						document.getElementById("google_extra_layer").value = "BICYCLING";
						google_transit.checked = false;
						google_traffic.checked = false;
					}
					break;
				case ('google2'):
					if (google_transit.checked == false) { document.getElementById("google_extra_layer").value = "NONE"; }
					else {
						document.getElementById("google_extra_layer").value = "TRANSIT";
						google_bicycling.checked = false;
						google_traffic.checked = false;
					}
					break;
				case ('google3'):
					if (google_traffic.checked == false) { document.getElementById("google_extra_layer").value = "NONE"; }
					else {
						document.getElementById("google_extra_layer").value = "TRAFFIC";
						google_bicycling.checked = false;
						google_transit.checked = false;
					}
					break;
			}
		}

		var options = <?php include $config_folder . '/google_style.php'; ?>;
		var extra = document.getElementById("google_extra_layer").value;
		settings_map.remove();
		document.getElementById("settings_map").innerHTML = "";;
		var tiles = new L.Google('ROADMAP', {
			mapOptions: {
				styles: options
			}
		}, extra);
		settings_map = L.map('settings_map')
		.addLayer(tiles)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 13);
	}
	else if (newProvider == "Bing"){
		document.getElementById("map_options_google").style.display = "none";
		document.getElementById("map_options_custom").style.display = "none";
		document.getElementById("map_options_esri").style.display = "none";
		document.getElementById("map_options_bing").style.display = "block";
		document.getElementById("use_providers_plugin").value = 0;
		document.getElementById("use_google").value = 0;
		document.getElementById("use_bing").value = 1;
		document.getElementById("leaflet_provider").value = newProvider;
		bingImagerySelect = document.getElementById("bing_imagery_select");
		imagerySet = bingImagerySelect.options[bingImagerySelect.selectedIndex].value;
		document.getElementById("bing_imagery").value = imagerySet;
		bingApiKey = document.getElementById("bing_api_key").value;
		settings_map.remove();
		document.getElementById("settings_map").innerHTML = "";
		var tiles = new L.BingLayer(bingApiKey, {type: imagerySet});
		settings_map = L.map('settings_map')
		.addLayer(tiles)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 13);
	}
	else{
		document.getElementById("map_options_custom").style.display = "none";
		document.getElementById("map_options_google").style.display = "none";
		document.getElementById("map_options_esri").style.display = "none";
		document.getElementById("map_options_bing").style.display = "none";
		if (newProvider.indexOf("Esri") != -1){
			document.getElementById("map_options_esri").style.display = "block";
		}
		document.getElementById("use_providers_plugin").value = 1;
		document.getElementById("use_google").value = 0;
		document.getElementById("use_bing").value = 0;
		settings_map.remove();
		document.getElementById("settings_map").innerHTML = "";
		var tiles = L.tileLayer.provider(newProvider);
		settings_map = L.map('settings_map')
		.addLayer(tiles)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
		document.getElementById("use_providers_plugin").value = 1;
		document.getElementById("leaflet_provider").value = newProvider;
	}
}

function update_google_api(){
	var key = document.getElementById("google_api_key").value;
	var googleURL = "https://maps.googleapis.com/maps/api/js?key=" + key + "&v=3";
	document.getElementById("google_api_link").src = googleURL;
	switch_map();
}

function calculate_centers() {
	var north = parseFloat(document.getElementById("north_bounds").value);
	var south = parseFloat(document.getElementById("south_bounds").value);
	var east = parseFloat(document.getElementById("east_bounds").value);
	var west = parseFloat(document.getElementById("west_bounds").value);
	var center_lat = (north + south) / 2;
	var center_long = (east + west) / 2;
	document.getElementById("center_lat").value = center_lat.toFixed(4);
	document.getElementById("center_long").value = center_long.toFixed(4);
	document.getElementById("mobile_center_lat").value = center_lat.toFixed(4);
	document.getElementById("mobile_center_long").value = center_long.toFixed(4);
}
</script>