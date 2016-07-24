<html>
<head>

<!-- main mobile stylesheet -->
<link rel="stylesheet" type="text/css" href="css/mobile_style.css" />

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
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />

<!-- mapbox -->
<script src='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.js'></script>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.css' rel='stylesheet' />

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>

<script type="text/javascript">
marker = new L.marker();

$(document).ready(function() {

	//Mapbox API access token
	L.mapbox.accessToken = 'pk.eyJ1IjoiaW5maW5pdGVzdW5yaXNlIiwiYSI6ImpkMjJZNDgifQ.XewtOwr2t6wlzCrwKDDArw';
	
	//Available Mapbox layers
	var mapboxTiles = L.tileLayer('https://{s}.tiles.mapbox.com/v3/infinitesunrise.k636mj38/{z}/{x}/{y}.png');
	var bikelanes = L.tileLayer('https://{s}.tiles.mapbox.com/v3/infinitesunrise.779ddd76/{z}/{x}/{y}.png');
	
	submit_map = L.map('submit_map')
		.addLayer(mapboxTiles)
		.addLayer(bikelanes)
		.setView([40.711, -73.982], 14);
	submit_map.on('click', onMapClick);
	
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
	
	$("#full_site_button").click( function() {
		window.location = 'index.php?forcedesktop=true';
	});
	
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
		$.ajax({
		  url: '/mobile_submission.php',
		  type: 'POST',
		  data: formData,
		  processData: false,
		  contentType: false,
		  mimeType: 'multipart/form-data',
		  success: function (a) {
			$('#form_results').html(a);
			$('#form_results').show();
			$('#submit_map').hide();
		  },
		  error: function(a) {
			alert( "something went wrong: " + a);
		  }
		});
	});
	
});

function onMapClick(e) {
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

<div id="top_banner">
<div id="test_div"></div>
<div id="title"><h2>//CARSINBIKELANES.NYC</h2></div><br>
<div id="top_text">MOBILE SUBMISSION FORM <span id="full_site_button">VIEW FULL SITE INSTEAD</span></div>
</div>

<br>
<br>

<form id="mobile_submission_form" action="submission.php" enctype="multipart/form-data">

	<div id="form_row">
    <div id="spacer"><span>Attach your image:</span></div> <input type="file" name="image_submission" id="image_submission">
    </div>
    
    <div id="form_row">
    <div id="spacer"><span>License plate:</span></div> <input type="text" name="plate" id="plate" maxlength="7">
    </div>
    
    <div id="form_row">
    <div id="spacer"><span> State: </span></div>
    <select name="state" id="state">
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
    </select>
    </div>

    <div id="form_row">
    <div id="spacer"><span> When:</span></div> <input type="text" name="date" id="datetimepicker">
    </div>
    
    <div id="form_row">
	<div id="spacer"><span>Cross streets (optional):</span></div> <input type="text" name="street1" id="street1"> <br>
	<div id="spacer"><span>&amp</span></div> <input type="text" name="street2" id="street2"><br>
    
    <div id="form_row">
    <span id="map_prompt">Tap to mark location if not detected:</span><br>
	<div id="submit_map"></div>
	<span id="gps_coords">Latitude: ... Longitude: ...</span>
	</div>
	<br>
	<div id="form_row">
	<span>Any additional info (
	<div id="character_limit">200</div> characters):</span><br>
	<textarea name="description" onKeyDown="limitText();" onKeyUp="limitText();" class="comments" id="comments"></textarea><br>
	</div>
	<br>
	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">
	
	<div id="form_row">
	<input type="submit" class="submit_button" value="SUBMIT" name="submit"><br>
	</div>
	
</form>

<div id="form_results" />

</body>

</html>