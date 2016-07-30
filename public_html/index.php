<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$activehm = $_GET['hm'];

$sensor_csv = '';//file_get_contents('../data/chch-sensors.csv');
$slines = explode("\n",$sensor_csv);
array_shift($slines);
$kites = array();

foreach($slines as $line){
	if($line == '')continue;
	$parts = explode(',',$line);
}

$csv_kites = file_get_contents('../data/kite-locations.csv');
$klines = explode("\n",$csv_kites);
array_shift($klines);


foreach($klines as $line){
	if($line == '')continue;
	$parts = explode(',',$line);
	$kites[$parts[0]]['lat'] = trim($parts[1]);
	$kites[$parts[0]]['long'] = trim($parts[2]);
}

function genGradient($type){
	switch($type){
		case 'green':
			return array(
				'rgba(0,255,0,0)',
				'rgba(0,255,0,1)',
				'rgba(50,150,50,1)'
			);
		break;
		case 'red':
			return array(
				'rgba(255,0,0,0)',
				'rgba(255,0,0,1)',
				'rgba(255,255,255,1)'
			);
		break;
	}
}


function getGradients($type1, $type2){
	return array('pos'=>genGradient($type1),'neg'=>genGradient($type2));
}

$heatmaps = array(
	'temperature' => 
	array(
		'name'=>'Temperature',
		'icon'=>'sun-o',
		'gradients' => getGradients('green','red')
	),
	'humidity' => 
	array(
		'name'=>'Humidity',
		'icon'=>'tint',
		'gradients' => getGradients('green','red')
	),
	'pressure' => 
	array(
		'name'=>'Pressure',
		'icon'=>'tachometer',
		'gradients' => getGradients('green','red')
	),
	'luminosity' => 
	array(
		'name'=>'Light Levels',
		'icon'=>'lightbulb-o',
		'gradients' => getGradients('green','red')
	),
	'co2' => 
	array(
		'name'=>'CO2 Levels',
		'icon'=>'tree',
		'gradients' => getGradients('green','red')
	),
	'sound' => 
	array(
		'name'=>'Noise Pollution',
		'icon'=>'volume-up',
		'gradients' => getGradients('green','red')
	)
);

if(!$heatmaps[$activehm])$activehm = 'temperature';

//print_r($heatmaps);
//
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ROA SmartCity</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<link rel="stylesheet" href="/css/app.css" />
		<link rel="stylesheet" href="/css/font-awesome.css" />
	</head>
	<body>
		<div id="map"></div>
		<script type="text/javascript">
			function getFile(path, asynch, callback) {
				var xhr = new XMLHttpRequest();
				xhr.open("GET", path, asynch);
				xhr.onload = function (e) {
				  if (xhr.readyState === 4) {
					callback(xhr.responseText);
				  }
				};
				xhr.onerror = function (e) {
				  console.error(xhr.status);
				};
				xhr.send(null);
			  }

			function loadScript(src) {
				var element = document.createElement("script");
				element.src = src;
				document.body.appendChild(element);
			}
			getFile("config.json", false, function(configData) {
				config = JSON.parse(configData)
				console.log("Using api key: " + config.GOOGLE_MAPS_API_KEY);
				loadScript("https://maps.googleapis.com/maps/api/js?key=" + config.GOOGLE_MAPS_API_KEY + "&libraries=drawing,visualization&callback=initMap");
			});
			function initMap() {
				map = new google.maps.Map(document.getElementById('map'), {
				  center: {lat: -43.531403, lng: 172.631714},
				  zoom: 13,
				  mapTypeControl: false,
				  streetViewControl: false
				});
				heatmap = [
				<?php $slug = $activehm;
					$hm = $heatmaps[$slug];
					
					?>
					new google.maps.visualization.HeatmapLayer({
					  data: getKitePoints(),
					  map: map,
					  radius: 30,
					  gradient: ['<?php echo implode('\',\'',$hm['gradients']['pos']); ?>']
					}),
					new google.maps.visualization.HeatmapLayer({
					  data: getKitePoints(),
					  map: null,
					  radius: 30,
					  gradient: ['<?php echo implode('\',\'',$hm['gradients']['neg']); ?>']
					}),
					<?php 
				
				?>
				];
				var legend = document.createElement('div');
				legend.id = 'legend';
				var content = [];
				content.push('<div class="layertoggle">');
				<?php
				foreach($heatmaps as $key => $info){
					$active = ($activehm == $key ? 'class="active"' : '');
					echo "content.push('<a href=\"/?hm={$key}\" {$active}><i class=\"fa fa-{$info[icon]} fa-2x\"></i>{$info[name]}</a>');\n";
				}
				?>
				content.push('</div>');
				legend.innerHTML = content.join('');
				legend.index = 1;
				map.controls[google.maps.ControlPosition.LEFT_CENTER].push(legend);
				
				var title = document.createElement('div');
				title.id = 'title';
				title.innerHTML = '<h1><b>SmartCity</b> Dashboard</h1>';
				map.controls[google.maps.ControlPosition.LEFT_TOP].push(title);
			}
			function getKitePoints(id) {
			return [
			  <?php foreach($kites as $kite){
				  echo 'new google.maps.LatLng('.$kite['lat'].', '.$kite['long'].'),'."\n";
			  }?>
			 ];
			}
			
		</script>
	</body>
</html>