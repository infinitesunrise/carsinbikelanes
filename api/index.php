<?php

require(__DIR__ . '/../admin/config_pointer.php');
require('../submission.php');

$path = parse_url($_SERVER['REQUEST_URI'])['path'];
$method = $_SERVER['REQUEST_METHOD'];

//error_log(print_r($_REQUEST, true));
//error_log(print_r($_POST, true));
//error_log(print_r($_GET, true));
//error_log(print_r($_FILES, true));

switch ($path){
	case '/api/upload': 
		if ($method = 'POST'){
			upload();
		}
		break;
	case '/api/search': 
		if ($method == 'GET'){
			search($connection);
		}
		break;
	case '/api/stats': 
		if ($method = 'GET'){
			stats();
		}
		break;
	default:
		return_error('Incompatible request');
		break;
}

function search($connection){
	
	$box = $around = $id = $plate = $before = $after = $streets = $description = $max = '';
	date_default_timezone_set('UTC');
	
	//Box search
	if(isset($_GET['box'])){		
		$box_array = $_GET['box'];
		
		//error_log('GOT BOX!');
		//error_log(print_r($box_array, true));
		
		//Make sure box is an array
		if (!is_array($box_array)){
			//In case array was given as comma-seperated string, try to explode to array
			$exploded = explode(',', $box_array);
			if (!is_array($exploded) || !$exploded){
				return_error('Search box query must be an array.');
			}
			else { $box_array = $exploded; }
		}
		
		//Make sure there are four values for N, S, E, W
		if( count($box_array) != 4 ) {
			return_error('Search box query did not supply four numbers representing North, South, East and West search bounds.');
		}
		
		//Make sure each bound value is a number
		for($i = 0; $i < count($box_array); $i++){
			if (!is_numeric($box_array[$i])){
				return_error('Search box boundary values must be numeric.');
			}
		}
		
		$box_north = $box_array[0];
		$box_south = $box_array[1];
		$box_east = $box_array[2];
		$box_west = $box_array[3];
		
		$box = 'AND gps_lat<' . $box_north . ' AND gps_lat>' . $box_south . ' AND gps_long<' . $box_east . ' AND gps_long>' . $box_west . ' ';
		
		//error_log($box);
	}
	
	//Around search
	if(isset($_GET['around'])){
		$around_array = $_GET['around'];
		
		//Make sure around is an array
		if (!is_array($around_array)){
			return_error('Around query must be an array.');
		}
		
		//Make sure there are three values for lat, long and distance
		if( count($around_array) != 3 ) {
			return_error('Search around query did not supply three values representing latitude, longitude and distance in degrees from coordinate to middle of box edges.');
		}
		
		//Make sure each around value is a number
		for($i = 0; $i < count($around_array); $i++){
			if (!is_numeric($around_array[$i])){
				return_error('Around search array values must be numeric.');
			}
		}
		
		$around_latitude = $around_array[0];
		$around_longitude = $around_array[1];
		$around_distance = $around_array[2];
		
		$around_north = $around_latitude + $around_distance;
		$around_south = $around_latitude - $around_distance;
		$around_east = $around_longitude + $around_distance;
		$around_west = $around_longitude - $around_distance;
		
		$around = 'AND gps_lat<' . $around_north . ' AND gps_lat>' . $around_south . ' AND gps_long<' . $around_west . ' AND gps_long>' . $around_east . ' ';
	}
	
	//ID search
	if(isset($_GET['id'])){
		$id_int = $_GET['id'];
		
		//Check if numeric and is int
		if( !( is_numeric($id_int) && (int)$id_int == $id_int ) ||  $id_int < 1 ){
			return_error('ID value must be a positive integer');
		}
		
		$id = 'AND increment=' . $id_int . ' ';
	}
	
	//Plate search
	if(isset($_GET['plate'])){
		//8 characters max
		$plate_string = substr($_GET['plate'], 0, 8);
		
		//Alpha-numeric only
				//error_log($_GET['plate']);
				//error_log(ctype_alnum($plate_string));
		if(!ctype_alnum($plate_string)){
			return_error('Plate text must be alpha-numeric.');
		}
		
		$plate = 'AND plate LIKE "%' . $plate_string . '%" ';
	}
	
	//Before-time search
	if(isset($_GET['before'])){
		
		$before_string = new DateTime($_GET['before']);
		if (!$before_string){
			return_error('Did not understand the before date value ' . $_GET['before'] . '. Before date must be a valid ISO8601 string.');
		}
		
		//$before_int = $_GET['before'];
		
		//Check if numeric and is int
		//if( !( is_numeric($before_int) && (int)$before_int == $before_int ) ){
		//	return_error('Before value must be a unix timestamp in integer format');
		//}
		
		//$date_before = new DateTime("@$before_int");
		
		$before = 'AND date_occurrence<"' . $before_string->format('Y-m-d H:i:s') . '" ';
	}
	
	//After-time search
	if(isset($_GET['after'])){
		
		$after_string = new DateTime($_GET['after']);
		if (!$after_string){
			return_error('Did not understand the after date value ' . $_GET['after'] . '. After date must be a valid ISO8601 string.');
		}
		//$after_int = $_GET['after'];
		
		//Check if numeric and is int
		//if( !( is_numeric($after_int) && (int)$after_int == $after_int ) ){
		//	return_error('After value must be a unix timestamp in integer format');
		//}
		
		//$date_after = new DateTime("@$after_int");
		
		$after = 'AND date_occurrence>"' . $date_string->format('Y-m-d H:i:s') . '" ';
	}
	
	//Streets search
	if(isset($_GET['streets'])){
		$streets_array = $_GET['streets'];
		
		//Make sure type is array
		if(!is_array($streets_array)){
			return_error('Streets filter must be an array of string values');
		}
		
		//Make sure all streets values are strings
		for($i = 0; $i < count($streets_array); $i++){
			if (!is_string($streets_array[$i])){
				return_error('Streets array must be composed of only string values');
			}
		}
		
		$streets = 'AND ';
		foreach($streets_array as $street){
			$streets .= '(street1 LIKE "%' . $street . '%" OR street2 LIKE "%' . $street . '%") ';
			if (count($streets_array) < $street + 2){
				$streets .= ' AND ';
			}
		}
	}
	
	//Description search
	if(isset($_GET['description'])){
		//8 characters max
		$description_string = substr($_GET['description'], 0, 8);
		
		$description = 'AND description LIKE "%' . $description_string . '%" ';
	}
	
	//Max results
	if(isset($_GET['max'])){
		$max_int = $_GET['max'];
		
		//Make sure max value is a number
		if(!is_numeric($max_int)){
			return_error('Max results value was not a number');
		}
		
		//Make sure max is greater than 0
		if($max_int < 1){
			return_error('Max results requested must be greater than zero.');
		}
		
		$max = 'LIMIT ' . $max_int . ' ';
	}
	
	$query = 'SELECT * FROM cibl_data WHERE increment>0 ' . $box . $around . $id . $plate . $before . $after . $streets . $description . 'ORDER BY increment DESC ' . $max;
	
	//error_log($query);
	
	$result = $connection->query($query);
	$entries = array();
	
	while($row = mysqli_fetch_row($result)){
		$entry = new StdClass();
		$entry->id = $row[0];
		$entry->image_url = $_SERVER['HTTP_HOST'] . '/images/' . $row[1];
		$entry->thumb_url = $_SERVER['HTTP_HOST'] . '/thumbs/' . $row[1];
		$entry->plate = $row[2];
		$entry->state = $row[3];
		$entry->date_occurrence = $row[4]; //gmdate('U',strtotime($row[4]) . ' GMT');
		$entry->date_added = $row[5]; //gmdate('U',strtotime($row[5] . ' GMT'));
		$entry->gps_latitude = $row[6];
		$entry->gps_longitude = $row[7];
		$entry->street1 = $row[8];
		$entry->street2 = $row[9];
		$entry->description = $row[10];
		array_push($entries, $entry);
	}
	
	$search = new StdClass();
	if($box) { $search->box = $box_array; }
	if($around) { $search->around = $around_array; }
	if($plate) { $search->plate = $plate_string; }
	if($before) { $search->before = $before_string; }
	if($after) { $search->after = $after_string; }
	if($streets) { $search->streets = $streets_array; }
	if($description) { $search->description = $description_string; }
	if($max) { $search->max = $max_int; }
	
	$response = new StdClass();	
	$response->search = $search;
	$response->entries = $entries;	
	
	http_response_code(200);
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	echo json_encode($response);
}
	
function upload(){
	date_default_timezone_set('UTC');
	
	$image = (isset($_FILES['image']) ? $_FILES['image'] : '');
		$image_name = (isset($_FILES['image']) ? $image['name'] : '');
		$image_type = (isset($_FILES['image']) ? $image['type'] : '');
		$image_error = (isset($_FILES['image']) ? $image['error'] : '');
		$image_size = (isset($_FILES['image']) ? $image['size'] : '');
	$plate = (isset($_POST['plate']) ? $_POST['plate'] : '');
	$state = (isset($_POST['state']) ? $_POST['state'] : '');
	$date_occurrence = (isset($_POST['date']) ? $_POST['date'] : '');
	$date_added = date('c');
	$gps_latitude = (isset($_POST['gps_latitude']) ? $_POST['gps_latitude'] : '');
	$gps_longitude = (isset($_POST['gps_longitude']) ? $_POST['gps_longitude'] : '');
	$street1 = (isset($_POST['street1']) ? $_POST['street1'] : '');
	$street2 = (isset($_POST['street2']) ? $_POST['street2'] : '');
	$description = (isset($_POST['description']) ? $_POST['description'] : '');
	
	$upload = new StdClass();
	$upload->image = array('name' => $image_name, 'type' => $image_type, 'error' => $image_error, 'size' => $image_size);
	$upload->plate = $plate;
	$upload->state = $state;
	$upload->date_occurrence = $date_occurrence;
	$upload->date_added = $date_added;
	$upload->gps_latitude = $gps_latitude;
	$upload->gps_longitude = $gps_longitude;
	$upload->street1 = $street1;
	$upload->street2 = $street2;
	$upload->description = $description;
	
	//error_log($upload->date);
	
	$result = new_upload($image,
				$plate,
				$state,
				$date_occurrence,
				$date_added,
				$gps_latitude,
				$gps_longitude,
				$street1,
				$street2,
				$description);
				
	$response = new StdClass();
	$response->result = $result;
	$response->upload = $upload;
	
	if (array_key_exists('success', $result)){ http_response_code(200); }
	else if (array_key_exists('error', $result)){ http_response_code(400); }
	
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	//error_log(print_r($response, true));
	//error_log(json_encode($response));
	echo json_encode($response);
}

function stats(){
	return_error('This method is not implemented yet');
}

function return_error($error){
	http_response_code(400);
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');	
	$response = ['error' => $error];	
	echo json_encode($response);
	exit();	
}

?>