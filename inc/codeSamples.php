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
  * Code Sample Generator
  * @package RAML2HTML
  */
class codeSamples
{
	private $RAML = false;
	
	/**
	 * Setup Class
	 * @param RAML
	 * @return void
	 */
	function __construct($object) {
		$this->RAML = $object;
	}
	
	/**
	 * Create a Path for API Calls
	 * @return string
	 */
	private function path() {
		$path = $this->RAML->baseUri . $this->RAML->getCurrentPath();
		if ($this->RAML->getCurrentAction() == 'GET') {
			$path .= '?';
			$opt = true;
			
			foreach ($this->RAML->action()->get('queryParameters')->toArray() as $k => $v) {
				if (isset($v['required']) && $v['required'] == 1) {
					$path .= $k . '=';
					if (isset($v['example'])) {
						$path .= urlencode($v['example']) . '&';
					} else {
						$path .= '%' . $k . '%&';
					}
				} elseif (isset($v['required']) && $opt && isset($v['example'])) {
					$path .= $k . '=' . urlencode($v['example']) . '&';
					$opt = false;
				}
			}
			return substr($path, 0,-1);
		}
		
		return $path;
	}
	
	
	/**
	 * Produce Code Sample for PHP
	 * @return string
	 */
	public function php() {
		$template = '$ch = curl_init();' . "\n";
		$template .= 'curl_setopt($ch, CURLOPT_URL, "'. $this->path() .'");' . "\n"; 
		$template .= 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);' . "\n";
		$template .= 'curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); ' . "\n";
		$template .= '' . "\n";
		
		$template .= '// Make sure you set the nessary headers as a $headers array' . "\n";
		$template .= 'curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);' . "\n";
		
		$template .= '' . "\n";
		
		if ($this->RAML->getCurrentAction() != 'GET') {
			$template .= 'curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "'.strtolower($this->RAML->getCurrentAction()).'")' . "\n";                                                                     
		}
		
		if (in_array($this->RAML->getCurrentAction(), array('POST', 'PUT', 'PATCH'))) {
			if($this->RAML->get('body')->get('application/json')->get('example')) {
				$template .= '$body = \'' . str_replace("'", "\\'", $this->RAML->get('body')->get('application/json')->get('example')) . '\';' . "\n";
			} else {
				$template .= '// $body is your JSON/ XML/ Text/ Form Query/ etc' . "\n";
			}
			
			$template .= 'curl_setopt($ch, CURLOPT_POSTFIELDS, $body);' . "\n"; 
			$template .= '' . "\n"; 
		}
		
		$template .= '$response = curl_exec($ch);' . "\n";
		$template .= '$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);' . "\n";
		$template .= 'curl_close($ch);' . "\n";
		
		return $template;
	}



	/**
	 * Produce Code Sample for Rails
	 * @return string
	 */
	public function rails() {
		$template = 'uri = URI.parse("'.$this->path().'")' . "\n";
		$template .= 'http = Net::HTTP.new(uri.host, uri.port)' . "\n";
		$template .= 'request = Net::HTTP::'.ucfirst(strtolower($this->RAML->getCurrentAction())).'.new(uri.request_uri)' . "\n\n";
		$template .= '# Make sure you set the appropriate headers' . "\n";
		$template .= 'request["header"] = "header value"' . "\n\n";
		
		if ($this->RAML->getCurrentAction() != 'GET') {
			$template .= '# body is your JSON/ XML/ Text/ Form Query/ etc' . "\n";
			$template .= 'request.set_form_data(body)' . "\n\n";
		}
		
		$template .= 'response = http.request(request)' . "\n";
		
		return $template;
	}
	
	
	
	/**
	 * Produce Code Sample for JavaScript
	 * @return string
	 */
	public function javascript() {		
		$template = 'var xmlHttp = new XMLHttpRequest();' . "\n";
		$template .= 'xmlHttp.open("'.strtoupper($this->RAML->getCurrentAction()).'", "'.$this->path().'", false);' . "\n";
		$template .= "\n";
		$template .= "// Make sure you set the appropriate headers\n";
		$template .= 'xmlHttp.setRequestHeader("Header Key", "Header Value");' . "\n\n";
		$send = 'null';
		
		if ($this->RAML->getCurrentAction() != 'GET') {
			$template .= 'var data = "# body is your JSON/ XML/ Text/ Form Query/ etc"' . "\n";
			$send = 'data';
		}
		
		$template .= 'xmlHttp.send('.$send.');' . "\n\n";
		$template .= 'var response = xmlHttp.responseText;' . "\n";
		
		return $template;
	}
}
