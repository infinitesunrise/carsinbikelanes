<?php require 'auth.php'; ?>

<!-- Non-admins have no reason to be on this page. -->
<?php
if (isset($_SESSION['admin'])){
	if ($_SESSION['admin'] == false){
		header('Location: index.php');
	}
}
?>

<html>
<head>

<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="../css/style.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- leaflet -->
<script src="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.css" />

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="../scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css"/ >

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'> 

<!-- license plate font by Dave Hansen -->
<link href='../css/license-plate-font.css' rel='stylesheet' type='text/css'>

<script type="text/javascript">

class Entry {
	constructor(id, url, plate, state, date, lat, lon, street1, street2, comment) {
 		this.id = id;
		this.url = url;
 		this.plate = plate;
		this.state = state;
		this.date = date;
 		this.lat = lat;
 		this.lon = lon;
 		this.street1 = street1;
 		this.street2 = street2;
 		this.comment = comment;
 	}
}

var zoomToggles = new Map();
var rotations = new Map();
var entries = new Map();

function toggleImg(link,id) {
	if (zoomToggles.has(id) && (zoomToggles.get(id))){
		var newImg = "../thumbs/" + link;
		var newHtml = "<img class='review' id='img" + id + "' src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
		$("#" + id + "").empty();
		$("#" + id + "").html(newHtml);
		rotate(0,id);
		zoomToggles.set(id, false);
	}
	else {
		var newImg = "../images/" + link;
		var newHtml = "<img class='review' id='img" + id + "' src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
		$("#" + id + "").empty();
		$("#" + id + "").html(newHtml);
		rotate(0,id);
		zoomToggles.set(id, true);
	}
}

function rotate(angle, imgNumber){
	var rotation = 0;
	if (rotations.has(imgNumber)){ rotation = rotations.get(imgNumber); }
	rotation += angle;
	if (rotation > 360) { rotation -= 360; }
	if (rotation < 0) { rotation += 360; }
	rotations.set(imgNumber,rotation);
	document.getElementById("img" + imgNumber).style.transform = "rotate(" + rotations.get(imgNumber) + "deg)";
	setTimeout( function(){
		var bounds = document.getElementById("img" + imgNumber).getBoundingClientRect();
		document.getElementById(imgNumber).style.width = bounds.width;
		document.getElementById(imgNumber).style.height = bounds.height;
	}, 10);
}

$(document).ready( function() {
	$(".disabled").prop('disabled', true);
});

</script>
</head>
<body id='body' class='non_map'>

<?php

require('config_pointer.php');

$per_page = $config['max_view'];
if (isset($_GET['per_page'])){ $per_page = $_GET['per_page']; }
$go_to_entry = 1;
if (isset($_GET['go_to_entry'])){ $go_to_entry = $_GET['go_to_entry']; }

$total_query = 'SELECT COUNT(*) FROM cibl_data';
$total_entries = mysqli_fetch_array(mysqli_query($connection, $total_query))[0];

$result = $connection->query(
	'SELECT *
	FROM cibl_data
	WHERE increment >= ' . $go_to_entry . '
	ORDER BY date_added ASC
	LIMIT ' . $per_page . '
	OFFSET 0');

echo "\n <div class='flex_container_scroll'>";
echo "\n <div class='moderation_queue' id='moderation_queue'>";
include 'nav.php';

$entries = array();
while ($row = mysqli_fetch_array($result)){
	$entries[] = $row;
}

?>

<form action='edit.php' method='GET'>
<div class="flex_container_nav">
<button class='bold_button_square' onclick='javascript:beginning();'>&#10094&#10094</button>
<button class='bold_button_square' onclick='javascript:back();'>&#10094</button>
<div class="nav_option">
<span>Entries per page:</span>
<input type="text" class="nav" name="per_page" value="<?php echo $per_page; ?>"/>
</div>
<div class="nav_option">
<span>Go to entry:</span>
<input type="text" class="nav" name="go_to_entry" value="<?php echo $go_to_entry; ?>"/>
</div>
<div class="nav_option">
<span><?php echo 'Displaying ' . $entries[0][0] . ' - ' . $entries[count($entries)-1][0] . ' out of ' . $total_entries; ?></span>
</div>
<button class='bold_button_square' onclick='javascript:forward();'>&#10095</button>
<button class='bold_button_square' onclick='javascript:end();'>&#10095&#10095</button>
<input type='submit' name='nav_submit' style='display:none'/>
</div>
</form>

<?php

$count = 0;
while ($count < count($entries)){
	
	//BEGIN MOD QUEUE ROW
	echo "\n\n <div class='moderation_queue_row'>";
	
	//---SECTION 1: BUTTONS---
	echo "\n <div class='moderation_queue_buttons'>";
	echo "\n <button id='save" . $entries[$count][0] . "' class='bold_button disabled' onclick='javascript:accept(" . $entries[$count][0] . ");'>UPDATE ENTRY</button> <br>";
	echo "\n <div class='delete_div'>";
	echo "\n <span><label><input type='checkbox' style='height:20px'onClick='javascript:armForDelete(" . $entries[$count][0] . ");'>DELETE:</label></span>";
	echo "\n <button id='delete" . $entries[$count][0] . "' class='bold_button disabled'  style='margin-top:0px' onClick='javascript:window.location = edit.php?delete=" . $entries[$count][0] . "'>DELETE</button><br>";
	echo "\n </div>";
	echo "\n <div style='width:100%; display:flex;'>";
	echo "<button class='rotate' onClick='rotate(-90," . $entries[$count][0] . ")'>&#10553</button>";
	echo "<div style='width:10px'></div>";
	echo "<button class='rotate' onClick='rotate(90," . $entries[$count][0] . ")'>&#10552</button>";
	echo "</div>";
	echo "\n </div>";
	
	//---SECTION 2: IMAGE---
	echo "\n <div id='" . $entries[$count][0] . "' class='mod_queue_img_container'>";
	echo "\n <img id='img" . $entries[$count][0] . "' class='review' src='../thumbs/" . $entries[$count][1] . "' onclick=\"javascript:toggleImg('" . $entries[$count][1] . "', " . $entries[$count][0] . ");\"/>";
	echo "\n </div>";
	
	//---SECTION 3: DETAILS---
	echo "\n <div class='moderation_queue_details'>";

		//---SECTION 3.TOP: PLATE AND DETAILS---
	echo "\n <div class='details_top'>";
	
			//---SECTION 3.TOP.LEFT: PLATE---
	echo "\n <div class='details_plate'>";	
	echo "\n <div class='plate_name'><div><br/><h2>#" . $entries[$count][0] . ":</h2></div>";
	echo "\n <div class='edit edit_plate' id='plate" . $entries[$count][0] . "' onclick='javascript:edit_plate(" . $entries[$count][0] . ")'>";
	
	if ($entries[$count][3] == "NYPD"){
		$plate_split = str_split($entries[$count][2], 4);
		echo "\n <div class='plate NYPD'>" . $plate_split[0] . "<span class='NYPDsuffix'>" . $plate_split[1] . "</span></div></div>";
	}
	else {
		echo "\n <div class='plate ". $entries[$count][3] . "'>" . $entries[$count][2] . "</div></div>";
	}

	echo "\n </div>";
	echo "\n </div>";

			//---SECTION 3.TOP.RIGHT: TIME AND PLACE---
	echo "<div class='details_timeplace'>";
	$datetime = new DateTime($entries[$count][4]);
	$datetime = strtoupper($datetime->format('m/d/Y g:ia'));
	
	echo "\n<span>TIME: </span>";
	echo "<div class='edit edit_date' id='date" . $entries[$count][0] . "' onclick='javascript:edit_date(" . $entries[$count][0] . ")'>";
	echo "<span>" . $datetime . "</span>";
	echo "</div><br/>";
	
	echo "\n<span>STREETS: </span>";
	echo "<div id='streets" . $entries[$count][0] . "' class='edit edit_streets main_font' onclick='javascript:edit_streets(" . $entries[$count][0] . ")'>";
	echo "<span>" . strtoupper($entries[$count][8]);
	if ($entries[$count][9] !== ''){
		echo " & " . strtoupper($entries[$count][9]);
	}
	echo "</span></div><br/>";
	
	echo "\n<span>GPS: </span>";
	echo "<div id='gps" . $entries[$count][0] . "' class='edit edit_gps' onclick='javascript:edit_gps(" . $entries[$count][0] . ")'><span>";
	echo $entries[$count][6] . " / " . $entries[$count][7];
	echo "</span></div>";
	echo "\n<div style='position:relative'><div id='gps_map_container_" . $entries[$count][0] . "' class='gps_map_container'><div id='gps_map" . $entries[$count][0] . "' class='gps_map'></div></div></div>";
	
	echo "\n</div>";
	echo "\n</div>";

		//---SECTION 3.BOTTOM: COMMENT---
	echo "\n <div class='details_bottom'>";
	echo "\n <span style='margin-left:7px'>COMMENT:</span>";
	echo "\n <div class='edit edit_comment' id='comment" . $entries[$count][0] . "' onclick='javascript:edit_comment(" . $entries[$count][0] . ")'><span>" . nl2br($entries[$count][10]) . "</span></div>";	
	echo "\n </div>";
	
	echo "\n </div>";
	echo "\n </div>";
	
	//ROW VALUES
	echo "<input id='id_" . $entries[$count][0] . "' name='id_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][0] . "'/>";
	echo "<input id='url_" . $entries[$count][0] . "' name='url_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][1] . "'/>";
	echo "<input id='plate_" . $entries[$count][0] . "' name='plate_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][2] . "'/>";
	echo "<input id='state_" . $entries[$count][0] . "' name='state_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][3] . "'/>";
	echo "<input id='date_" . $entries[$count][0] . "' name='date_" . $entries[$count][0] . "' type='hidden' value='" . $datetime . "'/>";
	echo "<input id='lat_" . $entries[$count][0] . "' name='lat_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][6] . "'/>";
	echo "<input id='lon_" . $entries[$count][0] . "' name='lon_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][7] . "'/>";
	echo "<input id='street1_" . $entries[$count][0] . "' name='street1_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][8] . "'/>";
	echo "<input id='street2_" . $entries[$count][0] . "' name='street2_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][9] . "'/>";
	echo "<input id='comment_" . $entries[$count][0] . "' name='comment_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][10] . "'/>";
	//END MOD QUEUE ROW
	$count++;
}
?>

<div class="flex_container_nav">
<button class='bold_button_square' onclick='javascript:beginning();'>&#10094&#10094</button>
<button class='bold_button_square' onclick='javascript:back();'>&#10094</button>
<div class="nav_option">
<span>Entries per page:</span>
<input type="text" class="nav" name="per_page"/>
</div>
<div class="nav_option">
<span>Go to entry:</span>
<input type="text" class="nav" name="go_to_entry"/>
</div>
<div class="nav_option">
<span>Displaying 1 - 50</span>
</div>
<button class='bold_button_square' onclick='javascript:forward();'>&#10095</button>
<button class='bold_button_square' onclick='javascript:end();'>&#10095&#10095</button>
</div>

<?php
if ($count == 0){
	echo "\n\n <div class='moderation_queue_row'>";
	echo "\n <h2>No new submissions.</h2>";
	echo "\n </div>";
}

echo "\n\n</div>";
echo "</div>";
?>

<script type="text/javascript">
var currentEntry;
var currentID;

//IMAGE RESIZE FUNCTION	
function resize_image($file, $w, $h, $crop=FALSE) {
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w/$h > $r) {
            $newwidth = $h*$r;
            $newheight = $h;
        } else {
            $newheight = $w/$r;
            $newwidth = $w;
        }
    }
    $info = getimagesize($file);
    if ($info['mime'] == 'image/jpeg') 
		$src = imagecreatefromjpeg($file);
	elseif ($info['mime'] == 'image/gif') 
		$src = imagecreatefromgif($file);
	elseif ($info['mime'] == 'image/png') 
		$src = imagecreatefrompng($file);
    
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	
    return $dst;
}

//ARM (ENABLE) A DELETE BUTTON FOR ENTRY DELETION
function armForDelete(id){
	$("#delete" + id).prop('disabled', function(i, v) {
		if (v){ $("#delete" + id).removeClass('disabled') }
		else { $("#delete" + id).addClass('disabled'); }
		return !v;
	});
}

function edit_plate(id){
	new_current_entry(id);
	if ( currentID != "plate" + id ) {	
		currentID = "plate" + id;
		$("#plate" + id).html("<input id='input_plate" + id + "' class='plate " + currentEntry.state + "' style='width:146px'/>");
		$("#input_plate" + id).val(currentEntry.plate);
		$("#input_plate" + id).focus();
		var plateListener = function(e){
			if (e.target.className != 'plate ' + currentEntry.state){
				document.removeEventListener('click', plateListener, true);
				currentEntry.plate = $("#input_plate" + id).val();
				if (currentEntry.state == "NYPD" && currentEntry.plate.length > 4){
					var bigText = currentEntry.plate.slice(0,4);
					var smallText = currentEntry.plate.slice(4,999);
					$("#plate" + id).html("<div class='plate " + currentEntry.state + "'>" + bigText + "<span class='NYPDsuffix'>" + smallText + "</span></div></div>");
				}
				else { $("#plate" + id).html("<div class='plate " + currentEntry.state + "'>" + currentEntry.plate + "</div></div>"); }
				$("#plate_" + id).val(currentEntry.plate);
					setTimeout( function(){
						currentID = "";
					}, 250);
			}
		}
		document.addEventListener('click', plateListener, true);
	}
}

function edit_date(id){
	new_current_entry(id);
	if ( currentID != "date" + id ) {
		currentID = "date" + id;
		$("#date" + id).html("<input id='input_date" + id + "' class='main_font transparent_bg'/>");
		$("#input_date" + id).datetimepicker({value:currentEntry.date, format:'m/d/Y g:iA'});
		$("#input_date" + id).focus();
		$("#input_date" + id).focusout( function(){
			currentEntry.date = $("#input_date" + id).val();
			$("#date" + id).html("<span>" + currentEntry.date + "</span>");
			$("#date_" + id).val(currentEntry.date);
			setTimeout( function(){
				currentID = "";
			}, 250);
		});
	}
}

function edit_streets(id){
	new_current_entry(id);
	if ( currentID != "streets" + id ) {
		currentID = "streets" + id;
		$("#streets" + id).html("<input id='input_street1-" + id + "' class='main_font' style='width:100px'/> & <input id='input_street2-" + id + "' class='main_font' style='width:100px'/>");
		$("#input_street1-" + id).val(currentEntry.street1);
		$("#input_street2-" + id).val(currentEntry.street2);
		$("#input_street1-" + id).focus();
		
		var streetsListener = function(e){
			//var focus = document.activeElement.id;
			if(e.target.id != 'input_street1-' + id && e.target.id != 'input_street2-' + id && e.target.id != 'streets' + id ){
				document.removeEventListener('click', streetsListener, true);
				currentEntry.street1 = $("#input_street1-" + id).val().toUpperCase();
				currentEntry.street2 = $("#input_street2-" + id).val().toUpperCase();
				var newContents = "<span>" + currentEntry.street1;
				if (currentEntry.street2 != 0 && currentEntry.street2 != ""){ newContents+= " & " + currentEntry.street2; }
				newContents += "</span>";
				$("#streets" + id).html(newContents);
				$("#street1_" + id).val(currentEntry.street1);
				$("#street2_" + id).val(currentEntry.street2);
				setTimeout( function(){
					currentID = "";
				}, 250);
			}
		}
		
		document.addEventListener('click', streetsListener, true);
	}
}

function edit_gps(id){
	new_current_entry(id);
	if ( currentID != "gps" + id ) {
		currentID = "gps" + id;
		$("#gps_map_container_" + id).show();
		initializeMaps(id);
		var gpsListener = function(e){
			if(e.target.className != 'leaflet-tile leaflet-tile-loaded'){
				document.removeEventListener('click', gpsListener, true);
				gps_map.remove();
				$("#gps_map_container_" + id).hide();
				setTimeout( function(){
					currentID = "";
				}, 250);
			}
		}
		document.addEventListener('click', gpsListener, true);
	}
}

function edit_comment(id){
	new_current_entry(id);
	if ( currentID != "comment" + id ) {
		currentID = "comment" + id;
		$("#comment" + id).html("<textarea id='textarea_comment" + id + "' class='main_font transparent_bg' style='width:100%' value=''></textarea>");
		$("#textarea_comment" + id).val(currentEntry.comment);
		$("#textarea_comment" + id).focus();
		var commentListener = function(e){
			//console.log(currentEntry.comment);
			document.removeEventListener('click', commentListener, true);
			if(e.target.id != 'textarea_comment' + id && e.target.id != 'comment' + id){
				currentEntry.comment = $("#textarea_comment" + id).val();
				$("#comment" + id).html("<span>" + currentEntry.comment + "</span>");
				$("#comment_" + id).val(currentEntry.comment);
				setTimeout( function(){
					currentID = "";
				}, 250);
			}
		};
		document.addEventListener('click', commentListener, true);
	}
}

function new_current_entry(id){
	if (currentEntry == null){
		currentEntry = new Entry(0,0,0,0,0,0,0,0,0,0);
	}
	if (currentEntry.id != 0 && currentEntry.id != id ){
		$("#save" + currentEntry.id).prop('disabled', true);
		$("#save" + currentEntry.id).addClass("disabled");
		/*TODO: ADD CODE TO RESET ALL VIEW VALUES FOR OLD ENTRY*/
	}
	if (currentEntry.id != id){
		currentEntry = new Entry(
			$("#id_" + id).val(),
			$("#url_" + id).val(),
			$("#plate_" + id).val(),
			$("#state_" + id).val(),
			$("#date_" + id).val(),
			$("#lat_" + id).val(),
			$("#lon_" + id).val(),
			$("#street1_" + id).val(),
			$("#street2_" + id).val(),
			$("#comment_" + id).val()
		);
		backupEntry = currentEntry;
		$("#save" + id).prop('disabled', false);
		$("#save" + id).removeClass("disabled");
		return true;
	}
	else { return false; }
}

function initializeMaps(id) {
	var divName = "gps_map" + id;
	if (<?php echo $config['use_providers_plugin']; ?>) {		
		gps_map = L.map(divName);
		try { var tiles = L.tileLayer.provider('<?php echo $config['leaflet_provider']; ?>'); }
		catch (err) { console.log(err); }
	}
	else if (<?php echo $config['use_google']; ?>) {
		gps_map = L.map(divName);
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
		gps_map = L.map(divName);
		imagerySet = '<?php echo $config['bing_imagery']; ?>';
		bingApiKey = '<?php echo $config['bing_api_key']; ?>';
		try { var tiles = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
	}
	else {
		gps_map = L.map(divName);
		try { var tiles = L.tileLayer('<?php echo $config['map_url']; ?>'); }
		catch (err) { console.log(err); }
	}
	
	gps_map.addLayer(tiles);
	gps_map.setView([currentEntry.lat, currentEntry.lon], 12);
	
	markers = L.layerGroup().addTo(gps_map);
	marker = new L.marker([currentEntry.lat, currentEntry.lon]).addTo(markers);
	
	gps_map.on('click', function(e){
		gps_map.removeLayer(markers);
		markers = L.layerGroup().addTo(gps_map);
		marker = new L.marker(e.latlng).addTo(markers);
		var gps_text = "<span>" + e.latlng.lat.toFixed(6) + " / " + e.latlng.lng.toFixed(6) + "</span>";
		$("#gps" + id).html(gps_text);
		currentEntry.lat = e.latlng.lat.toFixed(6)
		currentEntry.lon = e.latlng.lng.toFixed(6)
		$("#lat_" + id).val(e.latlng.lat.toFixed(6));
		$("#lon_" + id).val(e.latlng.lng.toFixed(6));
	});
}

//$(document).ready( function(){
//	setInterval( function(){ console.log(document.activeElement); }, 1000);
//});

</script>

</body>
</html>