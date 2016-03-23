<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 1.3beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

namespace RAML2HTML;  

 /**
  * Simple Markdown Parser
  * @package RAML2HTML
  */
class markdown 
{

	static $patterns = array(
		'\n(\s)*-' => '<br />&nbsp; &nbsp; &bullet; ',
		'\n(\s)*([0-9]+)\.?' => '<br />&nbsp; &nbsp; $1. ',
		'\[(info|warning|alert)\]([^\]]+)\[\/(info|warning|alert)\]' => '<div class="$1">$2</div>',
		'^|\s(\*\*|__)([^\*\*|__]+)(\*\*|__)' => '<strong>$2</strong>',
		'^|\s(\*|_)([^\*|_]+)(\*|_)' => '<em>$2</em>',
		'!\[([^\]]+)\]\(([^\)]+)\)' => '<img src="$2" alt="$1" />',
		'\[([^\]]+)\]\(([^\)]+)\)' => '<a href="$2">$1</a>',
		'\#{6}([^\n]+)\n' => '<h6>$1</h6>',
		'\#{5}([^\n]+)\n' => '<h5>$1</h5>',
		'\#{4}([^\n]+)\n' => '<h4>$1</h4>',
		'\#{3}([^\n]+)\n' => '<h3>$1</h3>',
		'\##([^\n]+)\n' => '<h2>$1</h2>',
		'\#([^\n]+)\n' => '<h1>$1</h1>',
	);
	
	
	static function parse($input) {
		foreach (self::$patterns as $search => $replace) {
			$input = preg_replace('/'.$search.'/i', $replace, $input);
		}
		
		// Handle Code
		$input = preg_replace_callback('/`([^`]+)`/i', function($matches) {
			return '<code><pre>'.htmlentities($matches[1]).'</pre></code>';
		}, $input);
		
		// Handle line breaks
		return str_replace("\n", "<br />", $input);
	}
	
	
	static function clean($input) {
		foreach (self::$patterns as $search => $replace) {
			if (strpos($replace, '$2')) {
				$replace = '$2';
			} elseif (strpos($replace, '$1')) {
				$replace = '$1';
			} else {
				$replace = '';
			}
			
			$input = preg_replace('/'.$search.'/i', $replace, $input);
		}
		
		return str_replace("\n", "  ", $input);
	}
}
