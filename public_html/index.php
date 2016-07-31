<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
function time_elapsed_string($ptime){
    $etime = time() - $ptime;
    if ($etime < 1){
        return '0 seconds';
    }
    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );
    foreach ($a as $secs => $str){
        $d = $etime / $secs;
        if ($d >= 1){
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str) . ' ago';
        }
    }
}
$sensor_csv = file_get_contents('../data/chch-sensors.csv');
$slines = explode("\n",$sensor_csv);
array_shift($slines);
$kites = array();
$kitedata = array();

foreach($slines as $line){
	$fail = false;
	if($line == '')continue;
	$parts = explode(',',$line);
	foreach($parts as $i => $part){
		$parts[$i] = trim($part);
		if($parts[$i] == -1){
			$fail = true;
			break;
		}
	}
	if($fail)continue;
	
	$kitedata[$parts[1]] = array(
		$parts[2],
		$parts[3],
		$parts[4],
		$parts[5],
		$parts[6],
		$parts[7],
		time_elapsed_string(strtotime($parts[0]))
	);
}
$kitemin = array();
$kitemax = array();
$first = true;

foreach($kitedata as $kite => $data){
	foreach($data as $k => $v){
		if($v == 0)continue;
		if($first || $kitemin[$k] == 0){
			$kitemin[$k] = $v;
			$kitemax[$k] = $v;
		}else{
			$kitemin[$k] = min($kitemin[$k], $v);
			$kitemax[$k] = max($kitemax[$k], $v);
		}
	}
	$first = false;
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
		case 'blue':
		return array(
			'rgba(0, 255, 255, 0)',
			'rgba(0, 255, 255, 1)',
			'rgba(0, 191, 255, 1)',
			'rgba(0, 127, 255, 1)',
			'rgba(0, 63, 255, 1)',
			'rgba(0, 0, 255, 1)',
			'rgba(0, 0, 223, 1)',
			'rgba(0, 0, 191, 1)',
			'rgba(0, 0, 159, 1)',
			'rgba(0, 0, 127, 1)'
		);
		break;
		case 'default':
			return false;
		break;
	}
}


$heatmaps = array(
	'temperature' => 
	array(
		'name'=>'Temperature',
		'icon'=>'sun-o',
		'gradient' => genGradient('default')
	),
	'humidity' => 
	array(
		'name'=>'Humidity',
		'icon'=>'tint',
		'gradient' => genGradient('blue')
	),
	'pressure' => 
	array(
		'name'=>'Pressure',
		'icon'=>'tachometer',
		'gradient' => genGradient('default')
	),
	'luminosity' => 
	array(
		'name'=>'Luminosity',
		'icon'=>'lightbulb-o',
		'gradient' => genGradient('default')
	),
	'co2' => 
	array(
		'name'=>'Carbon Dioxide',
		'icon'=>'tree',
		'gradient' => genGradient('default')
	),
	'sound' => 
	array(
		'name'=>'Noise Pollution',
		'icon'=>'volume-up',
		'gradient' => genGradient('default')
	)
);
$idtohm = array();
$i = 0;
foreach($heatmaps as $k => $v){
	$idtohm[$i] = $k;
	$i++;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>ROA SmartCity</title>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no">
		<meta charset="utf-8">
		<link rel="stylesheet" href="/css/app.css" />
		<link rel="stylesheet" href="/css/font-awesome.css" />
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
		<script src="scripts/angular.js"></script>
		<script src="scripts/angular-resource.js"></script>
		<script src="scripts/Peity.min.js"></script>
	</head>
	<body ng-controller="hackCtrl" >
		<div id="map"></div>
		
		
	</body>
	<script type="text/javascript">
			var map;
			var heatmap;
			var hmids;
			var activehm;
			var infowindow;
			
			var allInfo = $resource("http://roa.redream.co.nz/json.php?kite=:kiteid&type=:typeid");

			function getChart(kite, type) {
				$scope.specific = allInfo.query({kiteid: kite, typeid: type}, chart(type));
			}

			function chart(type) {
				for (var i = 0; i < $scope.specific.length; i++) {
					Wait.push($scope.specific[i][type]);
				}
				$(".line").text(Wait);
				$(".line").peity("line");
			}

			$.fn.peity.defaults.line = {
				delimiter: ",",
				fill: "#c6d9fd",
				height: 160,
				max: null,
				min: 0,
				stroke: "#4d89f9",
				strokeWidth: 1,
				width: 1135
			}
			
			var Wait = [];

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
				<?php 
				$i = 0;
				foreach($heatmaps as $slug => $hm){
					?>
					new google.maps.visualization.HeatmapLayer({
					  data: getKitePoints(<?php echo $i; ?>),
					  map: null,
					  radius: 30,
					  <?php if($hm['gradient']){ ?>
					  gradient: ['<?php echo implode('\',\'',$hm['gradient']); ?>']
					  <?php } ?>
					}),
					<?php 
					$i++;
				}
				?>
				];
				markers = [
				<?php 
				  for($i=0;$i<count($heatmaps);$i++){
					  echo '[';
					  foreach($kitedata as $kite => $data){
						  if($data[$i] == 0)continue;
							 echo 'new google.maps.Marker({
								   position: new google.maps.LatLng('.$kites[$kite]['lat'].', '.$kites[$kite]['long'].'),
								   map: null,
								   kite: "'.$kite.'",
								   type: "'.$idtohm[$i].'",
									title: "<small>'.$heatmaps[$idtohm[$i]]['name'].' ('.$data[6].'):</small><br/><b> '.$data[$i].'</b><span class=\"line\"><img src=\"/img/loading.gif\"/></span> ",
								 }),
							';
					  }
					  echo '],';
					 }
				  ?>
				];
				infowindow = new google.maps.InfoWindow({
				});
				
				markers.forEach(function(item,index){
					item.forEach(function(marker,index){
						google.maps.event.addListener(marker, 'mouseover', function() {
							infowindow.setContent(this.title);
							infowindow.open(map, this);
							getChart(this.kite, this.type);
						});
					});
				});
				hmids = {
				<?php 
				$i=0;
				foreach($heatmaps as $slug => $hm){
					echo '"'.$slug.'": '.$i.',';
					$i ++;
				}
				?>
				};
				
				var legend = document.createElement('span');
				var content = [];
				content.push('<div id="title"><h1><b>SmartCity</b> Dashboard</h1></div><div id="legend">');
				content.push('<div class="layertoggle">');
				<?php
				foreach($heatmaps as $key => $info){
					$active = ($activehm == $key ? 'class="active"' : '');
					echo "content.push('<a href=\"#{$key}\" data-type=\"{$key}\" {$active}><i class=\"fa fa-{$info[icon]} fa-2x\"></i>{$info[name]}</a>');\n";
				}
				?>
				content.push('</div></div>');
				
				legend.innerHTML = content.join('');
				legend.index = 1;
				
				map.controls[google.maps.ControlPosition.LEFT_TOP].push(legend);
				window.location.hash = "";
				$(window).on('hashchange', function(){
					checkHash();
				}).trigger('hashchange');
				setTimeout(checkHash(),200);
			}
			function getKitePoints(id) {
			kiteweights = [
			  <?php 
			  for($i=0;$i<count($heatmaps);$i++){
				  echo '[';
				  foreach($kitedata as $kite => $data){
					  if($data[$i] == 0)continue;
					  echo '{location: new google.maps.LatLng('.$kites[$kite]['lat'].', '.$kites[$kite]['long'].'), weight:'.max($data[$i]-$kitemin[$i]+($kitemax[$i]-$kitemin[$i])/2,0).'},'."\n";
				  }
				  echo '],';
				 }
			  ?>
			 ];
			return kiteweights[id];
			}
			
			function checkHash(){
				
				//if(!window.location.hash)window.location.hash = "#temperature";
				
				var hash = window.location.hash.substring(1);
				
				if(hash == "")return;
				heatmap.forEach(function(item, index){
					item.setMap(null);
				});
				$('.layertoggle a').removeClass('active');
				console.log('.layertoggle a[data-type=\''+hash+'\']');
				$('.layertoggle a[data-type=\''+hash+'\']').addClass('active');
				console.log(hmids[hash]);
				console.log(heatmap);
				heatmap[hmids[hash]].setMap(map);
				markers.forEach(function(item, index){
					item.forEach(function(marker, index){
						marker.setMap(null);
					});
				});
				markers[hmids[hash]].forEach(function(item, index){
					item.setMap(map);
				});
				
			}
		</script>
</html>