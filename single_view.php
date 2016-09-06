<?php

require 'admin/config_pointer.php';

$id = $_GET["id"];

$full_query = "SELECT * FROM cibl_data WHERE increment =" . $id . " LIMIT 1";

$entries = mysqli_query($connection, $full_query);

$row = mysqli_fetch_array($entries);

$image_url = $row[1];
echo '<img src="images/' . $image_url . '" class="fullsize" />';
echo '<br>';

if ($row[3] == "NYPD"){
	$plate_split = str_split($row[2], 4);
	echo "\n <div class='plate_name'><div><h2>#" . $row[0] . ":</h2></div> <div class='plate NYPD'><a class='plate_text' onclick='plateSearch(\"" . $row[2] . "\")'>" . $plate_split[0] . "</a><span class='NYPDsuffix'>" . $plate_split[1] . "</span></div></div>";
}
else {
	echo "\n <div class='plate_name'><div><h2>#" . $row[0] . ":</h2></div> <div class='plate ". $row[3] . "'><a class='plate_text' onclick='plateSearch(\"" . $row[2] . "\")'>" . $row[2] . "</a></div></div>";
}

$datetime = new DateTime($row[4]);
echo "<p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ <a class='coords' onclick='body_map.setZoomAround([" . $row[6] . "," . $row[7] . "], 17);'>";

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
echo "<p class='entry_comment'>" . nl2br($row[10]) . "</p>\n";

?>