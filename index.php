<?php
require_once('inc/spyc.php');
require_once('config.php');

$RAMLarray = false;
if ($cacheTimeLimit && function_exists('apc_fetch')) {
	$RAMLarray = apc_fetch('RAML' . md5($RAMLsource));
}

if (!$RAMLarray) {
	$RAMLarray = spyc_load(file_get_contents($RAMLsource));
	if ($cacheTimeLimit && function_exists('apc_store')) {
		apc_store('RAML' . md5($RAMLsource), $RAMLarray, $cacheTimeLimit);
	}
}


function generateResource($RAMLarray) {
	global $RAMLactionVerbs;
	
	$object = new stdClass();
	$object->resources = array();
	$object->verbs = array();
	
	if ($RAMLarray) {
		$object->exists= true;
		
		foreach ($RAMLarray as $k => $v) {
			if (in_array($k, $RAMLactionVerbs)) {
				$object->verbs[$k] = $v;
			} elseif (substr($k, 0, 1) != '/') {
				$object->$k = $v;
			} else {
				$object->resources[$k] = $v;
			}
		}
	}
	
	return $object;
}


function formatResponse($text) {
	return str_replace(array(" ", "\n"), array("&nbsp;", "<br />"), $text);
}


$RAML = generateResource($RAMLarray);
$RAML->currentResource = $RAML;
$RAML->currentAction = false;

if (!empty($_GET['action']) && in_array($_GET['action'], $RAMLactionVerbs)) {
	$RAML->currentAction = $_GET['action'];
}

// Kill for security purposes
unset($_GET['action']);


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
	
	if (!$pathRAMLArray) {
		$RAML->currentResource = generateResource(array());
	} else {
		$RAML->currentResource = generateResource($pathRAMLArray);
	}
	
}

$RAML->currentResource->path = !empty($_GET['path']) ? $_GET['path'] : '/';
$RAML->currentResource->pathSafe = $RAML->currentResource->path;

if (substr($RAML->currentResource->pathSafe, -1) == '/') {
	$RAML->currentResource->pathSafe = substr($RAML->currentResource->pathSafe, 0, -1);
}

require_once('templates/theme.phtml');

?>