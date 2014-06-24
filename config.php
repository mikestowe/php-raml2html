<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 1.0beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */
  
// Source of Your RAML file (local or http)
$RAMLsource = 'http://api-portal.anypoint.mulesoft.com/twitter/api/twitter-rest-api/twitter-rest-api.raml';

// Types of Action Verbs Allowed
$RAMLactionVerbs = array('get', 'post', 'put', 'patch', 'delete', 'connect', 'trace');

// APC Cache Time Limit - set to "0" to disable
$cacheTimeLimit = '36000';

// Path to the theme file for the docs
$docsTheme = 'templates/theme.phtml';

?>