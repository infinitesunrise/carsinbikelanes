<?php

require 'config/config.php';

$id = $_GET["id"];

$full_query = "SELECT * FROM cibl_data WHERE increment =" . $id . " LIMIT 1";

$entries = mysqli_query($connection, $full_query);

$row = mysqli_fetch_array($entries);

$image_url = $row[1];
echo '<img src="images/' . $image_url . '" class="fullsize" />';
echo '<br>';
echo "<h2>#" . $row[0] . ": " . strtoupper($row[2]) . "</h2>\n";
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