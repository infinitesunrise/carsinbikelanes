<?php

require(__DIR__ . '/../admin/config_pointer.php');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method){
	case 'GET': 
		error_log('RECEIVED A GET REQUEST');
		handleGET();
		break;
	case 'POST': 
		error_log('RECEIVED A POST REQUEST');
		handlePOST();
		break;		
	default:
		return_error('Incompatible HTTP request');
		break;
}

function handleGET(){
	$box = $around = $plate = $before = $after = $streets = $max = '';
	
	//Box search
	if(isset($_GET['box'])){
		$box = explode(',',$_GET['box']);
		
		//Make sure there are four values for N, S, E, W
		if( count($box) != 4 ) {
			return_error('Search box query did not supply four values representing North, South, East and West search bounds');
		}
		
		//Make sure each bound value is a number
		for($i = 0; $i < count($box); $i++){
			if (!is_numeric($box[$i])){
				return_error('Search box boundary values must be numeric.');
			}
		}
		
		$box_north = $box[0];
		$box_south = $box[1];
		$box_east = $box[2];
		$box_west = $box[3];
	}
	
	//Around search
	if(isset($_GET['around'])){
		$around = explode(',',$_GET['around']);
		
		//Make sure there are three values for lat, long and distance
		if( count($around) != 3 ) {
			return_error('Search around query did not supply three values representing GPS latitude, GPS longitude and distance from coordinate to middle of box edges.');
		}
		
		//Make sure each around value is a number
		for($i = 0; $i < count($around); $i++){
			if (!is_numeric($around[$i])){
				return_error('Around search array values must be numeric.');
			}
		}
		
		$around_latitude = $around[0];
		$around_longitude = $around[1];
		$around_distance = $around[2];
		
		$around_north = $around_latitude + $around_distance;
		$around_south = $around_latitude - $around_distance;
		$around_east = $around_longitude + $around_distance;
		$around_west = $around_longitude - $around_distance;
	}
	
	//Plate search
	if(isset($_GET['plate'])){
		
		//8 characters max
		$plate = substr($_GET['plate'], 0, 8);
		
		//Alpha-numeric only
		if(!ctype_alnum($plate)){
			return_error('Plate text must be alpha-numeric.');
		}
	}
	
	//Before-time search
	if(isset($_GET['before'])){
		$before = $_GET['before'];
		
		//Check if numberic and is int
		if( !( is_numeric($before) && (int)$before == $before ) ){
			return_error('Before value must be a unix timestamp in integer format');
		}
	}
	
	//After-time search
	if(isset($_GET['after'])){
		$after = $_GET['after'];
		
		//Check if numberic and is int
		if( !( is_numeric($after) && (int)$after == $after ) ){
			return_error('After value must be a unix timestamp in integer format');
		}
	}
	
	//Streets search
	if(isset($_GET['streets'])){
		$streets = explode(',', $_GET['streets']);
		
		//Make sure type is array
		if(!is_array($streets)){
			return_error('Streets filter must be an array of string values');
		}
		
		//Make sure all streets values are strings
		for($i = 0; $i < count($streets); $i++){
			if (!is_string($streets[$i])){
				return_error('Streets array must be composed of only string values');
			}
		}
	}
	
	//Max results
	if(isset($_GET['max'])){
		$max = $_GET['max'];
		
		//Make sure max value is a number
		if(!is_numeric($max)){
			return_error('Max results value was not a number');
		}
	}
	
	$search_echo = new StdClass();
	if($box) { $search_echo->box = $box; }
	if($around) { $search_echo->around = $around; }
	if($plate) { $search_echo->plate = $plate; }
	if($before) { $search_echo->before = $before; }
	if($after) { $search_echo->after = $after; }
	if($streets) { $search_echo->streets = $streets; }
	if($max) { $search_echo->max = $max; }
	
	$response = new StdClass();
	
	$response->search = $search_echo;
	
	error_log(print_r($response, true));
	
	//RESPONSE
	http_response_code(200);
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	echo json_encode($response);
}
	
function handlePOST(){
	if(isset($_POST['box'])){
		$box = $_POST['box'];
	}
	
	error_log(print_r($box, true));
	
	http_response_code(200);
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');	
	//echo json_encode($request);	
}

function return_error($error){
	http_response_code(400);
	header('Content-type: application/json');
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');	
	$response = ['error' => $error];	
	echo json_encode($response);	
}
?>