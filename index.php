<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 0.1beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

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


function findPath($pathRAMLArray, $paths) {
	$calcPath = '';
	$validPath = '';
	
	foreach($paths as $p) {
		$calcPath .= '/' . $p;
		if (isset($pathRAMLArray[$calcPath])) {
			$validPath = $calcPath;
		} elseif ($validPath) {
			return array('path' => $validPath, 'array' => $paths);
		}
		array_shift($paths);
		
		if (!$paths && isset($pathRAMLArray[$calcPath])) {
			return array('path' => $validPath, 'array' => $paths);
		}
	}
	
	return array('path' => false, 'array' => array());
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

	$paths = explode('/', $_GET['path']);
	if (empty($paths[0])) { array_shift($paths); }
	
	$pathRAMLArray = $RAML->resources;
	while ($paths) {
		$tmp = findPath($pathRAMLArray, $paths);
		if (!empty($tmp['path'])) {
			$pathRAMLArray = $pathRAMLArray[$tmp['path']];
			$paths = $tmp['array'];
		} else {
			$pathRAMLArray = false;
			break;
		}
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

require_once($docsTheme);

?>