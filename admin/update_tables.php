<html>
<head>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

<style type='text/css'>
body {
	font-family: "Courier New", Courier, monospace;
}

</style>

<script>
window.setInterval(function(){
	console.log('level 1');
	$('html, body').animate({ 
	   scrollTop: $(document).height()}, 
	   200,
	   'swing'
	);
}, 300);
</script>

</head>
<body>
<?php

require 'config_pointer.php';

$new_rows_queue = mysqli_multi_query($connection, 'ALTER TABLE cibl_queue ADD council_district int(11) NOT NULL AFTER street2; ALTER TABLE cibl_queue ADD precinct int(11) NOT NULL AFTER council_district; ALTER TABLE cibl_queue ADD community_board text NOT NULL AFTER precinct;');

if(!$new_rows_queue){ 
	echo 'Error adding new columns to cibl_queue, aborting: ' . $connection->error;
	exit;
}
else { echo 'New columns successfully added to cibl_queue.<br/>'; }

do { 
    $connection->use_result(); 
}while( $connection->more_results() && $connection->next_result() );

$new_rows_data = mysqli_multi_query($connection, 'ALTER TABLE cibl_data ADD council_district int(11) NOT NULL AFTER street2; ALTER TABLE cibl_data ADD precinct int(11) NOT NULL AFTER council_district; ALTER TABLE cibl_data ADD community_board text NOT NULL AFTER precinct;');

if(!$new_rows_data){ 
	echo 'Error adding new columns to cibl_data, aborting: ' . $connection->error;
	exit;
}
else { echo 'New columns successfully added to cibl_data.<br/>'; }

do { 
    $connection->use_result(); 
}while( $connection->more_results() && $connection->next_result() );

$max_increment = mysqli_fetch_array(mysqli_query($connection, "SELECT MAX(increment) AS increment FROM cibl_data"))[0] + 1;
$count = 1;

ob_start();
while ($count < $max_increment){
	$buffer = str_repeat(" ", 4096);
	$buffer .= "\r\n<span></span>\r\n";
	
	echo 'Processing #' . $count . '...    ';
	
	$query = 'SELECT gps_lat, gps_long FROM cibl_data WHERE increment = ' . $count;
	$result = $connection->query($query);
	
	if ($result->num_rows){
		$gps = mysqli_fetch_array($result);
		$address = get_address($gps[0], $gps[1]);
		$districts = get_districts($address[0], $address[1]);
		echo 'Council District: ' . $districts[0] . ' Precinct: ' . $districts[1] . ' Community Board ' . $districts[2];
		
		$update = mysqli_query($connection, 'UPDATE cibl_data SET council_district = ' . $districts[0] . ', precinct = ' . $districts[1] . ', community_board="' . $districts[2] . '" WHERE increment = ' . $count);
		if ($update){ echo ' ...Updated.'; }
		else{ echo ' ERROR: Failed to update database entry.'; }
	}
	
	else {
		echo 'none.<br/>'.$buffer;
		ob_flush();
		flush();
		$count++;
		continue;
	}
	
	echo '<br/>'.$buffer;
	ob_flush();
    flush();
	sleep(1);
	$count++;
}
ob_end_flush();

echo 'Done.';

/*
$debug_latitude = 40.657574;
$debug_longitude = -73.957604;
$debug_address = '75 Hawthorne St';
$debug_zip = '11225';

$address = get_address($debug_latitude, $debug_longitude);
$districts = get_districts($address[0], $address[1]);
echo $districts[0] . ' / ' . $districts[1] . ' / ' . $districts[2];
*/

function get_address($latitude, $longitude){
	if (!($latitude && $longitude)){ return false; }
	
	$base = 'http://geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode';
	$location = '?location='.$longitude.'%2C'.$latitude;
	$f = '&f=pjson';
	$distance = '&distance=150';
	$returnIntersection = '&returnIntersection=false';
	$url = $base . $location . $f . $distance . $returnIntersection;
	
	$json = file_get_contents($url);
	$result = json_decode($json);

	return array($result->address->Address, $result->address->Postal);
}

function get_districts($address, $zip){
	if (!$address){ return false; }
	
	$base = 'https://api.cityofnewyork.us/geoclient/v1/address.json';
	$address_array = explode(' ', $address, 2);
	$houseNumber = '?houseNumber='.$address_array[0];
	$street = '&street='.urlencode($address_array[1]);
	$zip = '&zip='.$zip;
	$app_id = '&app_id=b1b997d1';
	$app_key = '&app_key=a603e788b1c61d38d8c8b74772006831';
	$url = $base . $houseNumber . $street . $zip . $app_id . $app_key;
	
	$json = file_get_contents($url);
	$result = json_decode($json);
	
	$council_district = $result->address->cityCouncilDistrict;
	$council_district = ltrim($council_district, '0');
	$precinct = $result->address->policePrecinct;
	$precinct = ltrim($precinct, '0');
	$community_board = $result->address->communityDistrictNumber;
	$community_board = ltrim($community_board, '0');
	$boroughs = array('M', 'BX', 'BK', 'Q', 'ST');
	$community_board = $boroughs[$result->address->communityDistrictBoroughCode - 1] . $community_board;
	
	return array($council_district, $precinct, $community_board);
}

?>

</body>
</html>