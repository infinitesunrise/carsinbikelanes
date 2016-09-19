CARS IN BIKE LANES
------------------

CIBL is a browsable geographic database for crowd-sourcing traffic violation reports. Originally designed to publicly track illegal automotive encroachment into New York City bike lanes at carsinbikelanes.nyc, CIBL can be adapted to document any sort of observable traffic violations within a defined geographic area. CIBL records the time, date, cross streets, GPS coordinates, user description and image of each record submitted. CIBL's setup wizard should be able to self-deploy in a LAMP environment upon navigating to /index.php in a web browser. Adapt CIBL for your city however you'd like. Better yet, invite your local law enforcement agency to be involved!   
  
Features:  
- Responsive desktop site built with jQuery
- Quick and intuitive mobile site encourages on-the-go submissions
- Support for a long list of map providers
- Automatic detection of time, date and GPS information from image EXIF data
- Comments on individual submissions via Disqus
- Multiple moderators and email alerts
- Submission moderation queue
- Edit prior entries
- Custom project boundaries, site identity and 'about' info. 
  
Dependencies:  
- PHP 5.2+  
- MySQL 5.5+  
- Apache  
  
Included FOSS / third-party libraries and plugins:  
- jquery datetimepicker plugin by Valeriy (https://github.com/xdan)  
- exif library plugin by Jacob Seidelin (https://github.com/jseidelin)  
- leaflet-providers by leaflet-extras (https://github.com/leaflet-extras)  
- leaflet-plugins by Pavel Shramov (https://github.com/shramov/leaflet-plugins)  
- Leaflet (https://github.com/Leaflet/Leaflet)  
- Mapbox (https://github.com/mapbox)  
- Google fonts & Google Javascript API  
- Bing maps API  
- license plate font by Dave Hansen  
  
Map providers currently supported:  
- OpenStreetMap (Including dozens of third-party OSM tile hosts)  
- Custom (Self-hosted or third-party hosted tiles, such as Mapbox)  
- Bing  
- Google

CIBL uses the GNU General Public License v3.0