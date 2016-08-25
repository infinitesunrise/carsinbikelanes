<?php

function config_write($new_config){

	$config = array(
		"sqlhost" => "localhost",
		"sqluser" => "root",
		"sqlpass" => "root",
		"database" => "carsinbikelanes",
		"use_providers_plugin" => 1,
		"leaflet_provider" => "OpenStreetMap",
		"map_url" => 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
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
		"site_name" => "CARSINBIKELANES",
		"about_text" => '&lt;h3&gt;About&lt;/h3&gt;
&lt;p&gt;CIBL is a browsable geographic database for documenting crowdsourced traffic violation reports. Designed originally to publicly track illegal automotive encroachment into New York City bike lanes, this web app can be easily adapted for simple crowdsourced documentation of any geo-located event, particularly any sort of observable traffic violation.&lt;/p&gt;
&lt;p&gt;In a LAMP environement CIBL will deploy itself out of the box via a simple setup wizard. Support for numerous free maps is provided by &lt;a href=&quot;https://github.com/leaflet-extras/leaflet-providers&quot;&gt;leaflet-providers&lt;/a&gt; and Google Maps support via &lt;a href=&quot;https://github.com/shramov/leaflet-plugins&quot;&gt;leaflet-plugins&lt;/a&gt;. CIBL records the time, date, cross streets, GPS coordinates, user description and image of each record. GPS coordinates and date/time are pulled from image exif data if available via the included exif.js library. Administrative credentials are protected with PHPass. CIBL\'s mobile view is designed for quick and easy capture and upload of records on the go. A submissions queue allows administrative users to accept or deny pending submissions.&lt;/p&gt;
&lt;p&gt;CIBL was designed by a cyclist, inspired by the need for cycling advocacy in a city where law enforcement sentiment toward biking reads as apathetic, dismissive, and harmful. With enough public interest an active CIBL database has the potential to change hearts and minds, and expose endemic traffic and safety issues. Adapt it for your city however you\'d like. Better yet, invite your local law enforcement agency to be involved!&lt;/p&gt;' );
		
	if (file_exists('../config/config.php')){
		include '../config/config.php';
	}
	
	$new_config = array_merge($config, $new_config);
	$config_file = fopen(dirname(__FILE__) . "/../config/config.php", "w")
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
	"'site_name' => '" . $new_config['site_name'] . "',\n" .
	"'about_text' => '" . addslashes(stripslashes($new_config['about_text'])) . "'\n" .
	");\n\n" .
	"\$connection = mysqli_connect(\$config['sqlhost'],\$config['sqluser'],\$config['sqlpass'],\$config['database']);\n\n" .
	"?>";
	fwrite($config_file, $text);
	fclose($config_file);
}

?>