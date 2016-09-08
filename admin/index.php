<?php require 'auth.php'; ?>

<html>
<head>

<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="../css/style.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'> 

<!-- license plate font by Dave Hansen -->
<link href='../css/license-plate-font.css' rel='stylesheet' type='text/css'>

<script type="text/javascript">

var zoomToggles = new Map();
var rotations = new Map();

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

function accept(id){
	var acceptURL = "index.php?accept=" + id;
	if (rotations.has(id)){ acceptURL += "&rot=" + rotations.get(id); }
	window.location.href = acceptURL;
}

function deny(id){
	window.location.href = "index.php?deny=" + id;
}

</script>
</head>
<body class='non_map'>

<?php

require('config_pointer.php');

if (isset($_GET["accept"])) {
	try {
		//MOVE SUBMISSION TO MAIN TABLE, DELETE QUEUE SUBMISSION, UPDATE IMAGE NAMES AND URLS
		$connection->begin_transaction();
		$connection->query('INSERT INTO cibl_data 
							SELECT * FROM cibl_queue 
							WHERE increment = ' . $_GET["accept"]);
		$id = $connection->insert_id;
		$old_url = mysqli_fetch_array($connection->query(
							'SELECT url 
							FROM cibl_queue 
							WHERE increment = ' . $_GET["accept"]))[0];
		$new_url = pathinfo($old_url)['dirname'] . '/' . $id . '.' . pathinfo($old_url)['extension'];
		$connection->query('UPDATE cibl_data SET url=\'' . $new_url . '\' WHERE increment=' . $id);
		$connection->query('DELETE FROM cibl_queue
							WHERE increment = ' . $_GET["accept"]);
		$connection->commit();
		rename('../thumbs/' . $old_url, '../thumbs/' . $new_url);
		rename('../images/' . $old_url, '../images/' . $new_url);
		
		if (isset($_GET['rot'])){
			$source = imagecreatefromjpeg('../images/' . $new_url);
			$rotate = imagerotate($source, -$_GET['rot'], 0);
			imagejpeg($rotate, '../images/' . $new_url);
			
			$source = imagecreatefromjpeg('../thumbs/' . $new_url);
			$rotate = imagerotate($source, -$_GET['rot'], 0);
			imagejpeg($rotate, '../thumbs/' . $new_url);
			imagedestroy($source);
			imagedestroy($rotate);
		}
		
	}
	catch (Exception $e) {
		error_log('MySQL transaction exception: ' . $e);
		$connection->rollback();
	}
}

if (isset($_GET["deny"])) {
	//DELETE QUEUED SUBMISSION AND ASSOCIATED FILES
	$url = mysqli_fetch_array($connection->query(
							'SELECT url 
							FROM cibl_queue 
							WHERE increment = ' . $_GET["deny"]))[0];					
	$file_thumb = "../thumbs/" . $url;
	$file_image = "../images/" . $url;
	unlink($file_thumb);
	unlink($file_image);
	$connection->query('DELETE FROM cibl_queue 
						WHERE increment = ' . $_GET["deny"]);
}

$entries = $connection->query(
	'SELECT *
	FROM cibl_queue
	ORDER BY date_added ASC
	LIMIT ' . $config['max_view'] . '
	OFFSET 0');

echo "\n <div class='flex_container_scroll'>";
echo "\n <div class='moderation_queue' id='moderation_queue'>";
include 'nav.php';

$count = 0;
while ($row = mysqli_fetch_array($entries)){
	echo "\n\n <div class='moderation_queue_row'>";
	echo "\n <div class='moderation_queue_buttons'>";
	echo "\n <button class='bold_button' onclick='javascript:accept(" . $row[0] . ");'>ACCEPT</button> <br>";
	echo "\n <button class='bold_button' onclick='javascript:deny(" . $row[0] . ");'>DENY</button>";
	echo "\n <div style='width:100%; display:flex;'>";
	echo "<button class='rotate' onClick='rotate(-90," . $row[0] . ")'>&#10553</button>";
	echo "<div style='width:10px'></div>";
	echo "<button class='rotate' onClick='rotate(90," . $row[0] . ")'>&#10552</button>";
	echo "</div>";
	echo "\n </div>";
	echo "\n <div id='" . $row[0] . "' class='mod_queue_img_container'>";
	echo "\n <img id='img" . $row[0] . "' class='review' src='../thumbs/" . $row[1] . "' onclick=\"javascript:toggleImg('" . $row[1] . "', " . $row[0] . ");\"/>";
	echo "\n </div>";
	echo "\n <div class='moderation_queue_details'>";
	if ($row[3] == "NYPD"){
		$plate_split = str_split($row[2], 4);
		echo "\n <div class='plate_name'><div><h2>#" . $row[0] . ":</h2></div> <div class='plate NYPD'>" . $plate_split[0] . "<span class='NYPDsuffix'>" . $plate_split[1] . "</span></div></div>";
	}
	else {
		echo "\n <div class='plate_name'><div><h2>#" . $row[0] . ":</h2></div> <div class='plate ". $row[3] . "'>" . $row[2] . "</div></div>";
	}

	$datetime = new DateTime($row[4]);

	echo "\n <p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ ";

	if ($row[8] !== ''){
		echo strtoupper($row[8]);
		if ($row[9] !== ''){
			echo " & " . strtoupper($row[9]);
		}
	} else { 
		echo $row[6] . " / " . $row[7];
	}

	echo "</p>";
	echo "\n <p class='entry_comment'>" . nl2br($row[10]) . "</p>";
	echo "\n </div>";
	echo "\n </div>";
	$count++;
}

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
</html>