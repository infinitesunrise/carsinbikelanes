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
	/*var id;
	var	date;
	var	plate;
	var lat;
	var lon;
	var street1;
	var street2;
	var description;*/
	constructor(id, url, plate, state, date, lat, lon, street1, street2, comment) {
 		this.id = id;
		this.url = url;
 		this.plate = plate;
		this.state = state;
		this.date = new Date(date);
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

$(document).ready( function() {
	$(".disabled").prop('disabled', true);
});

</script>
</head>
<body class='non_map'>

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
	echo "\n <button id='save" . $entries[$count][0] . "' class='bold_button disabled' onclick='javascript:accept(" . $entries[$count][0] . ");'>SAVE CHANGES</button> <br>";
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
	
	echo "\n<span>TIME: </span>";
	echo "<div class='edit edit_date' id='date" . $entries[$count][0] . "' onclick='javascript:edit_date(" . $entries[$count][0] . ")'>";
	echo "<span>" . strtoupper($datetime->format('m/d/Y g:ia')) . "</span>";
	echo "</div><br/>";
	
	echo "\n<span>STREETS: </span>";
	echo "<div class='edit edit_streets'>";
	echo "<span>" . strtoupper($entries[$count][8]);
	if ($entries[$count][9] !== ''){
		echo " & " . strtoupper($entries[$count][9]);
	}
	echo "</span></div><br/>";
	
	echo "\n<span>GPS: </span>";
	echo "<div class='edit edit_gps'><span>";
	echo $entries[$count][5] . " / " . $entries[$count][7];
	echo "</span></div>";	
	
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
	echo "<input name='id_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][0] . "'/>";
	echo "<input name='url_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][1] . "'/>";
	echo "<input name='plate_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][2] . "'/>";
	echo "<input name='state_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][3] . "'/>";
	echo "<input name='date_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][4] . "'/>";
	echo "<input name='lat_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][6] . "'/>";
	echo "<input name='lon_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][7] . "'/>";
	echo "<input name='street1_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][8] . "'/>";
	echo "<input name='street2_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][9] . "'/>";
	echo "<input name='comment_" . $entries[$count][0] . "' type='hidden' value='" . $entries[$count][10] . "'/>";
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
	
	if ( !$("#input_plate" + id).is(":focus") ) {
		document.getElementById("plate" + id).innerHTML = "<input id='input_plate" + id + "' class='plate " + currentEntry.state + "' style='width:146px'/>";
		document.getElementById("input_plate" + id).value = currentEntry.plate;
	}
	
	if ( !$("#input_plate" + id).is(":focus") ){
		document.getElementById("input_plate" + id).focus();
		$("#input_plate" + id).focusout( function(){
			currentEntry.plate = $("#input_plate" + id).val();
			document.getElementById("plate" + id).innerHTML = "<div class='plate " + currentEntry.state + "'>" + currentEntry.plate + "</div></div>";
			document.getElementsByName("plate_" + id)[0].value = currentEntry.plate;
		});
	}
}

function edit_date(id){
	new_current_entry(id);
	
	if ( !$("#input_date" + id).is(":focus") ) {
		document.getElementById("date" + id).innerHTML = "<input id='input_date" + id + "' class='main_font no_show'/>";
		//document.getElementById("input_date" + id).value = currentEntry.date;
		var formattedDate = format_date(currentEntry.date);
		$("#input_date" + id).datetimepicker({value:formattedDate, format:'m/d/Y g:iA'});
	}
	
	if ( !$("#input_date" + id).is(":focus") ){
		document.getElementById("input_date" + id).focus();
		$("#input_date" + id).focusout( function(){
			currentEntry.date = new Date($("#input_date" + id).val());
			document.getElementById("date" + id).innerHTML = "<span>" + $("#input_date" + id).val() + "</span>";
			document.getElementsByName("date_" + id)[0].value = currentEntry.date;
		});
	}
}

function edit_comment(id){
	new_current_entry(id);
	
	if ( !$("#textarea_comment" + id).is(":focus") ) {
		document.getElementById("comment" + id).innerHTML = "<textarea id='textarea_comment" + id + "' style='width:100%; background: transparent; border: 0 none; outline: none;'></textarea>";
		document.getElementById("textarea_comment" + id).value = currentEntry.comment;
	}
	
	if ( !$("#textarea_comment" + id).is(":focus") ){
		document.getElementById("textarea_comment" + id).focus();
		$("#textarea_comment" + id).focusout( function(){
			currentEntry.comment = $("#textarea_comment" + id).val();
			document.getElementById("comment" + id).innerHTML = "<span>" + currentEntry.comment + "</span>";
			document.getElementsByName("comment_" + id)[0].value = currentEntry.comment;
		});
	}
}

function new_current_entry(id){
	if (currentEntry == null){
		currentEntry = new Entry(0,0,0,0,0,0,0,0,0,0);
	}
	if (currentEntry.id != id){
		currentEntry = new Entry(
			document.getElementsByName("id_" + id)[0].value,
			document.getElementsByName("url_" + id)[0].value,
			document.getElementsByName("plate_" + id)[0].value,
			document.getElementsByName("state_" + id)[0].value,
			document.getElementsByName("date_" + id)[0].value,
			document.getElementsByName("lat_" + id)[0].value,
			document.getElementsByName("lon_" + id)[0].value,
			document.getElementsByName("street1_" + id)[0].value,
			document.getElementsByName("street2_" + id)[0].value,
			document.getElementsByName("comment_" + id)[0].value
		);
		$("#save" + id).prop('disabled', false);
		$("#save" + id).removeClass("disabled");
		return true;
	}
	else { return false; }
}

function format_date(date) {
	var d = new Date(date);
	var month = d.getMonth()+1;
	var day = d.getDate();
	var year = d.getFullYear();
	var hour = d.getHours();
	var meridiem = "AM"; if (hour > 12){ meridiem = "PM"; }
	if (hour > 12){ hour -= 12; }
	if (hour == 0){ hour = 12; }
	var min = d.getMinutes();
	var date_string = month + "/" + day + "/" + year + " " + hour + ":" + min + meridiem;
	return date_string;
}
</script>

</body>
</html>