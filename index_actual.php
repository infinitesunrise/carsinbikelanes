<?php
include ('admin/config_pointer.php');
include ('mobile_detect.php');
?>

<html>
<head>

<?php 
if ($mobile){ echo '<meta charset="UTF-8" name="viewport" content="user-scalable=0"/>'; }
else{ echo '<meta charset="UTF-8"/>'; }
?>

<!-- local stylesheets -->
<?php
if ($mobile){ echo '<link rel="stylesheet" type="text/css" href="css/mobile_style.css" />'; }
else{ echo '<link rel="stylesheet" type="text/css" href="css/style.css" />'; }
?>
<link rel="stylesheet" type="text/css" href="css/plates.css" charset="utf-8" />

<!-- jquery -->
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>

<!-- jquery datetimepicker plugin by Valeriy (https://github.com/xdan) -->
<script src="scripts/jquery.datetimepicker.js"></script>
<link rel="stylesheet" type="text/css" href="css/jquery.datetimepicker.css"/ >

<!-- exif library plugin by Jacob Seidelin (https://github.com/jseidelin) -->
<script src="scripts/exif.js"></script>

<?php
if (!$config['use_mapboxgljs']){
	echo "<!-- leaflet -->\n";
	echo "<script src='//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.js'></script>\n";
	echo "<link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.3/leaflet.css' />\n";
}
?>

<!-- mapbox -->
<?php 
if (!$config['use_mapboxgljs']){
	echo "<script src='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.js'></script>\n";
	echo "<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.4/mapbox.css' rel='stylesheet' />\n";
}
if ($config['use_mapboxgljs']){
	echo "<script src='https://api.mapbox.com/mapbox-gl-js/v0.26.0/mapbox-gl.js'></script>\n";
	echo "<link href='https://api.mapbox.com/mapbox-gl-js/v0.26.0/mapbox-gl.css' rel='stylesheet' />\n";
}
?>

<!-- google fonts -->
<link href='//fonts.googleapis.com/css?family=Oswald:400,700|Francois+One' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Alfa+Slab+One' rel='stylesheet' type='text/css'>

<?php
if ($config['disqus']){
	echo '<script id="dsq-count-scr" src="//' . $config['disqus'] . '.disqus.com/count.js" async></script>';
}
?>

<!-- license plate font by Dave Hansen -->
<link href='css/license-plate-font.css' rel='stylesheet' type='text/css'>

<?php 
if (!$config['use_mapboxgljs']){
	echo "<!-- leaflet-providers by leaflet-extras (https://github.com/leaflet-extras) -->\n";
	echo "<script src='scripts/leaflet-providers.js'></script>\n";
	echo "\n";
	echo "<!-- Google Javascript API with current key -->\n";
	echo "<script id='google_api_link' src='//maps.google.com/maps/api/js?key=" . $config['google_api_key'] . "'></script>\n";
	echo "\n";
	echo "<!-- leaflet-plugins by Pavel Shramov (https://github.com/shramov/leaflet-plugins) -->\n";
	echo "<script id='leaflet_plugins' src='../scripts/leaflet-plugins-master/layer/tile/Google.js'></script>\n";
	echo "<script id='leaflet_plugins' src='../scripts/leaflet-plugins-master/layer/tile/Bing.js'></script>\n";
}
?>

<script type="text/javascript">
//load in config
config = {
	mobile: '<?php echo $mobile; ?>',
	site_name: '<?php echo $config['site_name']; ?>',
	disqus: '<?php echo $config['disqus']; ?>',
	comments: '<?php echo $config['comments']; ?>',
	max_default: <?php echo $config['max_view']; ?>,
	max_view: <?php if (isset($_GET['max'])){ echo $_GET['max']; } else { echo $config['max_view']; } ?>,
	id: <?php if (isset($_GET['id'])){ echo $_GET['id']; } else { echo 'null'; } ?>,
	plate: <?php if (isset($_GET['plate'])){ echo '"' . $_GET['plate'] . '"'; } else { echo '""'; } ?>,
	zoom: <?php if (isset($_GET['zoom'])){ echo $_GET['zoom']; } else { echo '12'; } ?>,
	openalpr_api_key: '<?php echo $config['openalpr_api_key']; ?>',
	north_bounds: <?php echo $config['north_bounds']; ?>,
	south_bounds: <?php echo $config['south_bounds']; ?>,
	east_bounds: <?php echo $config['east_bounds']; ?>,
	west_bounds: <?php echo $config['west_bounds']; ?>,
	center_lat: <?php if (isset($_GET['center'])){ echo explode(',',$_GET['center'])[0]; } 
					  else if($mobile == true){ echo $config['mobile_center_lat']; } 
					  else { echo $config['center_lat']; } ?>,
	center_long: <?php if (isset($_GET['center'])){ echo explode(',',$_GET['center'])[1]; } 
					   else if($mobile == true){ echo $config['mobile_center_long']; } 
					   else { echo $config['center_long']; } ?>,
	use_providers_plugin: <?php echo $config['use_providers_plugin']; ?>,
	leaflet_provider: '<?php echo $config['leaflet_provider']; ?>',
	map_url: '<?php echo $config['map_url']; ?>',
	mobile_map_url: '<?php echo $config['mobile_map_url']; ?>',
	use_mapboxgljs: <?php echo $config['use_mapboxgljs']; ?>,
	mapbox_style_url: '<?php echo $config['mapbox_style_url']; ?>',
	mapbox_mobile_style_url: '<?php echo $config['mapbox_mobile_style_url']; ?>',
	mapbox_key: '<?php echo $config['mapbox_key']; ?>',
	use_google: <?php echo $config['use_google']; ?>,
	google_api_key: '<?php echo $config['google_api_key']; ?>',
	google_extra_layer: '<?php echo $config['google_extra_layer']; ?>',
	use_bing: <?php echo $config['use_bing']; ?>,
	bing_api_key: '<?php echo $config['bing_api_key']; ?>',
	bing_imagery: '<?php echo $config['bing_imagery']; ?>'
}
//used for management of window traffic
windows = {
	single_view: false,
	about_view: false,
	submit_view: false,
	entry_view: false,
	results_view: false,
	submit_link_clicked: true,
	about_link_clicked: true,
	stop_load_entries: false,
	auto_view_change: false,
	nav_loaded: false,
	upload_view_loaded: false
}
//entry map icon
if (!config.use_mapboxgljs){
	xIcon = L.icon({
		iconUrl: 'css/x.svg',
		shadowUrl: 'css/x_shadow.svg',
		iconSize:     [20, 20], // size of the icon
		shadowSize:   [20, 20], // size of the shadow
		iconAnchor:   [10, 10], // point of the icon which will correspond to marker's location
		shadowAnchor: [7, 7],  	// the same for the shadow
		popupAnchor:  [10, 10] 	// point from which the popup should open relative to the iconAnchor
	});
	marker = new L.marker({icon: xIcon}); 	//upload map marker
}
//tracking of upload form auto-complete process to guide automatic scrolling through form
auto_complete = {
	plate: false,
	exif: false,
	streets: false
}
current_entries = {}; 					//collection of currently loaded database entries
var markers, body_map, submit_map;		//markers layer for leaflet, empty vars for body and upload map

$(document).ready(function() {
	//set up body and submit maps ASAP, deal with navigating all the map config possibilities
	initialize_body_map();
	
	//set up initial window states
	if (!config.mobile){ $(".right_menu").show(); }
	$("#about_view").hide();
	$("#upload_view").hide();
	$("#results_view").hide();
	$('#entry_view').hide();
	$("#single_view").hide();
	$('#entry_template').hide();
	
	
	if (config.id > 0){ setTimeout(function(){ zoom_to_entry(config.id); }, 250); }
	else if (config.plate) { setTimeout(function(){ plate_search(config.plate); }, 250); }
	else { load_entries(); }
	
	//upload form loading actions
	$("#submit_link").click( function() { 
		open_window('submit_view');
		initialize_upload_view();
	});
	
	//bind all initial change and click events
	$("#about_link").click( function() {open_window('about_view')} );
	$("#feedback").click( function(e) { showEmail(e) } );
	$("#dismiss_success_dialog").click ( function() { $("#success_dialog").hide() } );
	
	//disqus comments initialization
	if (config.disqus){
		(function() {
			var d = document, s = d.createElement('script');
			s.src = '//' + config.disqus + '.disqus.com/embed.js';
			s.setAttribute('data-timestamp', +new Date());
			(d.head || d.body).appendChild(s);
		})();
	}
});

//There are two totally gnarly open_window functions depending on desktop or mobile,
//Still needs to be integrated into one and made not so... overcomplicated.
//I apologize to any weary eyes for the ghasty state of the three functions below :D
function open_window(window){
	if (config.mobile){ open_window_mobile(window); }
	else { open_window_desktop(window); }
	/*console.log(
		'entry_view: ' + windows.entry_view + '\n' + 
		'submit_view: ' + windows.submit_view + '\n' + 
		'about_view: ' + windows.about_view + '\n' + 
		'single_view: ' + windows.single_view + '\n' + 
		'results_view: ' + windows.results_view
	);*/
}

function open_window_desktop(window) {
	if (windows.results_view == true && window != 'results_view'){
		$("#results_view").animate({opacity: 'toggle', right: '-565px'});
	}
	
	if (windows.single_view == true) {
		$('#single_view').animate({opacity: 'toggle', left: '-865px'});
	}
	if (windows.about_view == true) {
		$('#about_view').animate({opacity: 'toggle', right: '-605px'});
		$('.right_menu').delay(300).animate({opacity: 'toggle'});
	}
	if (windows.submit_view == true) {
		$('#upload_view').animate({opacity: 'toggle', right: '-565px'});
		$('.right_menu').delay(300).animate({opacity: 'toggle'});
	}
	windows.single_view = false; windows.about_view = false; windows.submit_view = false; windows.results_view = false;
	
	if (window == 'results_view'){
		$("#results_view").animate({opacity: 'toggle', right: '0px'});
		windows.results_view = true;
	}

	if (windows.entry_view == true && window == 'none') {
		$('#entry_view').animate({opacity: 'toggle', left: '-565px'});
		windows.entry_view = false;
	}
	if (window == 'single_view' && windows.single_view == false){
		$('#single_view').animate({opacity: 'toggle', left: '0px'});
		windows.single_view = true;
	}
	if (window == 'about_view' && windows.about_view == false){
		$('#about_view').animate({opacity: 'toggle', right: '0px'});
		$('.right_menu').hide();
		windows.about_view = true;
	}
	if (window == 'submit_view' && windows.submit_view == false){
		$('#upload_view').animate({opacity: 'toggle', right: '0px'});
		$('.right_menu').hide();
		windows.submit_view = true;
	}
	if (window == 'entry_view' && windows.entry_view == false){
		$('#entry_view').animate({opacity: 'toggle', left: '0px'});
		windows.entry_view = true;
	}
}

function open_window_mobile(window_name) {	
	if (window_name == 'submit_view'){ change_nav('submit'); }
	else if (window_name == 'about_view'){ change_nav('about'); }
	else { change_nav('close'); }

	if (windows.entry_view == true) {
		if (window_name != 'entry_view'){ 
			$('#entry_view').animate({opacity: 'toggle', bottom: '-50vh'}); 
			windows.entry_view = false;
		}
	}
	if (windows.single_view == true) {
		$('#single_view').animate({opacity: 'toggle', top: '100vh'});
	}
	if (windows.about_view == true) {
		$('#about_view').animate({opacity: 'toggle', top: '100vh'});
		if (window_name == 'about_view'){
			windows.about_view = false;
			open_window('entry_view');
			return;
		}
	}
	if (windows.submit_view == true) {
		$('#submit_view').animate({ top: '100vh'});
		windows.stop_load_entries = false;
		if (window_name == 'submit_view'){
			windows.submit_view = false;
			open_window('entry_view');
			return;
		}
	}
	if (windows.results_view == true) {
		$('#results_view').animate({opacity: 'toggle', top: '100vh'});
		if (window_name == 'results_view'){ open_window('entry_view'); }
	}
	
	if (windows.entry_view == true && window_name == 'none') {
		$('#entry_view').animate({opacity: 'toggle', bottom: '-50vh'});
		windows.entry_view = false;
	}

	if (window_name == 'entry_view' && windows.entry_view == false){
		$('#entry_view').animate({opacity: 'toggle', bottom: '0vh'});
		windows.entry_view = true;
		windows.single_view = false; windows.about_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'single_view' && windows.single_view == false){
		var new_position = $(window).innerHeight() - $('#single_view').outerHeight();
		$('#single_view').animate({opacity: 'toggle', top: new_position});
		windows.single_view = true;
		windows.entry_view = false; windows.about_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'about_view' && windows.about_view == false){
		$('#about_view').animate({opacity: 'toggle', top: '7vh'});
		windows.about_view = true;
		windows.entry_view = false; windows.single_view = false; windows.submit_view = false; windows.results_view = false;
	}
	if (window_name == 'submit_view' && windows.submit_view == false){
		$('#submit_view').animate({ top: '7vh'});
		windows.stop_load_entries = true;
		windows.submit_view = true;
		windows.entry_view = false; windows.single_view = false; windows.about_view = false; windows.results_view = false;
	}
	if (window_name == 'results_view' && windows.results_view == false){;
		$('#results_view').animate({ opacity: 'toggle', top: '7vh' });
		windows.results_view = true;
		windows.entry_view = false; windows.single_view = false; windows.about_view = false; windows.submit_view = false;
	}
}

//Switches contents of links at top of mobile page depending on page context
//(So for example, "upload" isn't an option when you're already in the upload form)
function change_nav(operation){
	if (config.mobile){
		switch (operation){
			case 'close':
				if (windows.submit_link_clicked){
					flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
				}
				if (windows.about_link_clicked){
					flip($('#about_link'), config.site_name, 'about_link_clicked', false);
				}
				break;
			case 'about':
				if (windows.about_link_clicked){
					flip($('#about_link'), config.site_name, 'about_link_clicked', false);
				}
				else {
					flip($('#about_link'), 'BACK TO MAP', 'about_link_clicked', true);
				}
				if (windows.submit_link_clicked){
					flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
				}
				break;
			case 'submit':
				if (windows.submit_link_clicked){
					flip($('#submit_link'), 'SUBMIT', 'submit_link_clicked', false);
				}
				else {
					flip($('#submit_link'), 'MAP', 'submit_link_clicked', true);
				}
				if (windows.about_link_clicked){
					flip($('#about_link'), config.site_name, 'about_link_clicked', false);
				}
				break;
		}
	}
}

//assist function for mobile navigation links
function flip(element, content, key, value){
	if (config.mobile){
		element.animate({'top': '-5vh'}, function(){
			element.html("<span class='navspan'>" + content + "</span>");
		})
		.animate({'top': '0vh'});
		if (key == 'submit_link_clicked'){windows.submit_link_clicked = value; }
		if (key == 'about_link_clicked'){windows.about_link_clicked = value; }
	}
}

//mobile upload form auto-scroll
function auto_scroll(autofilled){
	if (config.mobile){
		switch(autofilled){
			case 'plate':
				auto_complete.plate = true;
				break;
			case 'exif':
				auto_complete.exif = true;
				break;
			case 'streets':
				auto_complete.streets = true;
				break;
			case 'reset':
				auto_complete.plate = false;
				auto_complete.exif = false;
				auto_complete.streets = false;
				break;
		}
		if (auto_complete.plate == true &&
			auto_complete.exif == true &&
			auto_complete.streets == true){
			var offset = -100;
			$('#submit_view').animate({
				scrollTop: $("#description-title").offset().top + offset
			}, 2000);
		}
	}
}

//The meat and potatoes of CIBL's front page - handles dynamic loading and unloading of data onto the page
function load_entries(plate) {
	
	if (windows.stop_load_entries == false) {
		//block any other calls to load_entries, engage loading animation
		windows.stop_load_entries = true;
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		
		//load up dat query
		var query = {};
		if (plate){ query = { plate: plate }; }
		else {
			var west = body_map.getBounds().getWest();
			var east = body_map.getBounds().getEast();
			var south = body_map.getBounds().getSouth();
			var north = body_map.getBounds().getNorth();
			var box_array = [north, south, east, west];
			query = {
				box: box_array,
				max: config.max_view
			}
		}
		
		var url_string = document.location.origin + '/api/search';
		
		//fire away
		$.ajax({
			url: url_string,
			type: 'GET',
			dataType: 'json',
			data: query,
			
			success: function(a){
				var response = a;
				//console.log(response);
				
				if (plate){
					//body_map.fitBounds([
					//	[config.south_bounds, config.west_bounds-0.05],
					//	[config.north_bounds, config.east_bounds-0.05]
					//]);
					body_map.setView([config.center_lat, config.center_long], config.zoom);
					windows.auto_view_change = true;
				}
				
				//update page history dependent on search type
				if (box_array){
					//round gps fields to 3 decimal places
					var center = [Math.round(body_map.getCenter().lat * 1000) / 1000,
								Math.round(body_map.getCenter().lng * 1000) / 1000];
					var max = (config.max_default == config.max_view) ? '' : '&max=' + config.max_view;
					var zoom = (config.use_mapboxgljs) ? '&zoom=' + Math.round(body_map.getZoom() * 1000) / 1000 : '&zoom=' + body_map.getZoom();
					history.pushState(
						{box: box_array},
						config.site_name,
						'/index.php?center=' + center + zoom + max
					);
				}
				else if (plate){
					history.pushState(
						{plate: plate},
						config.site_name,
						'/index.php?plate=' + plate
					);
				}
				
				//build temporary reference array of current entries sorted by ID descending to determine where to load any new entries
				var current_entries_sorted = [];
				for (var i in current_entries) {
					if (current_entries.hasOwnProperty(i)) {
						current_entries_sorted.push(current_entries[i]['id']);
					}
				}
				current_entries_sorted.sort( function(a,b){ return a - b; } ).reverse();
				
				//load the entry view nav if it's not already (desktop view only)
				if (!config.mobile){
					if(!config.nav_loaded){
						$('#column_entry_nav_template').clone()
						.appendTo('#entry_list_content')
						.attr('id', 'column_entry_nav');
						$('#column_entry_nav').attr('class','column_entry');
						$('#column_entry_nav').css('display','flex');
						$('#column_entry_nav').find('#max_view').val(config.max_view);
						$('#column_entry_nav').find('#column_entry_nav_message').html(
							response.entries.length + ' most recent entires within view returned. '
						);
						$('#column_entry_nav').find('#max_view').on('change', function(){
							config.max_view = $(this).val();
							load_entries();
						});
						config.nav_loaded = true;
					}
					else {
						$('#column_entry_nav').find('#column_entry_nav_message').html(
							response.entries.length + ' most recent entires within view returned. '
						);
					}
				}
				
				//start loading entries into entry view
				var response_ids = [];
				for (var i = 0; i < response.entries.length; i++){
					var entry = response.entries[i];
					response_ids.push(entry.id);
					if (!current_entries.hasOwnProperty(entry.id)){
						
						//copy the column entry template to appropriate place in entry list
						var insert = -1;
						for (var j = 0; j < current_entries_sorted.length; j++){
							if (entry.id / 1 > current_entries_sorted[j] / 1){
								$('#column_entry_template').clone()
								.insertBefore('#column_entry' + current_entries_sorted[j])
								.attr('id', 'column_entry' + entry.id);
								insert = j;
								break;
							}
						}
						if (insert < 0){
							insert = current_entries_sorted.length;
							$('#column_entry_template').clone()
							.appendTo('#entry_list_content')
							.attr('id', 'column_entry' + entry.id); 
						}
						
						//update temporary reference index of loaded entries
						current_entries_sorted.splice(insert, 0, entry.id);
						
						//load data into the new clone
						var new_entry = $('#column_entry' + entry.id);
						new_entry.css('display', 'flex');
						new_entry.attr('class', 'column_entry');
						new_entry.attr('onClick', 'zoom_to_entry(' + entry.id + ')');
						new_entry.find('.thumbnail').attr( 'src', location.protocol + '//' + entry.thumb_url );
						new_entry.find('#id_text').html('#' + entry.id);
						new_entry.find('.plate_link').attr('id', 'plate' + entry.id);
						new_entry.find('.plate_link').attr('onClick', 'javascript:plate_search(\'' + entry.plate + '\')');
						switch (entry.state){
							case 'NYPD':
								var first = ''; var second = '';
								for (var j = 0; j < entry.plate.length; j++){
									if (j <  entry.plate.length - 2){ first += entry.plate[j]; }
									else { second += entry.plate[j]; }
								}
								var plate_string = first + '<span class="NYPDsuffix">' + second + '</span>';
								new_entry.find('#plate_text').html(plate_string).attr('class', 'plate NYPD');
								break;
							default:
								new_entry.find('#plate_text').html(entry.plate).attr('class', 'plate ' + entry.state);
								break;
						}
						var date = new Date(entry.date_occurrence);
						var date_string  = (date.getMonth()+1) + '/' + date.getDate() + '/' + date.getFullYear() + ' ';
						if (date.getHours() < 12){ date_string += (date.getHours()+1) + ':'; }
						else { date_string += (date.getHours()-11) + ':'; }
						if (date.getMinutes() < 10){ date_string += '0' + date.getMinutes(); }
						else { date_string += date.getMinutes(); }
						if (date.getHours() < 12){ date_string += 'AM'; }
						else { date_string += 'PM' }
						new_entry.find('#date_text').html('DATE: ' + date_string);
						var streets = entry.street1;
						if (entry.street2){ streets += ' & ' + entry.street2 }
						streets = streets.toUpperCase();
						new_entry.find('#streets_text').html('STREETS: ' + streets);
						new_entry.find('#gps_text').html('GPS: ' + entry.gps_latitude + ' / ' + entry.gps_longitude);
						if (entry.description){ new_entry.find('#description_text').html(entry.description); }
						else { new_entry.find('#description_text_label').remove(); }
						new_entry.find('.disqus-comment-count').attr('data-disqus-url', 'http://carsinbikelanes.nyc/index.php?single_view=' + entry.id);
						//fade in completed column entry
						new_entry.hide();
						new_entry.fadeIn();
						
						//load new map marker
						if (!config.use_mapboxgljs){ //for leaflet
							var entry_marker = new L.marker(
								[entry.gps_latitude, entry.gps_longitude],
								{
									icon: xIcon,
									title: '#' + entry.id + ': ' + entry.plate,
									riseOnHover: true,
									cibl_id: entry.id
								}
							).on('click', function(e) { zoom_to_entry(this['options']['cibl_id']); });
							markers.addLayer(entry_marker);
						}
						else { //for mapboxgljs
							var entry_marker = document.createElement('div');
							entry_marker.className = 'map_marker';
							entry_marker.id = entry.id;
							$(entry_marker).on('click', function() { zoom_to_entry($(this).attr('id')); });
							new mapboxgl.Marker(entry_marker, {offset: [-12, -12]})
								.setLngLat({lng: entry.gps_longitude, lat: entry.gps_latitude})
								.addTo(body_map);
						}

						//and new entry to global array of current entries
						current_entries[entry.id] = { 
							id: entry.id,
							column_entry: new_entry,
							marker: entry_marker
						};
					}
				}
				
				//"No results found here" column entry if response array is empty
				$('#column_entry0').remove();
				if (response.entries.length == 0){
					$('#column_entry_template').clone()
					.appendTo('#entry_list_content')
					.attr('id', 'column_entry0');
					var new_entry = $('#column_entry0');
					new_entry.attr('class', 'column_entry');
					new_entry.css('display', 'flex');
					new_entry.html('<h3>No records found here.</h3>');
					new_entry.css('min-height', '150px');
				}
				
				//Clean out non-visible entries from page and memory
				for (var i in current_entries) {
					if (current_entries.hasOwnProperty(i)) {
						if ($.inArray(current_entries[i]['id'], response_ids) < 0){
							//for leaflet
							if (!config.use_mapboxgljs){ markers.removeLayer(current_entries[i]['marker']); }
							//for mapboxgljs
							else { $(current_entries[i]['marker']).remove(); }
							$(current_entries[i]['column_entry']).fadeOut(300, function(){ $(this).remove(); });
							delete current_entries[i];
						}
					}
				}
				
				//unblock other calls to load entries, disengage loading animation, other post-load cleanups
				open_window('entry_view');
				if(config.disqus){ DISQUSWIDGETS.getCount({reset: true}); }
				$('#entry_list_content').css('overflow-y','scroll'); //fixes bug where firefox doesn't scroll on first list loaded
				setTimeout(function(){ resize_entry_view(); }, 500);
				windows.stop_load_entries = false;
				$('#loading').css('background', 'none');
			},
			
			//placeholder, should probably add to this
			error: function(a){
				var response = a;
				console.log(response);	
			}
		});
	}
}

function zoom_to_entry(id) {	
	if (windows.stop_load_entries == false) {
		windows.stop_load_entries = true;
		$('#loading').css('background', 'url(\'css/loader.svg\') 100% no-repeat');
		
		var url_string = document.location.origin + '/api/search';
		
		$.ajax({
			url: url_string,
			type: 'GET',
			dataType: 'json',
			data:{
				id: id
			},
			
			success: function(a){
				var entry = a.entries[0];
				//console.log(entry);

				history.pushState(
					{id: entry.id},
					config.site_name + ': Entry #' + entry.id,
					'/index.php?id=' + entry.id
				);
				open_window('none');
				
				if (!config.use_mapboxgljs){ //for leaflet
					if (config.mobile){ body_map.panTo([entry.gps_latitude-.002,entry.gps_longitude]).setZoom(18); }
					else { body_map.setView([entry.gps_latitude,entry.gps_longitude-.005], 17); }
					soloMarker = L.marker([entry.gps_latitude,entry.gps_longitude],
						{icon: xIcon, title: '#' + entry.id + ': ' + entry.plate})
						.addTo(body_map);
					markers.clearLayers();
					markers.addLayer(soloMarker);
				}
				else { //for mapboxgljs
					if (config.mobile){ 
						body_map.easeTo({
							center: [entry.gps_longitude, entry.gps_latitude-.001], 
							zoom: 18
						});
					}
					else { 
						body_map.easeTo({
							center: [entry.gps_longitude-.0025, entry.gps_latitude], 
							zoom: 17
						});
					}
					$('.map_marker').remove();
					var entry_marker = document.createElement('div');
					entry_marker.className = 'map_marker';
					entry_marker.id = entry.id;
					new mapboxgl.Marker(entry_marker, {offset: [-12, -12]})
						.setLngLat({lng: entry.gps_longitude, lat: entry.gps_latitude})
						.addTo(body_map);
				}
				
				//flush out current_entries
				for (var i in current_entries) {
					current_entries[i]['column_entry'].remove();
					delete current_entries[i];
				}
				
				//load entry data into single view
				$('#single_view').find('#fullsize')
				.one('load', function(){
					open_window('single_view');
				})
				.attr('src', location.protocol + '//' + entry.image_url);
				$('#single_view').find('#id_single').html('#' + entry.id);
				$('#single_view').find('#plate_single_link').attr('onClick', 'plate_search(\'' + entry.plate + '\')');
				switch (entry.state){
					case 'NYPD':
						var first = ''; var second = '';
						for (var i = 0; i < entry.plate.length; i++){
							if (i <  entry.plate.length - 2){ first += entry.plate[i]; }
							else { second += entry.plate[i]; }
						}
						var plate_string = first + '<span class="NYPDsuffix">' + second + '</span>';
						$('#single_view').find('#plate_single').html(plate_string).attr('class', 'plate NYPD');
						break;
					default:
						$('#single_view').find('#plate_single').html(entry.plate).attr('class', 'plate ' + entry.state);
						break;
				}
				$('#single_view').find('#date_single').html(entry.date_occurrence);
				$('#single_view').find('#date_single').html(entry.date_occurrence);
				var streets = entry.street1;
				if (entry.street2){ streets += ' & ' + entry.street2 }
				streets = streets.toUpperCase();
				$('#single_view').find('#streets_single').html(streets);
				$('#single_view').find('#gps_single').html(streets);
				if (!entry.description){ $('#single_view').find('#description_div_single').hide(); }
				else { $('#single_view').find('#description_div_single').show(); }
				if (entry.description){ $('#single_view').find('#description_single').html(entry.description); }
				else { $('#single_view').find('#description_single_label').remove(); }

				//open_window('single_view');
				setTimeout(function() { windows.stop_load_entries = false; }, 500);
				$('#loading').css('background', 'none');
				
				DISQUS.reset({
					reload: true,
					config: function () {
						var url = location.protocol + '//' + location.hostname + '/index.php?id=' + id;
						this.page.identifier = url;
						this.page.url =  url;
						this.page.title = config.site_name + ': Entry #' + id;
					}
				});
			},
			
			error: function(a){
				console.log(a);	
			}
		});
	}
}

function plate_search(plate) { load_entries(plate); }

function resize_entry_view(){
	if (config.mobile){
		var total_height = 0; var count = 0;
		$('.column_entry').map( function(){ 
			if ($(this).hasClass("single_view_column_entry") == false) {
				total_height += $(this).outerHeight() + 10;
				count++;
			}
		});
		if (count < 3){
			$('#entry_view').animate({ height: total_height, bottom: '0' });
		}
		else { 
			$('#entry_view').animate({ height: '33vh', bottom: '0vh' });
		}
	}
	else {
		total_height = -25;
		$('.column_entry').map( function(){ total_height += $(this).outerHeight() + 10; });
		if (total_height < document.body.clientHeight){ $('#entry_view').animate({ height: total_height }); }
		else { $('#entry_view').animate({ height: '96vh' }); }
	}
}

function limit_text() {
	var comments = document.getElementById("comments");
	if (comments.value.length > 200) {
		comments.value = comments.value.substring(0, 200);
	}
	else {
		var count = comments.value.length;
		document.getElementById("character_limit").innerHTML = 200 - count;
	}
}

function initialize_datetimepicker() {
	var date = new Date();
	$('#datetimepicker').datetimepicker({ format:'unixtime' });
	$('#datetimepicker').attr('unixtime', Date.parse(date)/1000 );
	$('#datetimepicker').val(unixtime_to_pretty($('#datetimepicker').attr('unixtime')));
	$('#datetimepicker').on('change', function() {
		$('#datetimepicker').attr('unixtime', $('#datetimepicker').val() );
		$('#datetimepicker').val(unixtime_to_pretty($('#datetimepicker').val()));
	});
}

function unixtime_to_pretty(unixtime){
	var date = new Date(unixtime * 1000);
	var date_string  = (date.getMonth()+1) + '/' + date.getDate() + '/' + date.getFullYear() + ' ';
	if (date.getHours() == 0){ date_string += '12:'; }
	else if (date.getHours() <= 12){ date_string += date.getHours() + ':'; }
	else { date_string += (date.getHours()-11) + ':'; }
	if (date.getMinutes() < 10){ date_string += '0' + date.getMinutes(); }
	else { date_string += date.getMinutes(); }
	if (date.getHours() < 12){ date_string += 'AM'; }
	else { date_string += 'PM' }
	return date_string;
}

function fill_plate_and_state(){
	var openalpr = config.openalpr_api_key;
	if (!openalpr) { return; }
	$('#plate').css('background', 'url(\'css/loader.svg\') 50% no-repeat');
	$('#plate').css('background-size', '50%');
	$('#plate').css('background-color', 'white');
	var url = 'https://api.openalpr.com/v1/recognize';
	var data = new FormData();
	data.append('secret_key', openalpr);
	data.append('tasks', ['plate']);
	data.append('image', $('#image_submission')[0].files[0]);
	data.append('country', 'us');
	var reply;

	function listener() {
		var reply = JSON.parse(this.responseText);
		var x1 = reply['plate']['img_width'] / 2;
		var y1 = reply['plate']['img_height'] / 2;
		var results = reply['plate']['results'];
		if (results.length > 0){
			var smallest_distance = (x1 >= y1) ? x1 : y1;
			var best = 0;
			$.each( results, function(i) {
				var x2 = (results[i]['coordinates'][0]['x'] +
						results[i]['coordinates'][1]['x'] +
						results[i]['coordinates'][2]['x'] +
						results[i]['coordinates'][3]['x']) / 4;
				var y2 = (results[i]['coordinates'][0]['y'] +
						results[i]['coordinates'][1]['y'] +
						results[i]['coordinates'][2]['y'] +
						results[i]['coordinates'][3]['y']) / 4;
				var x = x1 - x2;
				var y = y1 - y2;
				var distance = Math.sqrt(x*x + y*y);;
				if (distance < smallest_distance){
					best = i;
					smallest_distance = distance;
				}
			});
			$('#plate').css('background', 'white');
			$('#plate').val(reply['plate']['results'][best]['plate']);
			$('#state').val(reply['plate']['results'][best]['region'].toUpperCase());
			auto_scroll('plate');
		}
		else {
			auto_scroll('reset');
			$('#plate').css('background', 'white');
		}
	}
	var request = new XMLHttpRequest();
	request.addEventListener('load', listener);
	request.open('POST', url);
	request.send(data);
}

function fill_date_and_gps(e) {
	EXIF.getData(e.target.files[0], function() {
		//Auto-enter location data
		if(EXIF.getTag(this, "GPSLatitude")){
			var lat_deg = EXIF.getTag(this, "GPSLatitude")[0];
			var lat_min = EXIF.getTag(this, "GPSLatitude")[1];
			var lat_sec = EXIF.getTag(this, "GPSLatitude")[2];
			var lng_deg = EXIF.getTag(this, "GPSLongitude")[0];
			var lng_min = EXIF.getTag(this, "GPSLongitude")[1];
			var lng_sec = EXIF.getTag(this, "GPSLongitude")[2];
			var gps_lat = (lat_deg+(((lat_min*60)+lat_sec))/3600); //DMS to decimal
			var gps_lng = -(lng_deg+(((lng_min*60)+lng_sec))/3600); //DMS to decimal
			if (gps_lat != 0 && gps_lng != 0){
				document.getElementById("latitude").value = gps_lat;
				document.getElementById("longitude").value = gps_lng;
				//If OpenALPR active, auto-enter streets here by reverse geocoding gps coords
				fill_streets();
				
				if (config.use_mapboxgljs){
					$('#upload_marker').remove();
					$('#upload_marker').remove();
					marker = document.createElement('div');
					marker.className = 'map_marker';
					marker.id = 'upload_marker';
					new mapboxgl.Marker(marker, {offset: [-12, -12]})
					.setLngLat({lng: gps_lng, lat: gps_lat})
					.addTo(submit_map);
					submit_map.easeTo({ center: [gps_lng, gps_lat]});
				}
				else {
					submit_map.removeLayer(marker);
					marker = new L.marker([gps_lat, gps_lng], {icon: xIcon}).addTo(submit_map);
					submit_map.panTo([gps_lat, gps_lng]);
				}	
				
				var gps_text = "Latitude: " + gps_lat.toFixed(6) + " Longitude: " + gps_lng.toFixed(6);
				document.getElementById("gps_coords").innerHTML = gps_text;
				document.getElementById("map_prompt").innerHTML = "Location detected:";
				auto_scroll('exif');
			}
			else {
				document.getElementById("map_prompt").innerHTML = "Could not auto-detect location, please fill out manually!";
			}
		}
		//Auto-enter time and date
		if(EXIF.getTag(this, "DateTimeOriginal")){
			var capturetime = EXIF.getTag(this, "DateTimeOriginal");
			var isotime = capturetime.split(" ")[0].replace(/:/g,'-') + 'T' + capturetime.split(" ")[1];
			var unixtime = Date.parse(isotime)/1000;
			$('#datetimepicker').val(unixtime_to_pretty(unixtime));
		}
	});
}

function fill_streets(){
	var url = '//geocode.arcgis.com/arcgis/rest/services/World/GeocodeServer/reverseGeocode';
	var location = '{x:' + $('#longitude').val() + ',y:' + $('#latitude').val() + '}';
	var data = new FormData();
	data.append('location', location);
	data.append('distance', '150');
	data.append('returnIntersection', 'true');
	data.append('f', 'json');
	var reply;
	function listener() {
		var response = JSON.parse(this.responseText);
		var intersection = response['address']['Address'].split(" & ");
		$('#street1').val(intersection[0]);
		$('#street2').val(intersection[1]);
		auto_scroll('streets');
	}
	var request = new XMLHttpRequest();
	request.addEventListener('load', listener);
	request.open('POST', url);
	request.send(data);
}

function submit_form() {
	$('#upload_prompt').empty();
	$('#upload_button').css('background', 'url(\'css/loader.svg\') 50% no-repeat');
	$('#upload_button').css('background-size', '10%');
	$('#upload_button').css('background-color', 'lightgray');
	
	var formData = new FormData();
	formData.append( 'image', $('#image_submission')[0].files[0] );
	formData.append( 'plate', $('#plate').val() );
	formData.append( 'state', $('#state').val() );
	formData.append( 'date', $('#datetimepicker').attr('unixtime') /*$('#datetimepicker').val()*/ );
	formData.append( 'gps_latitude', $('#latitude').val() );
	formData.append( 'gps_longitude', $('#longitude').val() );
	formData.append( 'street1', $('#street1').val() );
	formData.append( 'street2', $('#street2').val() );
	formData.append( 'description', $('#comments').val() );
	
	var url_string = document.location.origin + '/api/upload';
	
	$.ajax({
		url: url_string,
		type: 'POST',
		data: formData,
		dataType: 'json',
		processData: false,
		contentType: false,
		mimeType: 'multipart/form-data',
		
		success: function (a) {
			console.log(a);
			var response = a;
			$('#results_header').html('Success!');
			$('#results_message').html(response['result']['success']);
			$('#results_details').html('<b>Upload details:</b><br/>'
										+ 'image:<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;name: ' + response['upload']['image']['name'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;type: ' + response['upload']['image']['type'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;error: ' + response['upload']['image']['error'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;size: ' + Math.round(response['upload']['image']['size'] / 1000) + ' kb<br/>'
										+ 'plate: ' + response['upload']['plate'] + '<br/>'
										+ 'state: ' + response['upload']['state'] + '<br/>'
										+ 'date: ' + response['upload']['date'] + '<br/>'
										+ 'gps: ' + response['upload']['gps_latitude'] + ' / ' + response['upload']['gps_longitude'] + '<br/>'
										+ 'street 1: ' + response['upload']['street1'] + '<br/>'
										+ 'street 2: ' + response['upload']['street2'] + '<br/>'
										+ 'description: ' + response['upload']['description']);
			$('#results_back').html('Submit Another');
			$('#upload_button').css('background', 'none');
			$('#upload_button').css('background-color', 'lightgray');
			$('#upload_prompt').append('UPLOAD!');
			//$("#results_view").animate({opacity: 'toggle', right: '0px'});
			open_window('results_view');
			$("#upload_form")[0].reset();
			if (config.use_mapboxgljs){ $('#upload_marker').remove(); }
			else { submit_map.removeLayer(marker); }
			var gps_text = "Latitude: ... Longitude: ...";
			$('#gps_coords').html(gps_text);
		},
		
		error: function(a) {
			console.log(a);
			var response = JSON.parse(a.responseText);
			$('#results_header').html('Error');
			$('#results_message').html(response['result']['error']);
			$('#results_details').html('<b>Upload details:</b><br/>'
										+ 'image:<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;name: ' + response['upload']['image']['name'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;type: ' + response['upload']['image']['type'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;error: ' + response['upload']['image']['error'] + '<br/>'
										+ '&nbsp;&nbsp;&nbsp;&nbsp;size: ' + Math.round(response['upload']['image']['size'] / 1000) + ' kb<br/>'
										+ 'plate: ' + response['upload']['plate'] + '<br/>'
										+ 'state: ' + response['upload']['state'] + '<br/>'
										+ 'date: ' + response['upload']['date'] + '<br/>'
										+ 'gps: ' + response['upload']['gps_latitude'] + ' / ' + response['upload']['gps_longitude'] + '<br/>'
										+ 'street 1: ' + response['upload']['street1'] + '<br/>'
										+ 'street 2: ' + response['upload']['street2'] + '<br/>'
										+ 'description: ' + response['upload']['description']);
			$('#results_back').html('Back');
			$('#upload_button').css('background', 'none');
			$('#upload_button').css('background-color', 'lightgray');
			$('#upload_prompt').append('UPLOAD!');
			open_window('results_view');
		}
	});
}

function set_gps_marker(e) {
	if (config.use_mapboxgljs){
		$('#upload_marker').remove();
		marker = document.createElement('div');
		marker.className = 'map_marker';
		marker.id = 'upload_marker';
		new mapboxgl.Marker(marker, {offset: [-12, -12]})
		.setLngLat(e.lngLat)
		.addTo(submit_map);
		var gps_text = "Latitude: " + e.lngLat.lat.toFixed(6) + " Longitude: " + e.lngLat.lng.toFixed(6);
		document.getElementById("latitude").value = e.lngLat.lat;
		document.getElementById("longitude").value = e.lngLat.lng;
	}
	else {
		submit_map.removeLayer(marker);
		marker = new L.marker(e.latlng, {icon: xIcon}).addTo(submit_map);
		var gps_text = "Latitude: " + e.latlng.lat.toFixed(6) + " Longitude: " + e.latlng.lng.toFixed(6);
		document.getElementById("latitude").value = e.latlng.lat;
		document.getElementById("longitude").value = e.latlng.lng;
	}
    
    document.getElementById("gps_coords").innerHTML = gps_text;
}

function initialize_body_map(){
	if (!config.use_mapboxgljs){ body_map = L.map('body_map'); }
	
	if (config.use_providers_plugin) {
		try { var tiles = L.tileLayer.provider(config.leaflet_provider, {maxZoom: 20}); }
		catch (err) { console.log(err); }
	}
	
	else if (config.use_google) {
		<?php if ($config['use_google']){
			echo "var options = ";
			include $config_folder . '/google_style.php';
			echo ";\n"; }
		?>
		var extra = '\\' + config.google_extra_layer + '\\';
		try { var tiles = new L.Google('ROADMAP', { mapOptions: { styles: options } }, extra); }
		catch (err) { console.log(err); }
	}
	
	else if (config.use_bing) {
		var imagerySet = config.bing_imagery;
		var bingApiKey = config.bing_api_key;
		try { var tiles = new L.BingLayer(bingApiKey, {type: imagerySet}); }
		catch (err) { console.log(err); }
	}
	
	else if (config.use_mapboxgljs){
		mapboxgl.accessToken = config.mapbox_key;
		var style_url = (config.mobile && config.mapbox_mobile_style_url) ? config.mapbox_mobile_style_url :  config.mapbox_style_url;
		body_map = new mapboxgl.Map({
			container: 'body_map',
			style: style_url,
			center: [config.center_long, config.center_lat],
			zoom: config.zoom
		});
		body_map.on('moveend', function() {
			if (windows.auto_view_change){ windows.auto_view_change = false; }
			else { load_entries(); } 
		});
		body_map.on('click', function() {
			load_entries(); 
		});
		return;
	}
	
	else {
		try { var tiles = L.tileLayer(config.map_url, {maxZoom: 20}); }
		catch (err) { console.log(err); }
	}
	
	//layers and initialization for leaflet
	body_map.addLayer(tiles);
	body_map.setView([config.center_lat, config.center_long], config.zoom);
	markers = L.layerGroup().addTo(body_map);
	
	//bind leaflet map events to body_map-
	//note: these are idential to Mapbox GL JS events in 'else if (config.use_mapboxgljs) above',
	//but keeping seperate for simplicity and in case APIs diverge
	body_map.on('moveend', function(e) {
		if (windows.auto_view_change){ windows.auto_view_change = false; }
		else { load_entries(); } 
	});
	body_map.on('click', function(e) {
		load_entries(); 
	});
}

function initialize_upload_view(){
	if (windows.upload_view_loaded == false) {
		
		//Upload map initialization
		if (!config.use_mapboxgljs){ submit_map = L.map('submit_map'); }
		if (config.use_providers_plugin) {
			try { var tiles2 = L.tileLayer.provider(config.leaflet_provider, {maxZoom: 20}); }
			catch (err) { console.log(err); }
		}
		else if (config.use_google) {
			<?php if ($config['use_google']){
				echo "var options = ";
				include $config_folder . '/google_style.php';
				echo ";\n"; }
			?>
		var extra = '\\' + config.google_extra_layer + '\\';
		try { var tiles = new L.Google('ROADMAP', { mapOptions: { styles: options } }, extra); }
		catch (err) { console.log(err); }
		}
		else if (config.use_bing) {
			var imagerySet = config.bing_imagery;
			var bingApiKey = config.bing_api_key;
			try { var tiles2 = new L.BingLayer(bingApiKey, {type: imagerySet}); }
			catch (err) { console.log(err); }
		}
		else if (config.use_mapboxgljs){
			mapboxgl.accessToken = config.mapbox_key;
			var style_url = (config.mobile && config.mapbox_mobile_style_url) ? config.mapbox_mobile_style_url :  config.mapbox_style_url;
			if(config.mobile && config.mapbox_mobile_style_url){
				submit_map = new mapboxgl.Map({
					container: 'submit_map',
					style: style_url,
					center: [config.center_long, config.center_lat],
					zoom: config.zoom
				});
			}
		}
		else {
			try { var tiles2 = L.tileLayer(config.map_url, {maxZoom: 20}); }
			catch (err) { console.log(err); }
		}
		
		if (!config.use_mapboxgljs){
			submit_map.addLayer(tiles2);
			submit_map.setView([config.center_lat, config.center_long], config.zoom);
		}
		
		//event bindings
		initialize_datetimepicker();
		$('#state').change(function(){
			if ($('#state').val() == 'UNKNOWN' || $('#state').val() == 'NONE'){
				$('#plate').val($('#state').val());
			}
		});
		$('#image_submission').on('change', function(e) {
			auto_scroll('reset');
			fill_plate_and_state();
			fill_date_and_gps(e);
		});
		submit_map.on('click', function(e) { set_gps_marker(e); });
		$('#upload_form').submit( function(event) {
			event.preventDefault();
			submit_form();
		});
		
		windows.upload_view_loaded = true;
	}
}
</script>
</head>

<body>

<?php
//site setup success message
if (isset($_GET['setup_success_dialog'])){
	echo "<div class=\"flex_container_dialog_float\" id=\"success_dialog\">\n";
	echo "<div class=\"setup_centered\">\n";
	echo "<div class=\"settings_group\">\n";
	echo "<h3>Setup Complete!</h3>\n";
	echo "<p>The site is now be ready to receive submissions.</p>\n";
	echo "<p>To change site settings and approve user submissions,
		point your browser at the <a href=\"/admin\">/admin</a> directory
		and log in with the credentials created during setup.</p>";
	echo "<p>Happy reporting!</p>\n";
	echo "</div>\n";
	echo "<div class=\"settings_group\">\n";
	echo "<form>\n";
	echo "<input class=\"bold_button\" type=\"button\" id=\"dismiss_success_dialog\" value=\"DISMISS\"/>\n";
	echo "</form>\n";
	echo "</div>\n";
	echo "</div>\n";
	echo "</div>\n";
}

//navigation elements, desktop and mobile variants
if (!$mobile){
echo <<< DESKTOPNAV
<!-- RIGHT MENU -->
<div class="right_menu">
<div class="right_menu_item">
<span>{$config['site_name']}</span>
</div>
<br>
<div class="right_menu_item" id="submit_link">
<span>SUBMIT</span>
</div>
<br>
<div class="right_menu_item" id="about_link">
<span>ABOUT</span>
</div>
<br>
<div class="right_menu_item" id="loading">
<span></span>
</div>
</div>
DESKTOPNAV;
}
else{
echo <<< MOBILENAV
<div id='nav_container' class='nav_container'>
<div id='nav' class='nav'>

<div id='loading' class='nav_link'>
</div>

<div id='about_link_container' class='nav_link'>
<div id='about_link'><span class='navspan'>{$config['site_name']}</span></div>
</div>

<div id='submit_link_container' class='nav_link'>
<div id='submit_link'><span class='navspan'>SUBMIT</span></div>
</div>

</div>
</div>
MOBILENAV;
}

if (!$mobile){
echo <<< DESKTOPUPLOADVIEW
<!-- SUBMISSION FORM -->
<div class="submission_form" id="upload_view">
<div class="submission_form_container">

<div class="top_dialog_button" onClick="open_window('entry_view')">
<span>&#x2A09</span>
</div>

<form id="upload_form" action="submission.php" style="margin-bottom: 0px" enctype="multipart/form-data">

	<div style="width: 100%">
    <span class="submit_form_item">Image: </span><input type="file" class="submit_form_item" name="image_submission" id="image_submission"><br>
	</div>

	<div class="submit_form_row">
	<div>
    <span class="submit_form_item">Plate: </span><input type="text" name="plate" id="plate" class="submit_form_item" style="width:70px" maxlength="8">
	</div>

	<div>
    <span class="submit_form_item"> State: </span>
    <select name="state" id="state" class="submit_form_item">
    <option value="NY">NY</option>
    <option value="NJ">NJ</option>
    <option value="NYPD">NYPD</option>
    <option value="FDNY">FDNY</option>
	<option value="UNKNOWN">UNKNOWN</option>
	<option value="NONE">NONE</option>
    <option>--</option>
    <option value="AL">AL</option>
    <option value="AK">AK</option>
    <option value="AZ">AZ</option>
    <option value="AR">AR</option>
    <option value="CA">CA</option>
    <option value="CO">CO</option>
    <option value="CT">CT</option>
    <option value="DE">DE</option>
    <option value="DC">DC</option>
    <option value="FL">FL</option>
    <option value="GA">GA</option>
    <option value="HI">HI</option>
    <option value="IA">IA</option>
    <option value="ID">ID</option>
    <option value="IL">IL</option>
    <option value="IN">IN</option>
    <option value="KS">KS</option>
    <option value="KY">KY</option>
    <option value="LA">LA</option>
    <option value="ME">ME</option>
    <option value="MD">MD</option>
    <option value="MA">MA</option>
    <option value="MI">MI</option>
    <option value="MN">MN</option>
    <option value="MS">MS</option>
    <option value="MO">MO</option>
    <option value="MT">MT</option>
    <option value="NE">NE</option>
    <option value="NV">NV</option>
    <option value="NH">NH</option>
    <option value="NM">NM</option>
    <option value="NC">NC</option>
    <option value="ND">ND</option>
    <option value="OH">OH</option>
    <option value="OK">OK</option>
    <option value="OR">OR</option>
    <option value="PA">PA</option>
    <option value="RI">RI</option>
    <option value="SC">SC</option>
    <option value="SD">SD</option>
    <option value="TN">TN</option>
    <option value="TX">TX</option>
    <option value="UT">UT</option>
    <option value="VT">VT</option>
    <option value="VA">VA</option>
    <option value="WA">WA</option>
    <option value="WV">WV</option>
    <option value="WI">WI</option>
    <option value="WY">WY</option>
    </select>
	</div>

	<div>
    <span class="submit_form_item"> When:</span> <input type="text" name="date" class="submit_form_item" id="datetimepicker">
	</div>
	</div>

	<div class="submit_form_row">
	<div>
	<span class="submit_form_item">Cross streets (optional): </span>
	</div>
	<div>
	<input type="text" name="street1" id="street1" class="submit_form_item" style="width:140px">
	<span class="submit_form_item">&amp</span>
	<input type="text" name="street2" id="street2" class="submit_form_item" style="width:140px">
	</div>
	</div>

    <span id="map_prompt">Click to mark location:</span>
	<div id="submit_map"></div>
	<span id="gps_coords">Latitude: ... Longitude: ...</span>
	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">

	<div class="submit_form_row">
	<span class="submit_form_item">Any additional info (
	<div id="character_limit">200</div> characters):</span>
	</div>

	<textarea name="description" onKeyDown="limit_text();" onKeyUp="limit_text();" class="description" id="comments"></textarea>

	<label id='upload_button' class='upload_button'>
	<span id='upload_prompt' class='v-centered'>UPLOAD!</span>
	<input type="submit" name="upload" id="upload"/>
	</label>
	
</form>
</div>
</div>
DESKTOPUPLOADVIEW;
}

else {
echo <<< MOBILEUPLOADVIEW
<div id='submit_view' class='submit_view'>
<form id="upload_form" action="submission.php" enctype="multipart/form-data">

	<label id='file_container' class='file_container'>
	<span id='image_prompt' class='v-centered'>TAP TO ADD AN IMAGE</span>
	<input type="file" name="image_submission" id="image_submission"/>
	</label>

    <span>PLATE:</span>
	<input type="text" name="plate" id="plate" class='wide' maxlength="8"/>

    <span> STATE: </span>
    <select name="state" id="state" class='wide'>
    <option value="NY">NY</option>
    <option value="NJ">NJ</option>
    <option value="NYPD">NYPD</option>
	<option value="NYPD">FDNY</option>
	<option value="UNKNOWN">UNKNOWN</option>
	<option value="NONE">NONE</option>
    <option>--</option>
    <option value="AL">AL</option>
    <option value="AK">AK</option>
    <option value="AZ">AZ</option>
    <option value="AR">AR</option>
    <option value="CA">CA</option>
    <option value="CO">CO</option>
    <option value="CT">CT</option>
    <option value="DE">DE</option>
    <option value="DC">DC</option>
    <option value="FL">FL</option>
    <option value="GA">GA</option>
    <option value="HI">HI</option>
    <option value="IA">IA</option>
    <option value="ID">ID</option>
    <option value="IL">IL</option>
    <option value="IN">IN</option>
    <option value="KS">KS</option>
    <option value="KY">KY</option>
    <option value="LA">LA</option>
    <option value="ME">ME</option>
    <option value="MD">MD</option>
    <option value="MA">MA</option>
    <option value="MI">MI</option>
    <option value="MN">MN</option>
    <option value="MS">MS</option>
    <option value="MO">MO</option>
    <option value="MT">MT</option>
    <option value="NE">NE</option>
    <option value="NV">NV</option>
    <option value="NH">NH</option>
    <option value="NM">NM</option>
    <option value="NC">NC</option>
    <option value="ND">ND</option>
    <option value="OH">OH</option>
    <option value="OK">OK</option>
    <option value="OR">OR</option>
    <option value="PA">PA</option>
    <option value="RI">RI</option>
    <option value="SC">SC</option>
    <option value="SD">SD</option>
    <option value="TN">TN</option>
    <option value="TX">TX</option>
    <option value="UT">UT</option>
    <option value="VT">VT</option>
    <option value="VA">VA</option>
    <option value="WA">WA</option>
    <option value="WV">WV</option>
    <option value="WI">WI</option>
    <option value="WY">WY</option>
    </select>

    <span>DATE:</span>
	<input type="text" name="date" class='wide' id="datetimepicker"/>

	<span>STREET 1 (OPTIONAL):</span>
	<input type="text" name="street1" id="street1" class='wide'/>

	<span>STREET 2 (OPTIONAL):</span>
	<input type="text" name="street2" id="street2" class='wide'/>

    <span id="map_prompt" class='medium'>Tap to mark location if not detected:</span><br>
	<div id="submit_map"></div>
	<span id="gps_coords" class='medium'>Latitude: ... Longitude: ...</span>
	<br>
	<br>
	<br>

	<span id='description-title'>DESCRIPTION (Optional)</span><br>
	<span  class='medium'><div id="character_limit">200</div> characters</span><br>
	<textarea name="description" onKeyDown="limitText();" onKeyUp="limitText();" class="comments" id="comments"></textarea><br>

	<input type="hidden" name="lat" id="latitude">
	<input type="hidden" name="lng" id="longitude">

	<label id='upload_button' class='upload_button'>
	<span id='upload_prompt' class='v-centered'>UPLOAD!</span>
	<input type="submit" name="upload" id="upload"/>
	</label>
	
</form>
</div>
MOBILEUPLOADVIEW;
}

?>

<!-- BODY MAP -->
<div id='body_map'></div>

<!-- ABOUT VIEW -->
<div id='about_view' class='about_view'>
<div class='top_dialog_button' onClick="open_window('entry_view')">
<span>&#x2A09</span>
</div>
<?php echo stripslashes(htmlspecialchars_decode($config['about_text'])); ?>
</div>


<!-- RESULTS VIEW -->
<div class='results_view' id='results_view'>
<div class='top_dialog_button' id='results_close' onClick='javascript:open_window("entry_view"); load_entries();'>
<span>&#x2A09</span>
</div>
<div class='results_view_container' id='results_view_container'>
<h3 id='results_header'></h3>
<p id='results_message'></p>
<p id='results_details'></p>
<!-- <button id='results_submit_another' onClick='javascript:$("#results_view").animate({opacity: "toggle", right: "-565px"}); open_window("submit_view");'>Submit Another</button> -->
<button id='results_back' onClick='javascript:open_window("submit_view");'>Back</button>
</div>
</div>

<!-- ENTRIES VIEW -->
<div class="entry_view" id="entry_view">
<div class="entry_list_content" id="entry_list_content"></div>
</div>

<!-- ENTRY VIEW NAV --->
<div id='column_entry_nav_template' class='column_entry_template' style='max-height:30px;min-height:25px;display:flex;justify-content:space-between;display:none;'>
<div style='margin-left:10px'>
<span id='column_entry_nav_message'>$_COUNT most recent entires within view returned. </span>
</div>
<div style='margin-right:10px'>
<span> Max: </span><input type='text' class='max_view' id='max_view' value='$_MAXVIEW' style='width:60px'/>
</div>
</div>

<!-- COLUMN ENTRY TEMPLATE -->
<div id='column_entry_template' class="column_entry_template" onclick="zoom_to_entry($_ID);" style='display:none;'>
<div class="column_entry_thumbnail">
<img class="thumbnail" src="">
</div>
<div class="moderation_queue_details">
<div class="details_top">
<div class="details_plate">
<div class="plate_name">
<div><br><h2 id='id_text'>$_ID</h2></div>
<div class="info plate_container plate_link" id="plate$_ID" onClick="event.stopPropagation();plate_search(&quot;$PLATE&quot;)">
<div class="plate $_STATE" id='plate_text'>$_PLATE</div></div>
</div>
</div><div class="details_timeplace">
<div class="info edit_date"><span id='date_text'>$_DATE</span></div><br>
<div class="info edit_streets"><span id='streets_text'>$_STREETS</span></div><br>
<div class="info edit_gps"><span id='gps_text'>$_GPS_LATITUDE / $_GPS_LONGITUDE</span></div>
</div>
</div>
<div id='description_text_div'>
<span  id='description_text_label'>DESCRIPTION:</span>
<div><span id='description_text'></span><span class="disqus-comment-count" data-disqus-url="http://carsinbikelanes.nyc/index.php?single_view=$_ID"><wbr></span></div>
</div>
</div>
</div>

<!-- SINGLE VIEW -->
<div id='single_view' class='single_view'>
<img src="" id="fullsize" class="fullsize" />
<div class='column_entry single_view_column_entry' style='background: transparent'>
<div class='moderation_queue_details'>
<div class='details_top'>
<div class='details_plate'>	
<div class='plate_name'><div><br/><h2 id='id_single'>$_ID</h2></div>
<div class='info plate_container plate_link' id='plate_single_link' onClick='event.stopPropagation();plate_search("$PLATE")'>
<div class='plate $_STATE' id='plate_single'>$_PLATE</div>
</div>
</div>
</div>
<div class='details_timeplace'>
<span>TIME: </span>
<div class='info edit_date'>
<span id='date_single'>$_DATE</span>
</div>
<br/>
<span>STREETS: </span>
<div class='info edit_streets main_font'>
<span id='streets_single'></span>
</div>
<br/>
<span>GPS: </span>
<div class='info edit_gps'>
<span id='gps_single'></span>
</div>
</div>
</div>
<div id='description_single_div'>
<span id='description_single_label'>DESCRIPTION:</span>
<div><span id='description_single'></span></div>
</div>
</div>
</div>
<div id='disqus_thread' class='disqus_thread'>
</div>
</div>

<script id="dsq-count-scr" src="//<?php echo $config['disqus']; ?>.disqus.com/count.js" async></script>
</body>

</html>
