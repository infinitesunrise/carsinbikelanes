<html>
<?php

require 'config/config.php';

$west;
$east;
$south;
$north;
$plate_query = "";

if (isset($_GET['plate'])){
	$east = $config['east_bounds'];
	$west = $config['west_bounds'];
	$south = $config['south_bounds'];
	$north = $config['north_bounds'];
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
FROM cibl_data 
" . $gps_query . $plate_query . "
ORDER BY date_added DESC 
LIMIT " . $config['max_view'] . "
OFFSET 0";

$entries = mysqli_query($connection, $full_query);
$count = mysqli_num_rows($entries);
$lat_total = 0;
$long_total = 0;

if ($count == 0){
	echo "\n <div class='column_entry'>";
	echo "<h3 style='padding:10px'>No records found here.</h3>";
	echo "\n </div>";
}

while ($row = mysqli_fetch_array($entries)){
	if (isset($_GET['plate'])) { $lat_total += $row[6]; $long_total += $row[7]; }
	
	echo "\n <div class='column_entry' onClick='zoomToEntry(" . $row[6] . ", " . $row[7] . ", " . $row[0] . ");'>";
		
	echo "\n <div class='column_entry_thumbnail'>";
	echo "\n <img class='thumbnail' src=thumbs/" . $row[1] . " />";
	echo "\n </div>";
	
	echo "\n <div class='column_entry_info'>";
	echo "\n <h2>#" . $row[0] . ": <a class='plate_text' onclick='plateSearch(\"" . $row[2] . "\")'>" . $row[2] . "</h2></a>";
	$datetime = new DateTime($row[4]);
	echo "\n <p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ <a class='coords' onclick='body_map.setZoomAround([" . $row[6] . "," . $row[7] . "], 17);'>";
	
	if ($row[8] !== ''){
		echo strtoupper($row[8]);
		if ($row[9] !== ''){
			echo " & " . strtoupper($row[9]);
		}
	}
	else { 
		echo $row[6] . " / " . $row[7];
	}
	
	echo "</a></p>\n";
	echo "\n <p class='entry_comment'>" . nl2br($row[10]) . "</p>";
	echo "\n </div>";
	
	echo "\n </div>";
	echo "\n";
	
	echo "\n <script> ";
	echo "\n $(document).ready(function() { ";
	echo "\n marker" . $row[0] . " = new L.marker([" . $row[6] . ", " . $row[7] . "], {title: '#" . $row[0] . ": " . strtoupper($row[2]) . "'}).addTo(body_map);";
	echo "\n markers.addLayer(marker" . $row[0] . ");";
	echo "\n }); ";
	echo "\n";
	echo "\n marker" . $row[0] . ".on('click', function(e) {";
	echo "\n	zoomToEntry(" . $row[6] . ", " . $row[7] . ", " . $row[0] . ");";
	echo "\n });";
	echo "\n </script>";
	echo "\n";
}

if (isset($_GET['plate'])){
$lat_average = $lat_total / $count;
$long_average =  $long_total / $count;
echo "\n <script type='text/javascript'>";
echo "\n body_map.setView([" . $lat_average . ", " . ($long_average - 0.05) . "], 12);";
echo "\n setTimeout(function() { stop_load_entries = false; }, 1000);";
echo "\n </script>";
}
?>

<script type="text/javascript">
$('.coords, .plate_text').click(function(e) {
	e.stopPropagation();
	e.preventDefault();
});

function plateSearch(plate) {
	var load_url = "entry_list.php?plate=" + plate;
	$( "#inner_container" ).load( load_url );
	stop_load_entries = true;
	markers.clearLayers();
	open_window('entry_list');
}
</script>

</html>