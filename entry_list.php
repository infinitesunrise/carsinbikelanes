<html>
<?php

include 'config.php';

$west;
$east;
$south;
$north;
$plate_query = "";

if (isset($_GET["plate"])){
	$east = -73.6619;
	$west = -74.2655;
	$south = 40.490617;
	$north = 40.9168;
	$plate_query = "AND (plate = '" . $_GET["plate"] . "') ";
}
else {
	$west = $_GET["west"];
	$east = $_GET["east"];
	$south = $_GET["south"];
	$north = $_GET["north"];
}

$gps_query = " WHERE (gps_lat BETWEEN " . $south . " AND " . $north . ") AND (gps_long BETWEEN " . $west . " AND " . $east . ") ";
$full_query =
"SELECT *
FROM `" . $table . "` 
" . $gps_query . $plate_query . "
AND (approved = 1) 
ORDER BY date_added DESC
LIMIT " . $max_view . "
OFFSET 0";

$entries = mysqli_query($connection, $full_query);

while ($row = mysqli_fetch_array($entries)){
	echo "\n <div class='column_entry' onClick='zoomToEntry(" . $row[7] . ", " . $row[8] . ", " . $row[0] . ");'>";
		
	echo "\n <div class='column_entry_thumbnail'>";
	echo "\n <img class='thumbnail' src=thumbs/" . $row[2] . " />";
	echo "\n </div>";
	
	echo "\n <div class='column_entry_info'>";
	echo "\n <h2>#" . $row[0] . ": <a class='plate_text' onclick='plateSearch(\"" . $row[3] . "\")'>" . $row[3] . "</h2></a>";
	$datetime = new DateTime($row[5]);
	echo "\n <p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ <a class='coords' onclick='body_map.setZoomAround([" . $row[7] . "," . $row[8] . "], 17);'>";
	
	if ($row[9] !== ''){
		echo strtoupper($row[9]);
		if ($row[10] !== ''){
			echo " & " . strtoupper($row[10]);
		}
	}
	else { 
		echo $row[7] . " / " . $row[8];
	}
	
	echo "</a></p>\n";
	echo "\n <p class='entry_comment'>" . nl2br($row[11]) . "</p>";
	echo "\n </div>";
	
	echo "\n </div>";
	echo "\n <br>";
	
	echo "\n <script> ";
	echo "\n $(document).ready(function() { ";
	echo "\n marker" . $row[0] . " = new L.marker([" . $row[7] . ", " . $row[8] . "], {title: '#" . $row[0] . ": " . strtoupper($row[3]) . "'}).addTo(body_map);";
	echo "\n markers.addLayer(marker" . $row[0] . ");";
	echo "\n }); ";
	echo "\n";
	echo "\n marker" . $row[0] . ".on('click', function(e) {";
	echo "\n	zoomToEntry(" . $row[7] . ", " . $row[8] . ", " . $row[0] . ");";
	echo "\n });";
	echo "\n </script>";
	echo "\n";
}
?>

<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

<script type="text/javascript">
$('.coords, .plate_text').click(function(e) {
	e.stopPropagation();
	e.preventDefault();
});

function plateSearch(plate) {
	single_view = true;
	var load_url = "entry_list.php?plate=" + plate;
	$( "#inner_inner_container" ).load( load_url );
	markers.clearLayers();
	body_map.setZoom(13);
	setTimeout(function() { single_view = false; }, 1000);
}
</script>

</html>