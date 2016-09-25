<?php require 'auth.php'; ?>

<html>
<head>

<!--local stylesheets -->
<link rel="stylesheet" type="text/css" href="../css/style.css" />
<link rel="stylesheet" type="text/css" href="../css/plates.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- leaflet -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css" />

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="../scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="../css/jquery.datetimepicker.css"/ >

<!-- google fonts -->
<link href='https://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'>

<!-- license plate font by Dave Hansen -->
<link href='../css/license-plate-font.css' rel='stylesheet' type='text/css'>

<!-- leaflet-providers by leaflet-extras (https://github.com/leaflet-extras) -->
<script src="../scripts/leaflet-providers.js"></script>

<!-- Google Javascript API with current key -->
<script id="google_api_link" src="<?php echo 'https://maps.google.com/maps/api/js?key=' . $config['google_api_key']; ?>"></script>

<!-- leaflet-plugins by Pavel Shramov (https://github.com/shramov/leaflet-plugins) -->
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Google.js"></script>
<script id="leaflet_plugins" src="../scripts/leaflet-plugins-master/layer/tile/Bing.js"></script>

</head>
<body class='non_map'>

<?php
require('config_pointer.php');

$success_string = '';
$error_string = '';

if (isset($_POST['save'])) {
	try {
		//MOVE SUBMISSION TO MAIN TABLE, DELETE QUEUE SUBMISSION, UPDATE IMAGE NAMES AND URLS
		$connection->begin_transaction();
		$connection->query(
		'UPDATE cibl_queue ' .
		'SET url="' . $_POST['url'] . '", ' .
		'plate="' . $_POST['plate'] . '", ' .
		'state="' . $_POST['state'] . '", ' .
		'date_occurrence="' . date('Y-m-d H:i:s', strtotime($_POST['date'])) . '", ' .
		'gps_lat=' . $_POST['lat'] . ', ' .
		'gps_long=' . $_POST['lon'] . ', ' .
		'street1="' . mysqli_real_escape_string($connection, $_POST['street1']) . '", ' .
		'street2="' . mysqli_real_escape_string($connection, $_POST['street2']) . '", ' .
		'description="' . mysqli_real_escape_string($connection, $_POST['comment']) . '" ' .
		'WHERE increment=' . $_POST['id']);
		$connection->query('INSERT INTO cibl_data
							SELECT * FROM cibl_queue
							WHERE increment = ' . $_POST['id']);
		$id = $connection->insert_id;
		$old_url = mysqli_fetch_array($connection->query(
							'SELECT url
							FROM cibl_queue
							WHERE increment = ' . $_POST['id']))[0];
		$new_url = pathinfo($old_url)['dirname'] . '/' . $id . '.' . pathinfo($old_url)['extension'];
		$connection->query('UPDATE cibl_data SET url=\'' . $new_url . '\' WHERE increment=' . $id);
		$connection->query('DELETE FROM cibl_queue
							WHERE increment = ' . $_POST['id']);
		$connection->commit();
		rename('../thumbs/' . $old_url, '../thumbs/' . $new_url);
		rename('../images/' . $old_url, '../images/' . $new_url);

		if ($_POST['rotate'] != 0){
			$image_large = imagecreatefromjpeg( '../images/' . $new_url );
			$rotated_image_large = imagerotate( $image_large , -$_POST['rotate'], 0 );
			$file1 = imagejpeg($rotated_image_large, '../images/' . $new_url);
			$rotated_image_small = resize_image('../images/' . $new_url, 200, 200);
			$file2 = imagejpeg($rotated_image_small, '../thumbs/' . $new_url);
		}
		$success_string = "Submission #" . $_POST['id'] . " has been saved to the map.";
	}
	catch (Exception $e) {
		$connection->rollback();
		$error_string = "Problem adding submission #" . $_POST['id'] . " to the map: " . $e;
	}
}

if (isset($_POST['delete'])){
	$url = mysqli_fetch_array($connection->query('SELECT url FROM cibl_queue WHERE increment = ' . $_POST['id']))[0];
	if ($url){
		$result = $connection->query('DELETE FROM cibl_queue WHERE increment=' . $_POST['id']);
		$file_thumb = "../thumbs/" . $url;
		$file_image = "../images/" . $url;
		unlink($file_thumb);
		unlink($file_image);
		$success_string = "Submission #" . $_POST['id'] . " deleted.";
	}
	else {
		$error_string = "Problem adding submission #" . $_POST['id'] . " to the map: " . $e;
	}
}

$per_page = $config['max_view'];
if (isset($_GET['per_page'])){ $per_page = $_GET['per_page']; }
$go_to_entry = 1;
if (isset($_GET['go_to_entry'])){ $go_to_entry = $_GET['go_to_entry']; }
if ($go_to_entry < 1){ $go_to_entry = 1; }

$total_query = 'SELECT COUNT(*) FROM cibl_queue';
$total_entries = mysqli_fetch_array(mysqli_query($connection, $total_query))[0];

$result = $connection->query(
	'SELECT *
	FROM cibl_queue
	WHERE increment >= ' . $go_to_entry . '
	ORDER BY date_added ASC
	LIMIT ' . $per_page . '
	OFFSET 0');

echo "\n <div class='flex_container_scroll'>";
echo "\n <div class='moderation_queue' id='moderation_queue'>";

include 'nav.php';

if ($success_string){
	echo "\n\n <div class='flex_container_nav' style='background-color:green !important'>";
	echo "\n <div>";
	echo "\n <h3> Success:</h3>";
	echo "\n <span>" . $success_string . "</span>";
	echo "\n </div>";
	echo "\n </div>";
}

if ($error_string){
	echo "\n\n <div class='flex_container_nav' style='background-color:red !important'>";
	echo "\n <div>";
	echo "\n <h3> Error:</h3><br>";
	echo "\n <span>" . $error_string . "</span>";
	echo "\n </div>";
	echo "\n </div>";
}

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

<div class="flex_container_nav list_nav">
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
$count = 0;
while ($count < count($entries)){

	//BEGIN MOD QUEUE ROW
	echo "\n\n <div class='moderation_queue_row' id='moderation_queue_row" . $entries[$count][0] . "'>";

	//---SECTION 1: BUTTONS---
	echo "\n <div class='moderation_queue_buttons'>";
	echo "\n <button id='save" . $entries[$count][0] . "' class='bold_button' onclick='javascript:accept(" . $entries[$count][0] . ");'>ACCEPT</button> <br>";
	echo "\n <div class='delete_div'>";
	echo "\n <span><label><input id='checkbox" . $entries[$count][0] . "' type='checkbox' style='height:20px'onClick='javascript:armForDelete(" . $entries[$count][0] . ");'>DELETE:</label></span>";
	echo "\n <button id='delete" . $entries[$count][0] . "' class='bold_button disabled'  style='margin-top:0px' onClick='javascript:remove(" . $entries[$count][0] . ")'>DELETE</button><br>";
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
	echo "<input id='street1_" . $entries[$count][0] . "' name='street1_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][8], ENT_QUOTES) . "'/>";
	echo "<input id='street2_" . $entries[$count][0] . "' name='street2_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][9], ENT_QUOTES) . "'/>";
	echo "<input id='comment_" . $entries[$count][0] . "' name='comment_" . $entries[$count][0] . "' type='hidden' value='" . htmlentities($entries[$count][10], ENT_QUOTES) . "'/>";
	//END MOD QUEUE ROW
	$count++;
}
?>

<div class="flex_container_nav list_nav">
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
	echo "\n <h2>No new submissions.</h2>";
	echo "\n </div>";
}

echo "\n\n</div>";
echo "</div>";

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

?>

</body>

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
			if(e.target.id != 'textarea_comment' + id && e.target.id != 'comment' + id){
				document.removeEventListener('click', commentListener, true);
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

		$("#date" + backupEntry.id).html("<span>" + backupEntry.date + "</span>");

		var oldStreets = "<span>" + backupEntry.street1;
		if (backupEntry.street2 != 0 && backupEntry.street2 != ""){ oldStreets+= " & " + backupEntry.street2; }
		oldStreets += "</span>";
		$("#streets" + backupEntry.id).html(oldStreets);

		var old_gps_text = "<span>" + backupEntry.lat + " / " + backupEntry.lon + "</span>";
		$("#gps" + backupEntry.id).html(old_gps_text);

		$("#comment" + backupEntry.id).html("<span>" + backupEntry.comment + "</span>");
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
		$("#" + id).empty();
		$("#" + id).html(newHtml);
		$('#img' + id).on('load', function() {
			update_img_container_size(id);
		});
		zoomToggles.set(id, false);
	}
	else {
		var newHtml = "<img class='review' id='img" + id + "' src=\"../images/" + link +
		"\" onclick=\"javascript:toggleImg('" + link + "'," + id + ")\" style='transform:rotate(" + rotations.get(id) + "deg)' />";
		$("#" + id).empty();
		$("#" + id).html(newHtml);
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
	{ $('#' + id).width(imgWidth); $('#' + id).height(imgHeight); }
	else
	{ $('#' + id).width(imgHeight); $('#' + id).height(imgWidth); }
}

function accept(id){
	new_current_entry(id);
	var form = $(
	'<form action="index.php" method="post" style="display:none">' +
	'<input type="hidden" name="save" value="true" />' +
	'<input type="hidden" name="id" value="' + currentEntry.id + '" />' +
	'<input type="hidden" name="url" value="' + currentEntry.url + '" />' +
	'<input type="hidden" name="plate" value="' + currentEntry.plate + '" />' +
	'<input type="hidden" name="state" value="' + currentEntry.state + '" />' +
	'<input type="hidden" name="date" value="' + currentEntry.date + '" />' +
	'<input type="hidden" name="street1" value="' + htmlEntities(currentEntry.street1) + '" />' +
	'<input type="hidden" name="street2" value="' + htmlEntities(currentEntry.street2) + '" />' +
	'<input type="hidden" name="lat" value="' + currentEntry.lat + '" />' +
	'<input type="hidden" name="lon" value="' + currentEntry.lon + '" />' +
	'<input type="hidden" name="comment" value="' + htmlEntities(currentEntry.comment) + '" />' +
	'<input type="hidden" name="rotate" value="' + rotations.get(currentEntry.id * 1) + '" />' +
	'</form>');
	$('body').append(form);
	form.submit();
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function remove(id){
	var form = $(
	'<form action="index.php" method="post" style="display:none">' +
	'<input type="hidden" name="delete" value="true" />' +
	'<input type="hidden" name="id" value="' + currentEntry.id + '" />' +
	'</form>');
	$('body').append(form);
	form.submit();
}

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

$(document).ready( function() {
	$(".disabled").prop('disabled', true);
	if (total_entries == 0){
		$('.list_nav').hide();
	}
});

</script>
</html>
