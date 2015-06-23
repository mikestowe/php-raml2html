<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 1.1beta
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


// Dangling Function
function formatResponse($text) {
        return str_replace(array(" ", "\n"), array("&nbsp;", "<br />"), htmlspecialchars($text, ENT_QUOTES));
}

// Handle Caching and Build
$RAML = false;
if ($cacheTimeLimit && function_exists('apc_fetch')) {
	$RAML = apc_fetch('RAML' . md5($RAMLsource));
} elseif (!$cacheTimeLimit && function_exists('apc_fetch')) {
	// Remove existing cache files
	apc_delete('RAML' . md5($RAMLsource));
}

if (!$RAML) {
	$RAMLarray = spyc_load(file_get_contents($RAMLsource));
	$RAML = new RAML2HTML\RAML($RAMLactionVerbs);

	$RAML->setIncludePath(dirname($RAMLsource) . '/');
	$RAML->buildFromArray($RAMLarray);
	
	if ($cacheTimeLimit && function_exists('apc_store')) {
		apc_store('RAML' . md5($RAMLsource), $RAML, $cacheTimeLimit);
	}
}


// Set Current Path
if (isset($_GET['path'])) {
	$RAML->setCurrentPath($_GET['path']);
	unset($_GET['path']);
}


// Set Current Action
if (isset($_GET['action']) && $RAML->isActionValid($_GET['action'])) {
	$RAML->setCurrentAction($_GET['action']);
	unset($_GET['action']);
}

// Render Template
require_once($docsTheme);

?>
