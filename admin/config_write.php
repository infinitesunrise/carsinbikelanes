<?php

function config_write($new_config){

	$config = array(
		"sqlhost" => "localhost",
		"sqluser" => "root",
		"sqlpass" => "root",
		"database" => "carsinbikelanes",
		"use_providers_plugin" => 1,
		"leaflet_provider" => "OpenStreetMap",
		"map_url" => '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
		"use_google" => 0,
		"google_api_key" => "",
		"google_extra_layer" => "NONE",
		"use_bing" => 0,
		"bing_api_key" => "",
		"bing_imagery" => 'Road',
		"max_view" => 50,
		"north_bounds" => 40.9168,
		"south_bounds" => 40.490617,
		"east_bounds" => -73.6619,
		"west_bounds" => -74.2655,
		"center_lat" => 40.711,
		"center_long" => -74.055,
		"mobile_center_lat" => 40.65,
		"mobile_center_long" => -73.9637,
		"site_name" => "CARSINBIKELANES",
		"comments" => "",
		"disqus" => "",
		"openalpr_api_key" => "",
		"openalpr_countries" => 'array("us")',
		"about_text" => '&lt;h3&gt;About CARSINBIKELANES&lt;/h3&gt;
&lt;p&gt;CIBL is a browsable geographic database for crowd-sourcing traffic violation reports. Originally designed to publicly track illegal automotive encroachment into New York City bike lanes at carsinbikelanes.nyc, CIBL can be adapted for to document any sort of observable traffic violations within a defined geographic area. CIBL records the time, date, cross streets, GPS coordinates, user description and image of each record submitted. CIBL\'s setup wizard should be able to self-deploy in a LAMP environment upon navigating to /index.php in a web browser.&lt;/p&gt;' );

	if (file_exists(__DIR__ . '/config_pointer.php')){
		include '' . __DIR__ . '/config_pointer.php';
	}
	else{
		return_error('There was a problem loading the configuration file. We are in ' . __DIR__);
	}

	$new_config = array_merge($config, $new_config);
	$config_file = fopen($config_location, "w")
		or return_error("PHP error. Issues creating config file. Are permissions set correctly?");
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
	"'use_providers_plugin' => " . $new_config['use_providers_plugin'] . ",\n" .
	"'leaflet_provider' => '" . $new_config['leaflet_provider'] . "',\n" .
	"'map_url' => '" . $new_config['map_url'] . "',\n" .
	"'use_google' => " . $new_config['use_google'] . ",\n" .
	"'google_api_key' => '" . $new_config['google_api_key'] . "',\n" .
	"'google_extra_layer' => '" . $new_config['google_extra_layer'] . "',\n" .
	"'use_bing' => " . $new_config['use_bing'] . ",\n" .
	"'bing_api_key' => '" . $new_config['bing_api_key'] . "',\n" .
	"'bing_imagery' => '" . $new_config['bing_imagery'] . "',\n" .
	"'max_view' => " . $new_config['max_view'] . ",\n" .
	"'north_bounds' => " . $new_config['north_bounds'] . ",\n" .
	"'south_bounds' => " . $new_config['south_bounds'] . ",\n" .
	"'east_bounds' => " . $new_config['east_bounds'] . ",\n" .
	"'west_bounds' => " . $new_config['west_bounds'] . ",\n" .
	"'center_lat' => " . $new_config['center_lat'] . ",\n" .
	"'center_long' => " . $new_config['center_long'] . ",\n" .
	"'mobile_center_lat' => " . $new_config['mobile_center_lat'] . ",\n" .
	"'mobile_center_long' => " . $new_config['mobile_center_long'] . ",\n" .
	"'site_name' => '" . $new_config['site_name'] . "',\n" .
	"'comments' => '" . $new_config['comments'] . "',\n" .
	"'disqus' => '" . $new_config['disqus'] . "',\n" .
	"'openalpr_api_key' => '" . $new_config['openalpr_api_key'] . "',\n" .
	"'openalpr_countries' => " . $new_config['openalpr_countries'] . ",\n" .
	"'about_text' => '" . addslashes(stripslashes($new_config['about_text'])) . "'\n" .
	");\n\n" .
	"\$connection = mysqli_connect(\$config['sqlhost'],\$config['sqluser'],\$config['sqlpass'],\$config['database']);\n\n" .
	"?>";
	fwrite($config_file, $text);
	fclose($config_file);
}

?>
