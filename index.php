<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 0.2beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

require_once('inc/spyc.php');
require_once('inc/ramlDataObject.php');
require_once('inc/raml.php');
require_once('inc/ramlPathObject.php');
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

function formatResponse($text) {	
	return str_replace(array(" ", "\n"), array("&nbsp;", "<br />"), htmlentities($text));
}

$RAML = new RAML($RAMLactionVerbs);
$RAML->buildFromArray($RAMLarray);

if (isset($_GET['path'])) {
	$RAML->setCurrentPath($_GET['path']);
	unset($_GET['path']);
}

if (isset($_GET['action']) && $RAML->isActionValid($_GET['action'])) {
	$RAML->setCurrentAction($_GET['action']);
	unset($_GET['action']);
}

require_once($docsTheme);

?>