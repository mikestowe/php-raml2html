<?php
require_once('inc/spyc.php');
require_once('config.php');
$RAMLarray = spyc_load(file_get_contents($RAMLsource));


function generateResource($object, $RAMLarray) {
	global $RAMLactionVerbs;
	
	$object = new stdClass();
	$object->resources = array();
	$object->verbs = array();
	
	foreach ($RAMLarray as $k => $v) {
		if (in_array($k, $RAMLactionVerbs)) {
			$object->verbs[$k] = $v;
		} elseif (substr($k, 0, 1) != '/') {
			$object->$k = $v;
		} else {
			$object->resources[$k] = $v;
		}
	}
	
	return $object;
}


$RAML = generateResource($RAML, $RAMLarray);
$RAML->currentResource = $RAML;


if (!empty($_GET['path']) && $_GET['path'] != '/') {
	$pathRAMLArray = $RAMLarray;

	$getPath = $_GET['path'];
	$paths = explode('/', $_GET['path']);
	if (empty($paths[0])) { array_shift($paths); }
	foreach ($paths as $p) {
		if (isset($pathRAMLArray[$getPath])) {
			$pathRAMLArray = $pathRAMLArray[$getPath];
			break;
		}
		
		$pl = strlen($p);
		$gpl = strlen($getPath);
		$getPath = substr($getPath, $pl + 1, $gpl);
		
		$pathRAMLArray = $pathRAMLArray['/' . $p];
	}
	
	$RAML->currentResource = generateResource($RAML->currentResource, $pathRAMLArray);
}

$RAML->currentResource->path = !empty($_GET['path']) ? $_GET['path'] : '/';
$RAML->currentResource->pathSafe = $RAML->currentResource->path;

if (substr($RAML->currentResource->pathSafe, -1) == '/') {
	$RAML->currentResource->pathSafe = substr($RAML->currentResource->pathSafe, 0, -1);
}


require_once('templates/theme.phtml');

?>