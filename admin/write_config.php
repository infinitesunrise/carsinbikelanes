<?php

function write_config($new_config){

	$config = array(
		"sqlhost" => "localhost",
		"sqluser" => "root",
		"sqlpass" => "root",
		"database" => "carsinbikelanes",
		"use_leaflet_provider" => "TRUE",
		"leaflet_provider" => "OpenStreetMap",
		"map_url" => 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		"max_view" => 50,
		"north_bounds" => 40.9168,
		"south_bounds" => 40.490617,
		"east_bounds" => -73.6619,
		"west_bounds" => -74.2655,
		"center_lat" => 40.711,
		"center_long" => -74.055,
		"site_name" => "CARSINBIKELANES",
		"about_text" => "This text can be edited on the settings page." );
		
	if (file_exists('config.php')){
		include 'config.php';
	}
	
	$new_config = array_merge($config, $new_config);
	$config_file = fopen(dirname(__FILE__) . "/config.php", "w")
		or die("PHP Error: Issues creating config file. Are permissions set correctly?");
	$text = 
	"<?php\n\n" .
	"//----------------------------------------------//\n" .
	"// CONFIGURATION\n" .
	"//----------------------------------------------//\n\n" .
	"\$config = array(\n" .
	"'sqlhost' => '" . $new_config['sqlhost'] . "',\n" .
	"'sqluser' => '" . $new_config['sqluser'] . "',\n" .
	"'sqlpass' => '" . $new_config['sqlpass'] . "',\n" .
	"'database' => '" . $new_config['database'] . "',\n" .
	"'use_leaflet_provider' => " . $new_config['use_leaflet_provider'] . ",\n" .
	"'leaflet_provider' => '" . $new_config['leaflet_provider'] . "',\n" .
	"'map_url' => '" . $new_config['map_url'] . "',\n" .
	"'max_view' => " . $new_config['max_view'] . ",\n" .
	"'north_bounds' => " . $new_config['north_bounds'] . ",\n" .
	"'south_bounds' => " . $new_config['south_bounds'] . ",\n" .
	"'east_bounds' => " . $new_config['east_bounds'] . ",\n" .
	"'west_bounds' => " . $new_config['west_bounds'] . ",\n" .
	"'center_lat' => " . $new_config['center_lat'] . ",\n" .
	"'center_long' => " . $new_config['center_long'] . ",\n" .
	"'site_name' => '" . $new_config['site_name'] . "',\n" .
	"'about_text' => '" . addslashes(stripslashes($new_config['about_text'])) . "'\n" .
	");\n\n" .
	"\$connection = mysqli_connect(\$config['sqlhost'],\$config['sqluser'],\$config['sqlpass'],\$config['database']);\n\n" .
	"?>";
	fwrite($config_file, $text);
	fclose($config_file);
}

?>