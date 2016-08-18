<?php 

if (isset($_GET["forcedesktop"]) == false){
	include 'detectmobilebrowser.php';
}
else if ($_GET["forcedesktop"] == false) {
	include 'detectmobilebrowser.php';
}

include ('admin/config.php');

?>

<html>
<head>
<meta charset="UTF-8"> 
 
<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="css/style.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >

<!-- exif library plugin by Jacob Seidelin (https://github.com/jseidelin) -->
<script src="scripts/exif.js"></script>

<!-- leaflet -->
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />

<!-- mapbox -->
<script src='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.js'></script>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.css' rel='stylesheet' />

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>

<script type="text/javascript">

marker = new L.marker();
halt_changes = false;
single_view_open = false;
about_view_open = false;
submit_view_open = false;
noemail = true;
map_url = '<?php echo $config['map_url']; ?>';

$(document).ready(function() {
	initializeMaps();
	initializeDateTimePicker();
	setTimeout(function() { load_entries(); }, 250);
	$("#about").hide();
	$("#submission_form").hide();
	$(".results_form").hide();
	$(".single_view_pane").hide();
	$(".left_menu").show();
	
	submit_map.on('click', onSubmitClick);
	
	body_map.on('panend', function(e) { load_entries(); });
	body_map.on('moveend', function(e) { load_entries(); });
	body_map.on('click', function(e) { close_single_view(); });
	
	$("#toggle_submit, #toggle_submit2").click( function() {toggleView("submit")} );
	
	$("#toggle_about, #toggle_about2").click( function() {toggleView("about")} );
	
	$("#feedback").click( function(e) { showEmail(e) } );
	
	$('#submission_form').submit( function(e) { submitForm(e) } );
	
	$("#image_submission").on("change", function(e) { fillExifFields(e) } );
	
	$("#dismiss_success_dialog").click ( function() { $("#success_dialog").hide() } );
});

function initializeMaps() {

	//var mapboxTiles = L.tileLayer('https://{s}.tiles.mapbox.com/v3/infinitesunrise.k636mj38/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiaW5maW5pdGVzdW5yaXNlIiwiYSI6ImpkMjJZNDgifQ.XewtOwr2t6wlzCrwKDDArw');
	var tiles = L.tileLayer(map_url);

	//Set up two maps - Main body map and submission form map
	body_map = L.map('body_map')
		.addLayer(tiles)
		//.addLayer(bikelanes)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	markers = L.layerGroup().addTo(body_map);

	submit_map = L.map('submit_map')
		//.addLayer(tiles)
		.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 14);
}

function toggleView(view) {
	if (view == "about"){
		if (about_view_open){
			$("#about").animate({opacity: 'toggle', width: 'toggle'});
			$(".left_menu").show();
			about_view_open = false;
		}
		else{
			$("#about").animate({opacity: 'toggle', width: 'toggle'});
			$(".left_menu").hide();
			about_view_open = true;
		}
	}
	if (view == "submit"){
		if (submit_view_open){
			$("#submission_form").animate({opacity: 'toggle', width: 'toggle'});
			$(".left_menu").show();
			submit_view_open = false;
		}
		else {
			$("#submission_form").animate({opacity: 'toggle', width: 'toggle'});
			$(".left_menu").hide();
			submit_view_open = true;
		}
	}
}

function zoomToEntry(lat,lng,id) {
	halt_changes = true;
	single_view_url = "single_view.php?id=" + id;
	markers.clearLayers();
	
	soloMarker = L.marker([lat,lng]).addTo(body_map);
	markers.addLayer(soloMarker);
	body_map.panTo([lat,lng-.005]);
	setTimeout(function() { body_map.setZoom(17) }, 500);
	
	setTimeout(function() {
		$(".entry_list").hide();
		$(".single_view_pane").load(single_view_url);
		if (single_view_open == false){
			$(".single_view_pane").animate({opacity: 'toggle', width: 'toggle'});
			single_view_open = true;
		}
	}, 1000);
	
	setTimeout(function() { halt_changes = false; }, 1000);
}

function close_single_view() {
	if (single_view_open == true && halt_changes == false) {
		$(".single_view_pane").animate({opacity: 'toggle', width: 'toggle'});
		single_view_open = false;
		$(".entry_list").show();
		var west = body_map.getBounds().getWest();
		var east = body_map.getBounds().getEast();
		var south = body_map.getBounds().getSouth();
		var north = body_map.getBounds().getNorth();
		markers.clearLayers();
		var load_url = "entry_list.php?west=" + west + "&east=" + east + "&south=" + south + "&north=" + north;
		$( "#inner_inner_container" ).load( load_url );
	}
}

function initializeDateTimePicker() {
	$('#datetimepicker').datetimepicker({format:'m/d/Y g:iA'});
	var d = new Date();
	var month = d.getMonth()+1;
	var day = d.getDate();
	var year = d.getFullYear();
	var hour = d.getHours();
	var meridiem = "AM"; if (hour > 12){ meridiem = "PM"; }
	if (hour > 12){ hour -= 12; }
	if (hour == 0){ hour = 12; }
	var min = d.getMinutes();
	var date_string = month + "/" + day + "/" + year + " " + hour + ":" + min + meridiem;
	document.getElementById('datetimepicker').value = date_string;
}

function showEmail(e) {
	if (noemail == true){
		e.preventDefault();
		$("#feedback").load("contact.php");
		noemail = false;
	}
}

function fillExifFields(e) {
	EXIF.getData(e.target.files[0], function() {			
		//Auto-enter location data
		if(EXIF.getTag(this, "GPSLatitude")){
			var lat_deg = EXIF.getTag(this, "GPSLatitude")[0];
			var lat_min = EXIF.getTag(this, "GPSLatitude")[1];
			var lat_sec = EXIF.getTag(this, "GPSLatitude")[2];
			var lng_deg = EXIF.getTag(this, "GPSLongitude")[0];
			var lng_min = EXIF.getTag(this, "GPSLongitude")[1];
			var lng_sec = EXIF.getTag(this, "GPSLongitude")[2];
			var gps_lat = (lat_deg+(((lat_min*60)+lat_sec))/3600); //DMS to decimal
			var gps_lng = -(lng_deg+(((lng_min*60)+lng_sec))/3600); //DMS to decimal
			document.getElementById("latitude").value = gps_lat;
			document.getElementById("longitude").value = gps_lng;
			submit_map.removeLayer(marker);
			marker = new L.marker([gps_lat, gps_lng]).addTo(submit_map);
			submit_map.panTo([gps_lat, gps_lng]);
			var gps_text = "Latitude: " + gps_lat.toFixed(6) + " Longitude: " + gps_lng.toFixed(6);
			document.getElementById("gps_coords").innerHTML = gps_text;
			document.getElementById("map_prompt").innerHTML = "Location detected:";
		}
		
		//Auto-enter time and date
		if(EXIF.getTag(this, "DateTimeOriginal")){
			var capture_time = EXIF.getTag(this, "DateTimeOriginal");
			var exif_date_and_time = capture_time.split(" ");
			var exif_date = exif_date_and_time[0].split(":");
			var exif_time = exif_date_and_time[1].split(":");
			var exif_year = exif_date[0];
			var exif_month = exif_date[1];
			var exif_day = exif_date[2];
			var exif_meridiem = "AM";
			var exif_hour = exif_time[0];
			if (exif_hour > 12) { exif_hour -= 12; exif_meridiem = "PM"; }
			var exif_minute = exif_time[1];
			var exif_date_final = exif_month + "/" + exif_day + "/" + exif_year + " " + exif_hour + ":" + exif_minute + exif_meridiem;
			document.getElementById('datetimepicker').value = exif_date_final;
		}
	});
}

function submitForm(e) {
	e.preventDefault();
	var formData = new FormData();
	formData.append( 'image_submission', $('#image_submission')[0].files[0] );
	formData.append( 'plate', document.getElementById("plate").value );
	formData.append( 'lat', document.getElementById("latitude").value );
	formData.append( 'lng', document.getElementById("longitude").value );
	formData.append( 'date', document.getElementById("datetimepicker").value );
	formData.append( 'state', document.getElementById("state").value );
	formData.append( 'street1', document.getElementById("street1").value );
	formData.append( 'street2', document.getElementById("street2").value );
	formData.append( 'description',document.getElementById("comments").value );
	$.ajax({
	  url: '/submission.php',
	  type: 'POST',
	  data: formData,
	  processData: false,
	  contentType: false,
	  mimeType: 'multipart/form-data',
	  success: function (a) {
		$("#submission_form").animate({opacity: 'toggle', width: 'toggle'});
		setTimeout(function() { $('#results_form').html(a); }, 500);
		setTimeout(function() { $("#results_form").animate({opacity: 'toggle', width: 'toggle'}); }, 500);
	  },
	  error: function(a) {
		alert( "something went wrong: " + a);
	  }
	});
}

function load_entries() {
	if (halt_changes == false) {
		if (single_view_open == true){
			$(".single_view_pane").animate({opacity: 'toggle', width: 'toggle'});
			single_view_open = false;
		}
		$(".entry_list").show();
		var west = body_map.getBounds().getWest();
		var east = body_map.getBounds().getEast();
		var south = body_map.getBounds().getSouth();
		var north = body_map.getBounds().getNorth();
		markers.clearLayers();
		var load_url = "entry_list.php?west=" + west + "&east=" + east + "&south=" + south + "&north=" + north;
		$( "#inner_inner_container" ).load( load_url );
	}
}

function onSubmitClick(e) {
    submit_map.removeLayer(marker);
    marker = new L.marker(e.latlng).addTo(submit_map);
    var gps_text = "Latitude: " + e.latlng.lat.toFixed(6) + " Longitude: " + e.latlng.lng.toFixed(6);
    document.getElementById("gps_coords").innerHTML = gps_text;
    document.getElementById("latitude").value = e.latlng.lat;
    document.getElementById("longitude").value = e.latlng.lng;
}

function limitText() {
	var comments = document.getElementById("comments");
	if (comments.value.length > 200) {
		comments.value = comments.value.substring(0, 200);
	}
	else {
		var count = comments.value.length;
		document.getElementById("character_limit").innerHTML = 200 - count;
	}
}

</script>

</head>

<body>
<div id="body_map">
</div>

<?php

if (isset($_GET['setup_success_dialog'])){
	echo "<div class=\"flex_container_dialog_float\" id=\"success_dialog\">\n";
	echo "<div class=\"setup_centered\">\n";
	echo "<div class=\"settings_group\">\n";
	echo "<h3>Setup Complete!</h3>\n";
	echo "<p>The site is now be ready to receive submissions.</p>\n";
	echo "<p>To change site settings and approve user submissions, 
		point your browser at the <a href=\"/admin\">/admin</a> directory 
		and log in with the credentials created during setup.</p>";
	echo "<p>Happy reporting!</p>\n";
	echo "</div>\n";
	echo "<div class=\"settings_group\">\n";
	echo "<form>\n";
	echo "<input class=\"bold_button\" type=\"button\" id=\"dismiss_success_dialog\" value=\"DISMISS\"/>\n";
	echo "</form>\n";
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n";
}
?>

<!-- LEFT MENU -->
<div class="left_menu">
<div class="left_menu_item">
<span><?php echo $config['site_name']; ?></span>
</div><br>
<div class="left_menu_item" id="toggle_submit">
<span>SUBMIT</span>
</div><br>
<div class="left_menu_item" id="toggle_about">
<span>ABOUT</span>
</div>
</div>

<!-- SINGLE VIEW PANE -->
<div class="single_view_pane" id="single_view_pane">
</div>

<!-- SUBMISSION FORM -->
<div class="submission_form" id="submission_form">

<div class="top_dialog_button" id="toggle_submit2">
<span>CANCEL</span>
</div>

<form id="the_form" action="submission.php" enctype="multipart/form-data">
    <span>Attach your image:</span> <input type="file" class="bold5" name="image_submission" id="image_submission"><br>
    <span>License plate:</span> <input type="text" name="plate" id="plate" class="bold1" maxlength="7">&nbsp&nbsp&nbsp
    <span> State: </span>
    <select name="state" id="state" class="bold4">
    <option value="NY">NY</option>
    <option value="NJ">NJ</option>
    <option value="POLICE">POLICE</option>
    <option>--</option>
    <option value="AL">AL</option>
    <option value="AK">AK</option>
    <option value="AZ">AZ</option>
    <option value="AR">AR</option>
    <option value="CA">CA</option>
    <option value="CO">CO</option>
    <option value="CT">CT</option>
    <option value="DE">DE</option>
    <option value="DC">DC</option>
    <option value="FL">FL</option>
    <option value="GA">GA</option>
    <option value="HI">HI</option>
    <option value="IA">IA</option>
    <option value="ID">ID</option>
    <option value="IL">IL</option>
    <option value="IN">IN</option>
    <option value="KS">KS</option>
    <option value="KY">KY</option>
    <option value="LA">LA</option>
    <option value="ME">ME</option>
    <option value="MD">MD</option>
    <option value="MA">MA</option>
    <option value="MI">MI</option>
    <option value="MN">MN</option>
    <option value="MS">MS</option>
    <option value="MO">MO</option>
    <option value="MT">MT</option>
    <option value="NE">NE</option>
    <option value="NV">NV</option>
    <option value="NH">NH</option>
    <option value="NM">NM</option>
    <option value="NC">NC</option>
    <option value="ND">ND</option>
    <option value="OH">OH</option>
    <option value="OK">OK</option>
    <option value="OR">OR</option>
    <option value="PA">PA</option>
    <option value="RI">RI</option>
    <option value="SC">SC</option>
    <option value="SD">SD</option>
    <option value="TN">TN</option>
    <option value="TX">TX</option>
    <option value="UT">UT</option>
    <option value="VT">VT</option>
    <option value="VA">VA</option>
    <option value="WA">WA</option>
    <option value="WV">WV</option>
    <option value="WI">WI</option>
    <option value="WY">WY</option>
    <option value="OTHER">OTHER</option>
    </select> &nbsp&nbsp&nbsp
    <span> When:</span> <input type="text" name="date" class="bold2" id="datetimepicker"><br>
	<span>Cross streets (optional): <input type="text" name="street1" id="street1" class="bold3"> &amp <input type="text" name="street2" id="street2" class="bold3"></span><br>
    <span id="map_prompt">Click to mark location:</span><br>
	<div id="submit_map"></div>
	<span id="gps_coords">Latitude: ... Longitude: ...</span><br>
	<span>Any additional info (
	<div id="character_limit">200</div> characters):</span><br>
	<textarea name="description" onKeyDown="limitText();" onKeyUp="limitText();" class="comments" id="comments" value="Brief description of the situation if desired, and any other info"></textarea><br>
	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">
	<input type="submit" class="submit_button" value="SUBMIT" name="submit"><br>
</form>
</div>

<!-- ABOUT BOX -->
<div id="about">
<?php echo stripslashes(htmlspecialchars_decode($config['about_text'])); ?>
<div class="bottom_dialog_button" id="toggle_about2">
<span>BACK</span>
</div>
</div>

<!-- RESULTS FORM -->
<div class="results_form" id="results_form"></div>

<!-- LIST OF ENTRIES -->
<div class="entry_list" id="entry_list">
<div class="inner_container">
<div class="inner_inner_container" id="inner_inner_container">
</div>
</div>
</div>

</body>

</html>