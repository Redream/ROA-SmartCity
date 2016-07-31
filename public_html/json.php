<?php
header('Access-Control-Allow-Origin: *');  
$getkite = $_GET['kite'];
$gettype = $_GET['type'];

$sensor_csv = file_get_contents('../data/chch-sensors.csv');
$slines = explode("\n",$sensor_csv);
array_shift($slines);
$kites = array();
$kitedata = array();
$typeid = array(
'temperature' => 2,
'humidity' => 3,
'pressure' => 4,
'luminosity' => 5,
'co2' => 6,
'sound' => 7
);
foreach($slines as $line){
	$fail = false;
	if($line == '')continue;
	$parts = explode(',',$line);
	foreach($parts as $i => $part){
		$parts[$i] = trim($part);
	}
	if($parts[1] != $getkite)continue;
	if($parts[$typeid[$gettype]] == -1){
		$kitedata = array();
	}
	$time = strtotime($parts[0]);
	$kitedata[] = array('time'=> $time,$gettype => $parts[$typeid[$gettype]]);
}
echo json_encode($kitedata,true);
?>