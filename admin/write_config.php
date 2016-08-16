<?php

function write_config($new_config){

	$config = array(
		"sqlhost" => "localhost",
		"sqluser" => "root",
		"sqlpass" => "root",
		"database" => "carsinbikelanes",
		"api_key" => "00000",
		"max_view" => 50,
		"north_bounds" => 40.490617,
		"south_bounds" => 40.9168,
		"east_bounds" => -73.6619,
		"west_bounds" => -74.2655,
		"center_lat" => 40.711,
		"center_long" => -74.055,
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
	"'api_key' => '" . $new_config['api_key'] . "',\n" .
	"'max_view' => " . $new_config['max_view'] . ",\n" .
	"'north_bounds' => " . $new_config['north_bounds'] . ",\n" .
	"'south_bounds' => " . $new_config['south_bounds'] . ",\n" .
	"'east_bounds' => " . $new_config['east_bounds'] . ",\n" .
	"'west_bounds' => " . $new_config['west_bounds'] . ",\n" .
	"'center_lat' => " . $new_config['center_lat'] . ",\n" .
	"'center_long' => " . $new_config['center_long'] . ",\n" .
	"'about_text' => '" . $new_config['about_text'] . "'\n" .
	");\n\n" .
	"\$connection = mysqli_connect(\$config['sqlhost'],\$config['sqluser'],\$config['sqlpass'],\$config['database']);\n\n" .
	"?>";
	fwrite($config_file, $text);
	fclose($config_file);
}

?>