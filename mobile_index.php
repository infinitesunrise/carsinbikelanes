<html>
<head>

<?php
include ('admin/config_pointer.php');
$single_view_id = (isset($_GET['single_view'])) ? $_GET['single_view'] : 0;
$single_view_details = '';
if (isset($_GET['single_view'])){
	$query = 'SELECT gps_lat, gps_long FROM cibl_data WHERE increment=' . $_GET['single_view'];
	$result = mysqli_fetch_array($connection->query($query));
	$single_view_lat = $result[0];
	$single_view_long = $result[1];
	$single_view_details = $single_view_lat . ', ' . $single_view_long . ', ' . $single_view_id;
}
?>

<meta name="viewport" content="user-scalable=0"/>

<!-- local stylesheets -->
<link rel="stylesheet" type="text/css" href="css/mobile_style.css" />
<link rel="stylesheet" type="text/css" href="css/plates.css" />

<!-- jquery links -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >

<!-- exif library plugin by Jacob Seidelin (https://github.com/jseidelin) -->
<script src="scripts/exif.js"></script>

<!-- leaflet -->
<script src="//cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="//cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />

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
marker = new L.marker();
windows = {
	single_view: false,
	about_view: false,
	submit_view: false,
	entry_view: false,
	results_view: false,
	submit_link_clicked: true,
	about_link_clicked: true,
	stop_load_entries: false
}

auto_complete = {
	plate: false,
	exif: false,
	streets: false
}

$(document).ready(function() {
	$('#entry_view').hide();
	$('#submit_view').hide();
	$('#about_view').hide();
	$('#single_view').hide();
	$('#results_view').hide();

	initialize_body_map();

	if(<?php echo $single_view_id; ?>){ zoomToEntry(<?php echo $single_view_details; ?>); }
	else{ load_entries(); }

	$('#submit_link').click(function(){ open_window('submit_view'); initialize_submit_view(); });
	$('#about_link').click(function(){ open_window('about_view'); });
});

function change_nav(operation){
	switch (operation){
		case 'close':
			if (windows.submit_link_clicked){
				flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
			}
			if (windows.about_link_clicked){
				flip($('#about_link'), '<?php echo $config['site_name'] ?>', 'about_link_clicked', false);
			}
			break;
		case 'about':
			if (windows.about_link_clicked){
				flip($('#about_link'), '<?php echo $config['site_name'] ?>', 'about_link_clicked', false);
			}
			else {
				flip($('#about_link'), 'BACK TO MAP', 'about_link_clicked', true);
			}
			if (windows.submit_link_clicked){
				flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
			}
			break;
		case 'submit':
			if (windows.submit_link_clicked){
				flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
			}
			else {
				flip($('#submit_link'), 'MAP', 'submit_link_clicked', true);
			}
			if (windows.about_link_clicked){
				flip($('#about_link'), '<?php echo $config['site_name'] ?>', 'about_link_clicked', false);
			}
			break;
	}
}

function flip(element, content, key, value){
	element.animate({'top': '-5vh'}, function(){
		element.html("<span class='navspan'>" + content + "</span>");
	})
	.animate({'top': '0vh'});
	if (key == 'submit_link_clicked'){windows.submit_link_clicked = value; }
	if (key == 'about_link_clicked'){windows.about_link_clicked = value; }
}

function initialize_body_map() {
	if (<?php echo $config['use_providers_plugin']; ?>) {
		body_map = L.map('body_map', { zoomControl:false });
		try { var tiles = L.tileLayer.provider('<?php echo $config['leaflet_provider']; ?>'); }
		catch (err) { console.log(err); }
	}
	else if (<?php echo $config['use_google']; ?>) {
		body_map = L.map('body_map', { zoomControl:false });
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
	}
	else if (<?php echo $config['use_bing']; ?>) {
		body_map = L.map('body_map', { zoomControl:false });
		imagerySet = '<?php echo $config['bing_imagery']; ?>';
		bingApiKey = '<?php echo $config['bing_api_key']; ?>';
		try { var tiles = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
	}
	else {
		body_map = L.map('body_map', { zoomControl:false });
		try { var tiles = L.tileLayer('<?php echo $config['map_url']; ?>'); }
		catch (err) { console.log(err); }
	}
	body_map.addLayer(tiles);
	body_map.setView([<?php echo $config['mobile_center_lat'] ?>, <?php echo $config['mobile_center_long'] ?>], 12);
	body_map.on('panend', function(e) { load_entries(); });
	body_map.on('moveend', function(e) { load_entries(); });
	body_map.on('click', function(e) { load_entries(); });
	markers = L.layerGroup().addTo(body_map);
	newMarkers = L.layerGroup();
}

function initialize_submit_view() {
	if (<?php echo $config['use_providers_plugin']; ?>) {
		submit_map = L.map('submit_map', { zoomControl:false });
		try { var tiles2 = L.tileLayer.provider('<?php echo $config['leaflet_provider']; ?>'); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else if (<?php echo $config['use_google']; ?>) {
		submit_map = L.map('submit_map', { zoomControl:false });
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
		submit_map = L.map('submit_map', { zoomControl:false });
		try { var tiles2 = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}
	else {
		submit_map = L.map('submit_map', { zoomControl:false });
		try { var tiles2 = L.tileLayer('<?php echo $config['map_url']; ?>'); }
		catch (err) { console.log(err); }
		submit_map.addLayer(tiles2);
		submit_map.setView([<?php echo $config['center_lat'] ?>, <?php echo $config['center_long'] ?>], 12);
	}

	submit_map.on('click', submit_map_click);

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

	jQuery('#datetimepicker').datetimepicker({format:'m/d/Y g:iA'});

	document.getElementById("image_submission").onchange = function(e) {
		$('#image_prompt').html('ATTACHED!');

		auto_scroll('reset');

		fill_plate_and_state();

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
				auto_scroll('exif');
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

	$('#mobile_submission_form').submit( function(e) {
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
		formData.append( 'upload','true' );
		formData.append( 'source','mobile' );
		$.ajax({
		  url: '/submission.php',
		  type: 'POST',
		  data: formData,
		  processData: false,
		  contentType: false,
		  mimeType: 'multipart/form-data',
		  success: function(a) {
			$('#results_view').html(a);
			open_window('results_view');
		  },
		  error: function(a) {
			alert( "something went wrong: " + a);
		  }
		});
	});
}

function auto_scroll(autofilled){
	switch(autofilled){
		case 'plate':
			auto_complete.plate = true;
			break;
		case 'exif':
			auto_complete.exif = true;
			break;
		case 'streets':
			auto_complete.streets = true;
			break;
		case 'reset':
			auto_complete.plate = false;
			auto_complete.exif = false;
			auto_complete.streets = false;
			break;
	}
	if (auto_complete.plate == true &&
		auto_complete.exif == true &&
		auto_complete.streets == true){
		var offset = -100;
		$('#submit_view').animate({
			scrollTop: $("#description-title").offset().top + offset
		}, 2000);
	}
}

function fill_plate_and_state(){
	var openalpr = '<?php echo $config['openalpr_api_key']; ?>';
	if (!openalpr) { return; }
	$('#plate').css('background', 'url(\'css/loader.svg\') 50% no-repeat');
	$('#plate').css('background-size', '20%');
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
			auto_scroll('plate');
		}
		else {
			auto_scroll('reset');
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
		auto_scroll('streets');
	}
	var request = new XMLHttpRequest();
	request.addEventListener('load', listener);
	request.open('POST', url);
	request.send(data);
}

function submit_map_click(e) {
    submit_map.removeLayer(marker);
    marker = new L.marker(e.latlng).addTo(submit_map);
    var gps_text = "Latitude: " + e.latlng.lat.toFixed(6) + " Longitude: " + e.latlng.lng.toFixed(6);
    document.getElementById("gps_coords").innerHTML = gps_text;
    document.getElementById("latitude").value = e.latlng.lat;
    document.getElementById("longitude").value = e.latlng.lng;
}

function load_entries() {
	if (windows.stop_load_entries == false) {
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		var west = body_map.getBounds().getWest();
		var east = body_map.getBounds().getEast();
		var south = body_map.getBounds().getSouth();
		var north = body_map.getBounds().getNorth();
		var load_url = "entry_list.php?west=" + west + "&east=" + east + "&south=" + south + "&north=" + north + "&mobile=true";
		$( "#entry_view" ).load( load_url, function(){
				$('#loading').css('background', 'none');
				resize_entry_list();
		});
		open_window('entry_view');
	}
}

function plate_search(plate) {
	if (windows.stop_load_entries == false) {
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		var load_url = 'entry_list.php?plate=' + plate + '&mobile=true';
		windows.stop_load_entries = true; //Will be set false again by entry_list.php
		$( '#entry_view' ).load( load_url, function(){
			$('#loading').css('background', 'none');
			resize_entry_list();
		});
	}
}

function resize_entry_list(){
	column_entries = document.getElementsByClassName("column_entry");
	if (column_entries.length < 3){
		total_height = 0;
		for (i = 0; i < column_entries.length; i++) {
			if ($(column_entries[i]).hasClass("single_view_column_entry") == false) {
				total_height += column_entries[i].offsetHeight;
			}
		}
		$('#entry_view').animate({ height: total_height, bottom: '0vh' });
	}
	else { $('#entry_view').animate({ height: '33vh', bottom: '0vh' }); }
}

function zoomToEntry(lat,lng,id) {
	windows.stop_load_entries = true;
	single_view_url = "single_view.php?id=" + id;
	$(".single_view").load(single_view_url, function(){
		$('#fullsize').on('load', function(){
			open_window('single_view');
		});
	});
	body_map.panTo([lat-.002,lng]).setZoom(18);
	soloMarker = L.marker([lat,lng]).addTo(body_map);
	markers.clearLayers();
	markers.addLayer(soloMarker);
	setTimeout(function() { windows.stop_load_entries = false; }, 500);
}

function open_window(window_name) {
	if (window_name == 'submit_view'){ change_nav('submit'); }
	else if (window_name == 'about_view'){ change_nav('about'); }
	else { change_nav('close'); }

	if (windows.entry_view == true) {
		if (window_name != 'entry_view'){ $('#entry_view').animate({opacity: 'toggle', bottom: '-50vh'}); }
	}
	if (windows.single_view == true) {
		$('#single_view').animate({opacity: 'toggle', top: '100vh'});
	}
	if (windows.about_view == true) {
		$('#about_view').animate({opacity: 'toggle', top: '100vh'});
		if (window_name == 'about_view'){
			windows.about_view = false;
			open_window('entry_view');
			return;
		}
	}
	if (windows.submit_view == true) {
		$('#submit_view').animate({opacity: 'toggle', top: '100vh'});
		windows.stop_load_entries = false;
		if (window_name == 'submit_view'){
			windows.submit_view = false;
			open_window('entry_view');
			return;
		}
	}
	if (windows.results_view == true) {
		$('#results_view').animate({opacity: 'toggle', top: '100vh'});
		if (window_name == 'results_view'){ open_window('entry_view'); }
	}

	if (window_name == 'entry_view' && windows.entry_view == false){
		$('#entry_view').animate({opacity: 'toggle', bottom: '0vh'});
		windows.entry_view = true;
		windows.single_view = false; windows.about_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'single_view' && windows.single_view == false){
		var new_position = $(window).innerHeight() - $('#single_view').outerHeight();
		$('#single_view').animate({opacity: 'toggle', top: new_position});
		windows.single_view = true;
		windows.entry_view = false; windows.about_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'about_view' && windows.about_view == false){
		$('#about_view').animate({opacity: 'toggle', top: '7vh'});
		windows.about_view = true;
		windows.entry_view = false; windows.single_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'submit_view' && windows.submit_view == false){
		$('#submit_view').animate({opacity: 'toggle', top: '7vh'});
		windows.stop_load_entries = true;
		windows.submit_view = true;
		windows.entry_view = false; windows.single_view = false; windows.about_view = false; windows.results_view = false;
	}
	if (window_name == 'results_view' && windows.results_view == false){;
		$('#results_view').animate({opacity: 'toggle', top: '25vh'});
		windows.results_view = true;
		windows.entry_view = false; windows.single_view = false; windows.about_view = false; windows.submit_view = false;
	}
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

function reset_form() {
	$('#mobile_submission_form')[0].reset();
	open_window('submit_view');
	setTimeout(function(){
		$('#image_prompt').html('TAP TO ADD AN IMAGE');
		$('#submit_view').scrollTop(0);
	},200);
}

</script>

</head>

<body>

<div id='nav_container' class='nav_container'>
<div id='nav' class='nav'>

<div id='loading' class='nav_link'>
</div>

<div id='about_link_container' class='nav_link'>
<div id='about_link'><span class='navspan'><?php echo $config['site_name']; ?></span></div>
</div>

<div id='submit_link_container' class='nav_link'>
<div id='submit_link'><span class='navspan'>SUBMIT</span></div>
</div>

</div>
</div>

<div id='body_map' class='body_map'></div>

<div id='entry_view' class='entry_view'></div>

<div id='results_view' class='results_view'></div>

<div id='single_view' class='single_view'></div>

<div id='about_view' class='about_view'>
<div class="top_dialog_button" onClick="javascript:open_window('about_view')">
<span>&#x2A09</span>
</div>
<?php echo stripslashes(htmlspecialchars_decode($config['about_text'])); ?>
</div>

<div id='submit_view' class='submit_view'>
<form id="mobile_submission_form" action="submission.php" enctype="multipart/form-data">

	<label id='file_container' class='file_container'>
	<span id='image_prompt' class='v-centered'>TAP TO ADD AN IMAGE</span>
	<input type="file" name="image_submission" id="image_submission"/>
	</label>

    <span>PLATE:</span>
	<input type="text" name="plate" id="plate" class='wide' maxlength="8"/>

    <span> STATE: </span>
    <select name="state" id="state" class='wide'>
    <option value="NY">NY</option>
    <option value="NJ">NJ</option>
    <option value="NYPD">NYPD</option>
	<option value="NYPD">FDNY</option>
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
	<option value="NONE">NONE</option>
    </select>

    <span>DATE:</span>
	<input type="text" name="date" class='wide' id="datetimepicker"/>

	<span>STREET 1 (OPTIONAL):</span>
	<input type="text" name="street1" id="street1" class='wide'/>

	<span>STREET 2 (OPTIONAL):</span>
	<input type="text" name="street2" id="street2" class='wide'/>

    <span id="map_prompt" class='medium'>Tap to mark location if not detected:</span><br>
	<div id="submit_map"></div>
	<span id="gps_coords" class='medium'>Latitude: ... Longitude: ...</span>
	<br>
	<br>
	<br>

	<span id='description-title'>DESCRIPTION (Optional)</span><br>
	<span  class='medium'><div id="character_limit">200</div> characters</span><br>
	<textarea name="description" onKeyDown="limitText();" onKeyUp="limitText();" class="comments" id="comments"></textarea><br>

	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">

	<input type="submit" class="submit_button" value="UPLOAD!" name="upload"/>

</form>
</div>

</body>

</html>
