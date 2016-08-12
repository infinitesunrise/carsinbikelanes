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

<script type="text/javascript">

var zoomToggles = new Map();

function toggleImg(link,id) {
	if (zoomToggles.has(id) && (zoomToggles.get(id))){
		var newImg = "../thumbs/" + link;
		var newHtml = "<img class='review' src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
		$("#" + id + "").empty();
		$("#" + id + "").html(newHtml);
		zoomToggles.set(id, false);
	}
	else {
		var newImg = "../images/" + link;
		var newHtml = "<img class='review' src=\"" + newImg + "\" onclick=\"javascript:toggleImg('" + link + "'," + id + ");\" />";
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
<body class='non_map'>

<?php

require('config.php');

if (isset($_GET["accept"])) {
	try {
		//MOVE SUBMISSION TO MAIN TABLE, DELETE QUEUE SUBMISSION, UPDATE IMAGE NAMES AND URLS
		$connection->beginTransaction();
		$connection->query('INSERT INTO cibl_data 
							SELECT * FROM cibl_queue 
							WHERE increment = ' . $_GET["accept"]);
		$id = $connection->insert_id();
		$old_url = mysqli_fetch_array($connection->query(
							'SELECT url 
							FROM cibl_queue 
							WHERE increment = ' . $_GET["accept"]))[0];
		$new_url = pathinfo($old_url)['dirname'] . '/' . $id . '.' . pathinfo($old_url)['extension'];
		$connection->query('UPDATE cibl_data SET url=\'' . $new_url . '\' WHERE increment=' . $id);
		$connection->query('DELETE FROM cibl_queue
							WHERE increment = ' . $_GET["accept"]);
		rename('../thumbs/' . $old_url, '../thumbs/' . $new_url);
		rename('../images/' . $old_url, '../images/' . $new_url);
		$connection->commit();
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
	LIMIT ' . $max_view . '
	OFFSET 0');

echo "\n <div class='flex_container_scroll'>";
echo "\n <div class='moderation_queue'>";
include 'nav.php';

while ($row = mysqli_fetch_array($entries)){
	echo "\n\n <div class='moderation_queue_row'>";
	echo "\n <div class='moderation_queue_buttons'>";
	echo "\n <button class='bold_button' onclick='javascript:accept(" . $row[0] . ");'>ACCEPT</button> <br>";
	echo "\n <button class='bold_button' onclick='javascript:deny(" . $row[0] . ");'>DENY</button>";
	echo "\n </div>";
	echo "\n <div id='" . $row[0] . "'>";
	echo "\n <img class='review' src='../thumbs/" . $row[1] . "' onclick=\"javascript:toggleImg('" . $row[1] . "', " . $row[0] . ");\"/>";
	echo "\n </div>";
	echo "\n <div class='moderation_queue_details'>";
	echo "\n <h2>#" . $row[0] . ": " . $row[2] . "</h2>";

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
}

echo "\n\n</div>";
echo "</div>";

?>

</body>
</html>