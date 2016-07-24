<html>
<?php

include 'config.php';

$id = $_GET["id"];

$full_query = "SELECT * FROM `bikelane` WHERE increment =" . $id . " LIMIT 1";

$entries = mysqli_query($connection, $full_query);

$row = mysqli_fetch_array($entries);

$image_url = $row[2];
echo '<img src="images/' . $image_url . '" class="fullsize" />';
echo '<br>';
echo "<h2>#" . $row[0] . ": " . strtoupper($row[3]) . "</h2>\n";
$datetime = new DateTime($row[5]);
echo "<p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ <a class='coords' onclick='body_map.setZoomAround([" . $row[7] . "," . $row[8] . "], 17);'>";

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
echo "<p class='entry_comment'>" . nl2br($row[11]) . "</p>\n";

?>
</html>