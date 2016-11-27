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

<!--local stylesheets -->
<link rel="stylesheet" type="text/css" href="../css/style.css" />
<link rel="stylesheet" type="text/css" href="../css/plates.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- leaflet -->
<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css" />

<!-- mapbox -->
<script src='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.js'></script>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.css' rel='stylesheet' />
<script src='https://api.mapbox.com/mapbox-gl-js/v0.26.0/mapbox-gl.js'></script>
<link href='https://api.mapbox.com/mapbox-gl-js/v0.26.0/mapbox-gl.css' rel='stylesheet' />

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="../scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css"/ >

<!-- google fonts -->
<link href='//fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='//fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'>

<!-- license plate font by Dave Hansen -->
<link href='../css/license-plate-font.css' rel='stylesheet' type='text/css'>

<!-- leaflet-providers by leaflet-extras (https://github.com/leaflet-extras) -->
<script src="../scripts/leaflet-providers.js"></script>

<!-- Google Javascript API with current key -->
<script id="google_api_link" src="<?php echo '//maps.google.com/maps/api/js?key=' . $config['google_api_key']; ?>"></script>

<!-- leaflet-plugins by Pavel Shramov (https://github.com/shramov/leaflet-plugins) -->
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Google.js"></script>
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Bing.js"></script>

</head>
<body id='body' class='non_map'>

<?php

require('config_pointer.php');

if (isset($_POST['save'])){
	if ($_POST['rotate'] != 0){
		/*$image_large = imagecreatefromjpeg( '../images/' . $_POST['url'] );
		$rotated_image_large = imagerotate( $image_large , -$_POST['rotate'], 0 );
		imagedestroy($image_large);
		$file1 = imagejpeg($rotated_image_large, '../images/' . $_POST['url']);
		imagedestroy($rotated_image_large);
		$rotated_image_small = resize_image('../images/' . $_POST['url'], 200, 200);
		$file2 = imagejpeg($rotated_image_small, '../thumbs/' . $_POST['url']);*/
		
		$imagick = new Imagick($_SERVER['DOCUMENT_ROOT'] . '/images/' . $_POST['url']);
		$imagick->rotateImage('black', $_POST['rotate']);
		$imagick->writeImage($_SERVER['DOCUMENT_ROOT'] . '/images/' . $_POST['url']);
		$imagick->scaleImage(200, 200, true);
		$imagick->writeImage($_SERVER['DOCUMENT_ROOT'] . '/thumbs/' . $_POST['url']);
	}
	$result = $connection->query(
	'UPDATE cibl_data ' .
	'SET url="' . $_POST['url'] . '", ' .
	'plate="' . $_POST['plate'] . '", ' .
	'state="' . $_POST['state'] . '", ' .
	'date_occurrence="' . date('Y-m-d H:i:s', strtotime($_POST['date'])) . '", ' .
	'gps_lat=' . $_POST['lat'] . ', ' .
	'gps_long=' . $_POST['lon'] . ', ' .
	'street1="' . mysqli_real_escape_string($connection, $_POST['street1']) . '", ' .
	'street2="' . mysqli_real_escape_string($connection, $_POST['street2']) . '", ' .
	'council_district=' . $_POST['council_district'] . ', ' .
	'precinct=' . $_POST['precinct'] . ', ' .
	'community_board="' . $_POST['community_board'] . '", ' .
	'description="' . mysqli_real_escape_string($connection, $_POST['comment']) . '" ' .
	'WHERE increment=' . $_POST['id']);
	$save_success = $_POST['id'];
}

if (isset($_POST['delete'])){
	$url = mysqli_fetch_array($connection->query('SELECT url FROM cibl_data WHERE increment = ' . $_POST['id']))[0];
	$result = $connection->query('DELETE FROM cibl_data WHERE increment=' . $_POST['id']);
	$file_thumb = "../thumbs/" . $url;
	$file_image = "../images/" . $url;
	unlink($file_thumb);
	unlink($file_image);
}

$per_page = $config['max_view'];
if (isset($_GET['per_page'])){ $per_page = $_GET['per_page']; }
$go_to_entry = 1;
if (isset($_GET['go_to_entry'])){ $go_to_entry = $_GET['go_to_entry']; }
if ($go_to_entry < 1){ $go_to_entry = 1; }

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
if ($total_entries > 0){ $first_entry = $entries[0][0]; }
else { $first_entry = 0; }
if($total_entries > 1) { $last_entry = $entries[count($entries)-1][0]; }
else if($total_entries == 1){ $last_entry = 1; }
else if($total_entries == 0){ $last_entry = 0; }

?>

<div class="flex_container_nav list_nav box_shadow2">
<button class='bold_button_square' onclick='javascript:beginning();'>&#10094&#10094</button>
<button class='bold_button_square' onclick='javascript:back();'>&#10094</button>
<div class="nav_option">
<form action='edit.php' method='GET'>
<span>Go to entry:</span>
<input type="text" class="nav" name="go_to_entry" id='go_to_entry' value="<?php echo $go_to_entry; ?>"/>
</div>
<div class="nav_option">
<span>Entries per page:</span>
<input type="text" class="nav" name="per_page" id='per_page' value="<?php echo $per_page; ?>"/>
</div>
<div class="nav_option">
<span><?php echo 'Displaying ' . $first_entry . ' - ' . $last_entry . ' out of ' . $total_entries; ?></span>
</div>
<input type='submit' style='display:none'/>
</form>
<button class='bold_button_square' onclick='javascript:forward();'>&#10095</button>
<button class='bold_button_square' onclick='javascript:end();'>&#10095&#10095</button>
</div>

<?php

if ($save_success){
	//echo "\n\n <div class='moderation_queue_row' style='background-color: green'>";
	echo "\n<div class='settings_box box_shadow_green' style='background-color: green'>";
	echo "\n<div class='settings_group'>";
	echo "\n<h3>Success:</h3>";
	echo "\n<p>Entry # " . $save_success . " updated.</p>";
	echo "\n</div>";
	echo "\n</div>";
}

$count = 0;
while ($count < count($entries)){

	//BEGIN MOD QUEUE ROW
	echo "\n\n <div class='moderation_queue_row box_shadow2' id='moderation_queue_row" . $entries[$count][0] . "'>";

	//---SECTION 1: BUTTONS---
	echo "\n<div class='moderation_queue_buttons'>";
	echo "\n<button id='save" . $entries[$count][0] . "' class='bold_button2 disabled' onclick='javascript:save(" . $entries[$count][0] . ");'>UPDATE ENTRY</button> <br>";
	//echo "\n<div class='delete_div'>";
	//echo "\n<span><label><input id='checkbox" . $entries[$count][0] . "' type='checkbox' style='height:20px'onClick='javascript:armForDelete(" . $entries[$count][0] . ");'>DELETE:</label></span>";
	echo "\n<button id='delete" . $entries[$count][0] . "' class='bold_button2 disabled'  style='margin-top:0px' onClick='javascript:remove(" . $entries[$count][0] . ")'>DELETE</button><br>";
	//echo "\n</div>";
	echo "\n<div style='width:100%; display:flex;'>";
	echo "<button class='rotate' onClick='rotate(-90," . $entries[$count][0] . ")'>&#10553</button>";
	echo "<div style='width:10px'></div>";
	echo "<button class='rotate' onClick='rotate(90," . $entries[$count][0] . ")'>&#10552</button>";
	echo "\n</div>";
	echo "\n</div>";

	echo "\n<div class='parameters' style='margin: 5px 0px 0px 5px;'>";
	
	//echo "\n<div class='thumb_and_plate_holder'>";
	echo "\n<div class='thumb_and_plate'>";
	//echo "\n<div id='" . $entries[$count][0] . "' class='mod_queue_img_container'>";
	
	echo "\n<div class='column_entry_thumbnail' id='thumb" . $entries[$count][0] . "'>";
	echo "\n<img id='img" . $entries[$count][0] . "' class='review' src='../thumbs/" . $entries[$count][1] . "' onclick=\"javascript:toggleImg('" . $entries[$count][1] . "', " . $entries[$count][0] . ");\"/>";
	echo "\n</div>";
	
	echo "\n<h2 id='id_text' class='id_text'>#" . $entries[$count][0] . ":</h2>";
	echo "\n <div class='edit plate_container' id='plate" . $entries[$count][0] . "' onclick='javascript:edit_plate(" . $entries[$count][0] . ")'>";
	if ($entries[$count][3] == "NYPD"){
		$plate_split = str_split($entries[$count][2], 4);
		echo "\n <div id='plate_text" . $entries[$count][0] . "' class='plate NYPD'>" . $plate_split[0] . "<span class='NYPDsuffix'>" . $plate_split[1] . "</span></div></div>";
	}
	else {
		echo "\n <div id='plate_text" . $entries[$count][0] . "' class='plate ". $entries[$count][3] . "'>" . $entries[$count][2] . "</div></div>";
	}
	echo "\n</div>";
	//echo "\n</div>";
	
	//DATE
	$datetime = new DateTime($entries[$count][4]);
	$datetime = strtoupper($datetime->format('m/d/Y g:ia'));
	echo "\n<div class='entry_parameter edit_date' id='date" . $entries[$count][0] . "' onclick='javascript:edit_date(" . $entries[$count][0] . ")'><span>DATE: " . $datetime . "</span></div>";
	
	//GPS
	echo "\n<div class='entry_parameter edit_gps' id='gps" . $entries[$count][0] . "' onclick='javascript:edit_gps(" . $entries[$count][0] . ")'><span>GPS: " . $entries[$count][6] . " / " . $entries[$count][7] . "</span></div>";
	echo "\n<div style='position:relative'><div id='gps_map_container_" . $entries[$count][0] . "' class='gps_map_container'><div id='gps_map" . $entries[$count][0] . "' class='gps_map'></div></div></div>";
	
	//STATE
	echo "\n<div class='entry_parameter state' id='state" . $entries[$count][0] . "' onclick='javascript:edit_state(" . $entries[$count][0] . ")'><span>STATE: " . $entries[$count][3] . "</span></div>";
	
	//STREETS
	$streets = strtoupper($entries[$count][8]);
	if ($entries[$count][9] !== ''){ $streets .= " & " . strtoupper($entries[$count][9]); }
	echo "\n<div class='entry_parameter edit_streets' id='streets" . $entries[$count][0] . "' onclick='javascript:edit_streets(" . $entries[$count][0] . ")'><span>STREETS: " . $streets . "</span></div>";
	
	//COUNCIL DISTRICT
	echo "\n<div class='entry_parameter council_district' id='council_district" . $entries[$count][0] . "' onclick='javascript:edit_council_district(" . $entries[$count][0] . ")'><span>COUNCIL DISTRICT: " . $entries[$count][10] . "</span></div>";
	
	//PRECINCT
	echo "\n<div class='entry_parameter precinct' id='precinct" . $entries[$count][0] . "' onclick='javascript:edit_precinct(" . $entries[$count][0] . ")'><span>PRECINCT: " . $entries[$count][11] . "</span></div>";
	
	//COMMUNITY BOARD
	echo "\n<div class='entry_parameter community_board' id='community_board" . $entries[$count][0] . "' onclick='javascript:edit_community_board(" . $entries[$count][0] . ")'><span>COMMUNITY BOARD: " . $entries[$count][12] . "</span></div>";
	
	//DESCRIPTION
	echo "\n<div class='entry_parameter description' id='comment" . $entries[$count][0] . "' onclick='javascript:edit_comment(" . $entries[$count][0] . ")'><span>DESCRIPTION: " . nl2br($entries[$count][13]) . "</span></div>";
	
	echo "\n</div>";
	
	//ROW VALUES
	echo "<input id='id_" . $entries[$count][0] . "' name='id_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][0] . "'/>";
	echo "<input id='url_" . $entries[$count][0] . "' name='url_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][1] . "'/>";
	echo "<input id='plate_" . $entries[$count][0] . "' name='plate_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][2] . "'/>";
	echo "<input id='state_" . $entries[$count][0] . "' name='state_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][3] . "'/>";
	echo "<input id='date_" . $entries[$count][0] . "' name='date_" . $entries[$count][0] . "' type='hidden' value='" . $datetime . "'/>";
	echo "<input id='lat_" . $entries[$count][0] . "' name='lat_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][6] . "'/>";
	echo "<input id='lon_" . $entries[$count][0] . "' name='lon_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][7] . "'/>";
	echo "<input id='street1_" . $entries[$count][0] . "' name='street1_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][8], ENT_QUOTES) . "'/>";
	echo "<input id='street2_" . $entries[$count][0] . "' name='street2_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][9], ENT_QUOTES) . "'/>";
	echo "<input id='council_district_" . $entries[$count][0] . "' name='council_district_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][10] . "'/>";
	echo "<input id='precinct_" . $entries[$count][0] . "' name='precinct_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][11] . "'/>";
	echo "<input id='community_board_" . $entries[$count][0] . "' name='community_board_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][12] . "'/>";
	echo "<input id='comment_" . $entries[$count][0] . "' name='comment_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][13], ENT_QUOTES) . "'/>";
	
	echo "\n</div>";
	//END MOD QUEUE ROW
	$count++;
}
?>

<div class="flex_container_nav list_nav box_shadow2">
<button class='bold_button_square' onclick='javascript:beginning();'>&#10094&#10094</button>
<button class='bold_button_square' onclick='javascript:back();'>&#10094</button>
<div class="nav_option">
<form action='edit.php' method='GET'>
<span>Go to entry:</span>
<input type="text" class="nav" name="go_to_entry" id='go_to_entry' value="<?php echo $go_to_entry; ?>"/>
</div>
<div class="nav_option">
<span>Entries per page:</span>
<input type="text" class="nav" name="per_page" id='per_page' value="<?php echo $per_page; ?>"/>
</div>
<div class="nav_option">
<span><?php echo 'Displaying ' . $first_entry . ' - ' . $last_entry . ' out of ' . $total_entries; ?></span>
</div>
<input type='submit' style='display:none'/>
</form>
<button class='bold_button_square' onclick='javascript:forward();'>&#10095</button>
<button class='bold_button_square' onclick='javascript:end();'>&#10095&#10095</button>
</div>

<?php
if ($count == 0){
	echo "\n\n <div class='moderation_queue_row'>";
	echo "\n <h2>No entries found.</h2>";
	echo "\n </div>";
}

echo "\n\n</div>";
echo "</div>";
?>

<script type="text/javascript">
var per_page = <?php echo $per_page; ?>;
var first_entry = <?php echo $first_entry; ?>;
var last_entry = <?php echo $last_entry; ?>;
var total_entries = <?php echo $total_entries; ?>;
var currentEntry;
var currentID;
var zoomToggles = new Map();
var rotations = new Map();
var entries = new Map();

function beginning(){ window.location = "edit.php?per_page=" + $('#per_page').val(); }

function back(){ window.location = "edit.php?per_page=" + $('#per_page').val() + "&go_to_entry=" + (first_entry - $('#per_page').val()); }

function forward(){ window.location.href = "edit.php?per_page=" + $('#per_page').val() + "&go_to_entry=" + (last_entry + 1); }

function end(){ window.location = "edit.php?per_page=" + $('#per_page').val() + "&go_to_entry=" + (total_entries - per_page + 1); }

//ARM (ENABLE) A DELETE BUTTON FOR ENTRY DELETION
function armForDelete(id){
	new_current_entry(id, false);
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
			$("#date" + id).html("<span>DATE: " + currentEntry.date + "</span>");
			$("#date_" + id).val(currentEntry.date);
			setTimeout( function(){
				currentID = "";
			}, 250);
		});
	}
}

function edit_state(id){
	new_current_entry(id);
	if ( currentID != "state" + id ) {
		currentID = "state" + id;
		$("#state" + id).html("<span>STATE: </span><input id='input_state" + id + "' class='main_font transparent_bg'/>");
		$("#input_state" + id).val(currentEntry.state);
		$("#input_state" + id).focus();
		$("#input_state" + id).focusout( function(){
			currentEntry.state = $("#input_state" + id).val();
			$("#state" + id).html("<span>STATE: " + currentEntry.state + "</span>");
			$("#state_" + id).val(currentEntry.state);
			$("#plate_text" + id).attr('class', 'plate ' + currentEntry.state);
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
		$("#streets" + id).html("<span>STREETS: <input id='input_street1-" + id + "' class='main_font transparent_bg' style='width:100px'/> & <input id='input_street2-" + id + "' class='main_font transparent_bg' style='width:100px'/></span>");
		$("#input_street1-" + id).val(currentEntry.street1);
		$("#input_street2-" + id).val(currentEntry.street2);
		$("#input_street1-" + id).focus();

		var streetsListener = function(e){
			if(e.target.id != 'input_street1-' + id && e.target.id != 'input_street2-' + id && e.target.id != 'streets' + id ){
				document.removeEventListener('click', streetsListener, true);
				currentEntry.street1 = $("#input_street1-" + id).val().toUpperCase();
				currentEntry.street2 = $("#input_street2-" + id).val().toUpperCase();
				var newContents = "<span>STREETS: " + currentEntry.street1;
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
			console.log(e.target.className);
			if(e.target.className != 'leaflet-tile leaflet-tile-loaded' && e.target.className != 'mapboxgl-canvas'){
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

function edit_council_district(id){
	new_current_entry(id);
	if ( currentID != "council_district" + id ) {
		currentID = "council_district" + id;
		$("#council_district" + id).html("<span>COUNCIL DISTRICT: </span><input id='input_council_district" + id + "' class='main_font transparent_bg'/>");
		$("#input_council_district" + id).val(currentEntry.council_district);
		$("#input_council_district" + id).focus();
		$("#input_council_district" + id).focusout( function(){
			currentEntry.council_district = $("#input_council_district" + id).val();
			$("#council_district" + id).html("<span>COUNCIL DISTRICT: " + currentEntry.council_district + "</span>");
			$("#council_district_" + id).val(currentEntry.council_district);
			setTimeout( function(){
				currentID = "";
			}, 250);
		});
	}
}

function edit_precinct(id){
	new_current_entry(id);
	if ( currentID != "precinct" + id ) {
		currentID = "precinct" + id;
		$("#precinct" + id).html("<span>PRECINCT: </span><input id='input_precinct" + id + "' class='main_font transparent_bg'/>");
		$("#input_precinct" + id).val(currentEntry.precinct);
		$("#input_precinct" + id).focus();
		$("#input_precinct" + id).focusout( function(){
			currentEntry.precinct = $("#input_precinct" + id).val();
			$("#precinct" + id).html("<span>PRECINCT: " + currentEntry.precinct + "</span>");
			$("#precinct_" + id).val(currentEntry.precinct);
			setTimeout( function(){
				currentID = "";
			}, 250);
		});
	}
}

function edit_community_board(id){
	new_current_entry(id);
	if ( currentID != "community_board" + id ) {
		currentID = "community_board" + id;
		$("#community_board" + id).html("<span>COMMUNITY BOARD: </span><input id='input_community_board" + id + "' class='main_font transparent_bg'/>");
		$("#input_community_board" + id).val(currentEntry.community_board);
		$("#input_community_board" + id).focus();
		$("#input_community_board" + id).focusout( function(){
			currentEntry.community_board = $("#input_community_board" + id).val();
			$("#community_board" + id).html("<span>COMMUNITY BOARD: " + currentEntry.community_board + "</span>");
			$("#community_board_" + id).val(currentEntry.community_board);
			setTimeout( function(){
				currentID = "";
			}, 250);
		});
	}
}

function edit_comment(id){
	new_current_entry(id);
	if ( currentID != "comment" + id ) {
		currentID = "comment" + id;
		$("#comment" + id).html("<span>DESCRIPTION: </span><textarea id='textarea_comment" + id + "' class='main_font transparent_bg' style='width:100%' value=''></textarea>");
		$("#textarea_comment" + id).val(currentEntry.comment);
		$("#textarea_comment" + id).focus();
		var commentListener = function(e){
			if(e.target.id != 'textarea_comment' + id && e.target.id != 'comment' + id){
				document.removeEventListener('click', commentListener, true);
				currentEntry.comment = $("#textarea_comment" + id).val();
				$("#comment" + id).html("<span>DESCRIPTION: " + currentEntry.comment + "</span>");
				$("#comment_" + id).val(currentEntry.comment);
				setTimeout( function(){
					currentID = "";
				}, 250);
			}
		};
		document.addEventListener('click', commentListener, true);
	}
}

function new_current_entry(id, enableUpdate = true){
	if (currentEntry == null){
		currentEntry = new Entry(0,0,0,0,0,0,0,0,0,0);
	}
	//Reset any unsaved edits on other entries
	if (currentEntry.id != 0 && currentEntry.id != id ){
		$('#moderation_queue_row' + currentEntry.id).css('border', 'none');
		$("#save" + currentEntry.id).prop('disabled', true);
		$("#save" + currentEntry.id).addClass("disabled");
		$("#delete" + currentEntry.id).prop('disabled', true);
		$("#delete" + currentEntry.id).addClass("disabled");
		$("#checkbox" + currentEntry.id).prop('checked', false);

		$("#id_" + currentEntry.id).val(backupEntry.id);
		$("#url_" + currentEntry.id).val(backupEntry.url);
		$("#plate_" + currentEntry.id).val(backupEntry.plate);
		$("#state_" + currentEntry.id).val(backupEntry.state);
		$("#date_" + currentEntry.id).val(backupEntry.date);
		$("#lat_" + currentEntry.id).val(backupEntry.lat);
		$("#lon_" + currentEntry.id).val(backupEntry.lon);
		$("#street1_" + currentEntry.id).val(backupEntry.street1);
		$("#street2_" + currentEntry.id).val(backupEntry.street2);
		$("#council_district_" + currentEntry.id).val(backupEntry.council_district);
		$("#precinct_" + currentEntry.id).val(backupEntry.precict);
		$("#community_board_" + currentEntry.id).val(backupEntry.community_board);
		$("#comment_" + currentEntry.id).val(backupEntry.comment);

		var newHtml = "<img class='review' id='img" + backupEntry.id + "' src=\"" + "../thumbs/" + backupEntry.url + "\" onclick=\"javascript:toggleImg('" + backupEntry.url + "'," + backupEntry.id + ");\" />";
		$("#" + backupEntry.id).empty();
		$("#" + backupEntry.id).html(newHtml);
		zoomToggles.set(backupEntry.id * 1, false);
		rotations.set((backupEntry.id * 1), 0);
		$("#img" + backupEntry.id).css('transform', 'rotate(0deg)');
		var savedID = backupEntry.id;
		setTimeout( function(){
			var bounds = document.getElementById("img" + savedID).getBoundingClientRect();
			document.getElementById(savedID).style.width = bounds.width;
			document.getElementById(savedID).style.height = (bounds.height >= 200) ? bounds.height : 200;;
		}, 10);

		if (backupEntry.state == "NYPD" && backupEntry.plate.length > 4){
			var bigText = backupEntry.plate.slice(0,4);
			var smallText = backupEntry.plate.slice(4,999);
			$("#plate" + backupEntry.id).html("<div class='plate " + backupEntry.state + "'>" + bigText + "<span class='NYPDsuffix'>" + smallText + "</span></div></div>");
		}
		else { $("#plate" + backupEntry.id).html("<div class='plate " + backupEntry.state + "'>" + backupEntry.plate + "</div></div>"); }

		$("#date" + backupEntry.id).html("<span>DATE: " + backupEntry.date + "</span>");
		
		$("#state" + backupEntry.id).html("<span>STATE: " + backupEntry.state + "</span>");

		var oldStreets = "<span>STREETS: " + backupEntry.street1;
		if (backupEntry.street2 != 0 && backupEntry.street2 != ""){ oldStreets+= " & " + backupEntry.street2; }
		oldStreets += "</span>";
		$("#streets" + backupEntry.id).html(oldStreets);

		var old_gps_text = "<span>GPS: " + backupEntry.lat + " / " + backupEntry.lon + "</span>";
		$("#gps" + backupEntry.id).html(old_gps_text);
		
		$('#council_district' + backupEntry.id).html('<span>COUNCIL DISTRICT: ' + backupEntry.council_district + '</span>');
		$('#precinct' + backupEntry.id).html('<span>PRECINCT: ' + backupEntry.precinct + '</span>');
		$('#community_board' + backupEntry.id).html('<span>COMMUNITY BOARD: ' + backupEntry.community_board + '</span>');

		$("#comment" + backupEntry.id).html("<span>DESCRIPTION: " + backupEntry.comment + "</span>");
	}
	//If method wasn't called from the delete checkbox, enable saving / updating
	if (enableUpdate == true){
		$("#save" + id).prop('disabled', false);
		$("#save" + id).removeClass("disabled");
	}
	//New entry setup
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
			$("#council_district_" + id).val(),
			$("#precinct_" + id).val(),
			$("#community_board_" + id).val(),
			$("#comment_" + id).val()
		);
		backupEntry = new Entry(
			currentEntry.id,
			currentEntry.url,
			currentEntry.plate,
			currentEntry.state,
			currentEntry.date,
			currentEntry.lat,
			currentEntry.lon,
			currentEntry.street1.toUpperCase(),
			currentEntry.street2.toUpperCase(),
			currentEntry.council_district,
			currentEntry.precinct,
			currentEntry.community_board,
			currentEntry.comment
		);
		$('#moderation_queue_row' + id).css('border', '3px dashed gray');
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
	else if (<?php echo $config['use_mapboxgljs']; ?>){
		mapboxgl.accessToken = '<?php echo $config['mapbox_key']; ?>';
		gps_map = new mapboxgl.Map({
			container: divName,
			style: '<?php echo $config['mapbox_style_url']; ?>',
			center: [currentEntry.lon, currentEntry.lat],
			zoom: 12
		});
		marker = document.createElement('div');
		marker.className = 'map_marker';
		marker.id = 'gps_marker'
		new mapboxgl.Marker(marker, {offset: [-12, -12]})
		.setLngLat([currentEntry.lon, currentEntry.lat])
		.addTo(gps_map);
		gps_map.on('click', function(e) {
			$('#gps_marker').remove();
			marker = document.createElement('div');
			marker.className = 'map_marker';
			marker.id = 'gps_marker'
			new mapboxgl.Marker(marker, {offset: [-12, -12]})
			.setLngLat(e.lngLat)
			.addTo(gps_map);
			var gps_text = "<span>" + e.lngLat.lat.toFixed(6) + " / " + e.lngLat.lng.toFixed(6) + "</span>";
			$("#gps" + id).html(gps_text);
			currentEntry.lat = e.lngLat.lat.toFixed(6)
			currentEntry.lon = e.lngLat.lng.toFixed(6)
			$("#lat_" + id).val(e.lngLat.lat.toFixed(6));
			$("#lon_" + id).val(e.lngLat.lng.toFixed(6));
		});
		return;
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

function toggleImg(link,id) {
	if (zoomToggles.has(id) && (zoomToggles.get(id))){
		var newHtml = "<img class='review' id='img" + id + "' src=\"../thumbs/" + link +
		"\" onclick=\"javascript:toggleImg('" + link + "'," + id + ")\" style='transform:rotate(" + rotations.get(id) + "deg)' />";
		$("#thumb" + id).empty();
		$("#thumb" + id).html(newHtml);
		$('#img' + id).on('load', function() {
			update_img_container_size(id);
		});
		zoomToggles.set(id, false);
	}
	else {
		var newHtml = "<img class='review' id='img" + id + "' src=\"../images/" + link +
		"\" onclick=\"javascript:toggleImg('" + link + "'," + id + ")\" style='transform:rotate(" + rotations.get(id) + "deg)' />";
		$("#thumb" + id).empty();
		$("#thumb" + id).html(newHtml);
		$('#img' + id).on('load', function() {
			update_img_container_size(id);
		});
		zoomToggles.set(id, true);
	}
}

function rotate(angle, id){
	new_current_entry(id);
	var rotation = 0;
	if (rotations.has(id)){ rotation = rotations.get(id); }
	rotation += angle;
	if (rotation >= 360) { rotation -= 360; }
	if (rotation < 0) { rotation += 360; }
	rotations.set(id,rotation);
	$("#img" + id).css("transform", "rotate(" + rotations.get(id) + "deg)");
	update_img_container_size(id);
}

function update_img_container_size(id){
	var imgWidth = $('#img' + id).width();
	var imgHeight = $('#img' + id).height();
	if (rotations.get(id) == null || rotations.get(id) == 0 || rotations.get(id) == 180)
	{ $('#thumb' + id).width(imgWidth); $('#thumb' + id).height(imgHeight); }
	else
	{ $('#thumb' + id).width(imgHeight); $('#thumb' + id).height(imgWidth); }
}

function save(id){
	var form = $(
	'<form action="edit.php" method="post" style="display:none">' +
	'<input type="hidden" name="save" value="true" />' +
	'<input type="hidden" name="id" value="' + currentEntry.id + '" />' +
	'<input type="hidden" name="url" value="' + currentEntry.url + '" />' +
	'<input type="hidden" name="plate" value="' + currentEntry.plate + '" />' +
	'<input type="hidden" name="state" value="' + currentEntry.state + '" />' +
	'<input type="hidden" name="date" value="' + currentEntry.date + '" />' +
	'<input type="hidden" name="street1" value="' + currentEntry.street1 + '" />' +
	'<input type="hidden" name="street2" value="' + currentEntry.street2 + '" />' +
	'<input type="hidden" name="lat" value="' + currentEntry.lat + '" />' +
	'<input type="hidden" name="lon" value="' + currentEntry.lon + '" />' +
	'<input type="hidden" name="council_district" value="' + currentEntry.council_district + '" />' +
	'<input type="hidden" name="precinct" value="' + currentEntry.precinct + '" />' +
	'<input type="hidden" name="community_board" value="' + currentEntry.community_board + '" />' +
	'<input type="hidden" name="comment" value="' + currentEntry.comment + '" />' +
	'<input type="hidden" name="rotate" value="' + rotations.get(currentEntry.id * 1) + '" />' +
	'</form>');
	$('body').append(form);
	form.submit();
}

function remove(id){
	if (confirm("Really delete upload #" + id + "?")){
		var form = $(
		'<form action="index.php" method="post" style="display:none">' +
		'<input type="hidden" name="delete" value="true" />' +
		'<input type="hidden" name="id" value="' + id + '" />' +
		'</form>');
		$('body').append(form);
		form.submit();
	}
}

class Entry {
	constructor(id, url, plate, state, date, lat, lon, street1, street2, council_district, precinct, community_board, comment) {
 		this.id = id;
		this.url = url;
 		this.plate = plate;
		this.state = state;
		this.date = date;
 		this.lat = lat;
 		this.lon = lon;
 		this.street1 = street1;
 		this.street2 = street2;
		this.council_district = council_district;
		this.precinct = precinct;
		this.community_board = community_board;
 		this.comment = comment;
 	}
}

$(document).ready( function() {
	$(".disabled").prop('disabled', true);
	if (total_entries == 0){
		$('.list_nav').hide();
	}
	//setInterval( function(){ console.log(document.getElementById("img2").getBoundingClientRect()); }, 1000);
});

</script>

</body>
</html>
