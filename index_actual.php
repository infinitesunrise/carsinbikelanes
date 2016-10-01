<?php
include ('admin/config_pointer.php');
$single_view_id = (isset($_GET['single_view'])) ? $_GET['single_view'] : 0;
include ('mobile_detect.php');
$single_view_details = '';
if (isset($_GET['single_view'])){
	$query = 'SELECT gps_lat, gps_long FROM cibl_data WHERE increment=' . $_GET['single_view'];
	$result = mysqli_fetch_array($connection->query($query));
	$single_view_lat = $result[0];
	$single_view_long = $result[1];
	$single_view_details = $single_view_lat . ', ' . $single_view_long . ', ' . $single_view_id;
}
include ('mobile_detect.php');
?>

<html>
<head>
<meta charset="UTF-8">

<!-- local stylesheets -->
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="css/plates.css" />

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
<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css" />

<!-- mapbox -->
<script src='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.js'></script>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.css' rel='stylesheet' />

<!-- google fonts -->
<link href='//fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'>

<!-- license plate font by Dave Hansen -->
<link href='css/license-plate-font.css' rel='stylesheet' type='text/css'>

<!-- leaflet-providers by leaflet-extras (https://github.com/leaflet-extras) -->
<script src="scripts/leaflet-providers.js"></script>

<!-- Google Javascript API with current key -->
<script id="google_api_link" src="<?php echo '//maps.google.com/maps/api/js?key=' . $config['google_api_key']; ?>"></script>

<!-- leaflet-plugins by Pavel Shramov (https://github.com/shramov/leaflet-plugins) -->
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Google.js"></script>
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Bing.js"></script>

<script type="text/javascript">

windows = {
	single_view: false,
	about_view: false,
	submit_view: false,
	entry_list: false,
	stop_load_entries: false
}
marker = new L.marker();
max_view = <?php echo $config['max_view']; ?>;

$(document).ready(function() {
	initializeMaps();
	initializeDateTimePicker();
	$("#about_view").hide();
	$("#submission_form").hide();
	$(".results_form").hide();
	$('#entry_view').hide();
	$(".single_view").hide();
	$(".right_menu").show();
	setTimeout(function() {
		if(<?php echo $single_view_id; ?>){ zoomToEntry(<?php echo $single_view_details; ?>); }
		else { load_entries(); }
	}, 250);

	body_map.on('panend', function(e) { load_entries(); });
	body_map.on('moveend', function(e) { load_entries(); });
	body_map.on('click', function(e) { load_entries(); });
	submit_map.on('click', onSubmitClick);

	$("#submit_link").click( function() { open_window('submit_view') } );

	$("#about_link").click( function() {open_window('about_view')} );

	$("#feedback").click( function(e) { showEmail(e) } );

	$('#submission_form').submit( function(e) { submitForm(e) } );

	$('#image_submission').on('change', function(e) {
		fill_plate_and_state();
		fillExifFields(e);
	});

	$("#dismiss_success_dialog").click ( function() { $("#success_dialog").hide() } );
	
	$('#state').change(function(){
	if ($('#state').val() == 'UNKNOWN' || $('#state').val() == 'NONE'){
			$('#plate').val($('#state').val());
		}
	});
});

function zoomToEntry(lat,lng,id) {
	windows.stop_load_entries = true;
	single_view_url = "single_view.php?id=" + id;
	$("#single_view").load(single_view_url);
	open_window('none');
	body_map.setView([lat,lng-.005], 17);
	soloMarker = L.marker([lat,lng]).addTo(body_map);
	markers.clearLayers();
	markers.addLayer(soloMarker);
	setTimeout(function() { open_window('single_view'); }, 700);
	setTimeout(function() { windows.stop_load_entries = false; }, 500);	;
}

function open_window(window) {
	if (windows.single_view == true) {
		$('#single_view').animate({opacity: 'toggle', left: '-865px'});
	}
	if (windows.about_view == true) {
		$('#about_view').animate({opacity: 'toggle', right: '-605px'});
		$('.right_menu').delay(300).animate({opacity: 'toggle'});
	}
	if (windows.submit_view == true) {
		$('#submission_form').animate({opacity: 'toggle', right: '-565px'});
		$('.right_menu').delay(300).animate({opacity: 'toggle'});
	}
	windows.single_view = false; windows.about_view = false; windows.submit_view = false;

	if (windows.entry_list == true && window == 'none') {
		$('#entry_view').animate({opacity: 'toggle', left: '-565px'});
		windows.entry_list = false;
	}
	if (window == 'single_view' && windows.single_view == false){
		$('#single_view').animate({opacity: 'toggle', left: '0px'});
		windows.single_view = true;
	}
	if (window == 'about_view' && windows.about_view == false){
		$('#about_view').animate({opacity: 'toggle', right: '0px'});
		$('.right_menu').hide();
		windows.about_view = true;
	}
	if (window == 'submit_view' && windows.submit_view == false){
		$('#submission_form').animate({opacity: 'toggle', right: '0px'});
		$('.right_menu').hide();
		windows.submit_view = true;
	}
	if (window == 'entry_list' && windows.entry_list == false){
		$('#entry_view').animate({opacity: 'toggle', left: '0px'});
		windows.entry_list = true;
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

function fill_plate_and_state(){
	var openalpr = '<?php echo $config['openalpr_api_key']; ?>';
	if (!openalpr) { return; }
	$('#plate').css('background', 'url(\'css/loader.svg\') 50% no-repeat');
	$('#plate').css('background-size', '50%');
	$('#plate').css('background-color', 'white');
	var url = 'https://api.openalpr.com/v1/recognize';
	var data = new FormData();
	data.append('secret_key', openalpr);
	data.append('tasks', ['plate']);
	data.append('image', $('#image_submission')[0].files[0]);
	data.append('country', 'us');
	var reply;

	function listener() {
		var reply = JSON.parse(this.responseText);
		var x1 = reply['plate']['img_width'] / 2;
		var y1 = reply['plate']['img_height'] / 2;
		var results = reply['plate']['results'];
		if (results.length > 0){
			var smallest_distance = (x1 >= y1) ? x1 : y1;
			var best = 0;
			$.each( results, function(i) {
				var x2 = (results[i]['coordinates'][0]['x'] +
						results[i]['coordinates'][1]['x'] +
						results[i]['coordinates'][2]['x'] +
						results[i]['coordinates'][3]['x']) / 4;
				var y2 = (results[i]['coordinates'][0]['y'] +
						results[i]['coordinates'][1]['y'] +
						results[i]['coordinates'][2]['y'] +
						results[i]['coordinates'][3]['y']) / 4;
				var x = x1 - x2;
				var y = y1 - y2;
				var distance = Math.sqrt(x*x + y*y);;
				if (distance < smallest_distance){
					best = i;
					smallest_distance = distance;
				}
			});
			$('#plate').css('background', 'white');
			$('#plate').val(reply['plate']['results'][best]['plate']);
			$('#state').val(reply['plate']['results'][best]['region'].toUpperCase());
		}
		else {
			$('#plate').css('background', 'white');
		}
	}
	var request = new XMLHttpRequest();
	request.addEventListener('load', listener);
	request.open('POST', url);
	request.send(data);
}

function fill_streets(){
	var url = '//geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode';
	var location = '{x:' + $('#longitude').val() + ',y:' + $('#latitude').val() + '}';
	var data = new FormData();
	data.append('location', location);
	data.append('distance', '150');
	data.append('returnIntersection', 'true');
	data.append('f', 'json');
	var reply;
	function listener() {
		var response = JSON.parse(this.responseText);
		var intersection = response['address']['Address'].split(" & ");
		$('#street1').val(intersection[0]);
		$('#street2').val(intersection[1]);
	}
	var request = new XMLHttpRequest();
	request.addEventListener('load', listener);
	request.open('POST', url);
	request.send(data);
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
			fill_streets();
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
	$('#upload_prompt').empty();
	$('#upload_button').css('background', 'url(\'css/loader.svg\') 50% no-repeat');
	$('#upload_button').css('background-size', '10%');
	$('#upload_button').css('background-color', 'lightgray');
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
	formData.append( 'upload','true' );
	formData.append( 'source','desktop' );
	$.ajax({
	  url: '/submission.php',
	  type: 'POST',
	  data: formData,
	  processData: false,
	  contentType: false,
	  mimeType: 'multipart/form-data',
	  success: function (a) {
		open_window('none');
		$('#results_form_container').empty();
		$('#results_form_container').html(a);
		$('#upload_button').css('background', 'none');
		$('#upload_button').css('background-color', 'lightgray');
		$('#upload_prompt').append('UPLOAD!');
		$("#results_form").animate({opacity: 'toggle', right: '0px'});
	  },
	  error: function(a) {
		alert( "something went wrong: " + a);
		$('#upload_button').css('background', 'none');
		$('#upload_button').css('background-color', 'lightgray');
		$('#upload_prompt').append('UPLOAD!');
	  }
	});
}

function load_entries() {
	if (windows.stop_load_entries == false) {
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		var west = body_map.getBounds().getWest();
		var east = body_map.getBounds().getEast();
		var south = body_map.getBounds().getSouth();
		var north = body_map.getBounds().getNorth();
		var load_url = "entry_list.php?west=" + west + "&east=" + east + "&south=" + south + "&north=" + north + "&max_view=" + max_view;
		$( "#entry_list_content" ).load( load_url, function(){
				open_window('entry_list');
				$('#loading').css('background', 'none');
				setTimeout(function(){
					resize_entry_list();
					$('#entry_list_content').css('overflow-y','scroll'); //fixes bug where firefox doesn't scroll first list loaded
				}, 500);
			});
	}
}

function plate_search(plate) {
	if (windows.stop_load_entries == false) {
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		var load_url = 'entry_list.php?plate=' + plate;
		windows.stop_load_entries = true; //Will be set false again by entry_list.php
		$( "#entry_list_content" ).load( load_url, function(){
				open_window('entry_list');
				$('#loading').css('background', 'none');
				setTimeout(function(){ resize_entry_list(); }, 500);
			});;
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

function resize_entry_list(){
	total_height = 10;
	$('.column_entry').map( function(){ total_height += $(this).outerHeight(); });
	if (total_height < document.body.clientHeight){ $('#entry_view').animate({ height: total_height }); }
	else { $('#entry_view').animate({ height: '97vh' }); }
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

function initializeMaps() {

	if (<?php echo $config['use_providers_plugin']; ?>) {
		body_map = L.map('body_map');
		try { var tiles = L.tileLayer.provider('<?php echo $config['leaflet_provider']; ?>'); }
		catch (err) { console.log(err); }
		body_map.addLayer(tiles);
		body_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);

		submit_map = L.map('submit_map');
		try { var tiles2 = L.tileLayer.provider('<?php echo $config['leaflet_provider']; ?>'); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else if (<?php echo $config['use_google']; ?>) {
		body_map = L.map('body_map');
		<?php if ($config['use_google']){
			echo "var options = ";
			include $config_folder . '/google_style.php';
			echo ";\n"; }
		?>
		var extra = <?php echo "\"" . $config['google_extra_layer'] . "\";\n"; ?>
		try {
			var tiles = new L.Google('ROADMAP', {
					mapOptions: {
						styles: options
					}
				}, extra);
		}
		catch (err) { console.log(err); }
		body_map.addLayer(tiles);
		body_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);

		submit_map = L.map('submit_map');
		try {
			var tiles2 = new L.Google('ROADMAP', {
					mapOptions: {
						styles: options
					}
				}, extra);
		}
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else if (<?php echo $config['use_bing']; ?>) {
		body_map = L.map('body_map');
		imagerySet = '<?php echo $config['bing_imagery']; ?>';
		bingApiKey = '<?php echo $config['bing_api_key']; ?>';
		try { var tiles = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
		body_map.addLayer(tiles);
		body_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);

		submit_map = L.map('submit_map');
		try { var tiles2 = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else {
		body_map = L.map('body_map');
		try { var tiles = L.tileLayer('<?php echo $config['map_url']; ?>'); }
		catch (err) { console.log(err); }
		body_map.addLayer(tiles);
		body_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);

		submit_map = L.map('submit_map');
		try { var tiles2 = L.tileLayer('<?php echo $config['map_url']; ?>'); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}

	markers = L.layerGroup().addTo(body_map);
	newMarkers = L.layerGroup();
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

<!-- RIGHT MENU -->
<div class="right_menu">
<div class="right_menu_item">
<span><?php echo $config['site_name']; ?></span>
</div>
<br>
<div class="right_menu_item" id="submit_link">
<span>SUBMIT</span>
</div>
<br>
<div class="right_menu_item" id="about_link">
<span>ABOUT</span>
</div>
<br>
<div class="right_menu_item" id="loading">
<span></span>
</div>
</div>

<!-- SUBMISSION FORM -->
<div class="submission_form" id="submission_form">
<div class="submission_form_container">

<div class="top_dialog_button" onClick="open_window('entry_list')">
<span>&#x2A09</span>
</div>

<form id="the_form" action="submission.php" style="margin-bottom: 0px" enctype="multipart/form-data">

	<div style="width: 100%">
    <span class="submit_form_item">Image: </span><input type="file" class="submit_form_item" name="image_submission" id="image_submission"><br>
	</div>

	<div class="submit_form_row">
	<div>
    <span class="submit_form_item">Plate: </span><input type="text" name="plate" id="plate" class="submit_form_item" style="width:70px" maxlength="8">
	</div>

	<div>
    <span class="submit_form_item"> State: </span>
    <select name="state" id="state" class="submit_form_item">
    <option value="NY">NY</option>
    <option value="NJ">NJ</option>
    <option value="NYPD">NYPD</option>
    <option value="FDNY">FDNY</option>
	<option value="UNKNOWN">UNKNOWN</option>
	<option value="NONE">NONE</option>
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
    </select>
	</div>

	<div>
    <span class="submit_form_item"> When:</span> <input type="text" name="date" class="submit_form_item" id="datetimepicker">
	</div>
	</div>

	<div class="submit_form_row">
	<div>
	<span class="submit_form_item">Cross streets (optional): </span>
	</div>
	<div>
	<input type="text" name="street1" id="street1" class="submit_form_item" style="width:140px">
	<span class="submit_form_item">&amp</span>
	<input type="text" name="street2" id="street2" class="submit_form_item" style="width:140px">
	</div>
	</div>

    <span id="map_prompt">Click to mark location:</span>
	<div id="submit_map"></div>
	<span id="gps_coords">Latitude: ... Longitude: ...</span>
	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">

	<div class="submit_form_row">
	<span class="submit_form_item">Any additional info (
	<div id="character_limit">200</div> characters):</span>
	</div>

	<textarea name="description" onKeyDown="limitText();" onKeyUp="limitText();" class="description" id="comments"></textarea>

	<!--<div class="submit_form_row">
	<input type="submit" class="submit_form_item" style="width:100%" value="SUBMIT" name="upload">
	</div>-->

	<label id='upload_button' class='upload_button'>
	<span id='upload_prompt' class='v-centered'>UPLOAD!</span>
	<input type="submit" name="upload" id="upload"/>
	</label>
	
</form>
</div>
</div>

<!-- ABOUT VIEW -->
<div id='about_view' class='about_view'>
<div class="top_dialog_button" onClick="open_window('entry_list')">
<span>&#x2A09</span>
</div>
<?php echo stripslashes(htmlspecialchars_decode($config['about_text'])); ?>
</div>


<!-- RESULTS VIEW -->
<div class="results_form" id="results_form">
<div class="top_dialog_button" onClick="open_window('entry_list')">
<span>&#x2A09</span>
</div>
<div class="results_form_container" id="results_form_container">
</div>
</div>

<!-- ENTRIES VIEW -->
<div class="entry_view" id="entry_view">
<div class="entry_list_content" id="entry_list_content">
</div>
</div>

<!-- SINGLE VIEW -->
<div class="single_view" id="single_view"></div>

</body>

</html>
