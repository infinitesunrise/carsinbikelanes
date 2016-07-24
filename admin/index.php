<!DOCTYPE html>
<html>
<head>

<!-- main stylesheet -->
<link rel="stylesheet" type="text/css" href="../style.css" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

<!-- google fonts -->
<link href='http://fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>

<!-- admin-specific css -->
<style type="text/css">
body {
	overflow: scroll;
}

.moderation_queue_table {
	display: inline-block;
	margin: 10px 10px 10px 100px;
	border: 2px solid black;
}

.moderation_queue_row {
	width: 100%;
	border: 2px solid black;
}

.moderation_queue_cell {
	vertical-align: top;
	display: inline-block;
}

.moderation_queue_cell_details {
	display: inline-block;
	width: 450px;
}

button {
	font-size: 32px;
	font-family: "Oswald",sans-serif;
	width: 200px;
	margin: 10px;
}

img {
	margin: 10px;
}
</style>

<script type="text/javascript">

var zoomToggles = new Map();

function toggleImg(link,id) {
	if (zoomToggles.has(id)){
		if (zoomToggles.get(id)){
			var newImg = "../thumbs/" + link;
			var newHtml = "<img src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
			$("#" + id + "").empty();
			$("#" + id + "").html(newHtml);
			zoomToggles.set(id, false);
		}
		else {
			var newImg = "../images/" + link;
			var newHtml = "<img src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
			$("#" + id + "").empty();
			$("#" + id + "").html(newHtml);
			zoomToggles.set(id, true);
		}
	}
	else {
		var newImg = "../images/" + link;
		var newHtml = "<img src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
		$("#" + id + "").empty();
		$("#" + id + "").html(newHtml);
		zoomToggles.set(id, true);

	}
}

function accept(id){
	window.location.href = "index.php?accept=" + id;
}

function deny(id){
	window.location.href = "index.php?deny=" + id;
}

</script>

</head>
<body>

Hint: You may want to password-protect the /admin directory that this page lives in.

<?php

include("../config.php");

if (isset($_GET["accept"])) {
	$accept_query = "UPDATE " . $table . " SET approved = 1 WHERE increment = " . $_GET["accept"];
	$entries = mysqli_query($connection, $accept_query);
}

if (isset($_GET["deny"])) {
	$file_query = "SELECT url FROM " . $table . " WHERE increment = " . $_GET["deny"];
	$file_result = mysqli_query($connection, $file_query);
	while ($row = mysqli_fetch_array($file_result)){
		$file_thumb = "../thumbs/" . $row[0];
		$file_image = "../images/" . $row[0];
		unlink($file_thumb);
		unlink($file_image);
	}	
	$delete_query = "DELETE FROM " . $table . " WHERE increment = " . $_GET["deny"];
	$entries = mysqli_query($connection, $delete_query);
}

$full_query =
"SELECT *
FROM `" . $table . "` 
WHERE approved = 0 
ORDER BY date_added ASC
LIMIT " . $max_view . "
OFFSET 0";

$entries = mysqli_query($connection, $full_query);

echo "\n <div class=\"moderation_queue_table\">\n";

while ($row = mysqli_fetch_array($entries)){

echo "\n <div class=\"moderation_queue_row\">";
echo "\n <div class=\"moderation_queue_cell\">";
echo "\n <button onclick='javascript:accept(" . $row[0] . ");'>ACCEPT</button> <br>";
echo "\n <button onclick='javascript:deny(" . $row[0] . ");'>DENY</button>";
echo "\n </div>";

echo "\n <div class=\"moderation_queue_cell\" id=\"" . $row[0] . "\">";
echo "\n <img src=\"../thumbs/" . $row[2] . "\" onclick=\"javascript:toggleImg('" . $row[2] . "', " . $row[0] . ");\"/>";
echo "\n </div>";

echo "\n <div class=\"moderation_queue_cell_details\">";
echo "\n <h2>#" . $row[0] . ": " . $row[3] . "</h2>";
$datetime = new DateTime($row[5]);
echo "\n <p class='entry_details'>" . strtoupper($datetime->format('m/d/Y g:ia')) . " @ ";
if ($row[9] !== ''){
	echo strtoupper($row[9]);
	if ($row[10] !== ''){
		echo " & " . strtoupper($row[10]);
	}
}
else { 
	echo $row[7] . " / " . $row[8];
}
echo "</p>";
echo "\n <p class='entry_comment'>" . nl2br($row[11]) . "</p>";
echo "\n </div>";
echo "\n </div>";
echo "\n";
echo "\n";
}

echo "\n\n</div>";

?>

</body>
</html>