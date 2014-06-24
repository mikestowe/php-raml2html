<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 1.0beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

namespace RAML;  

 /**
  * RAML Path Object Class
  * @package RAML
  */
class RAMLPathObject extends RAMLDataObject
{
	
	private $path;
	private $children = array();
	private $verbs = array();
	
	
	/**
	 * Construct
	 * @param array $data
	 * @return void
	 */
	public function __construct($master, $path)
	{
		$this->master = $master;
		$this->path = $path;
		parent::__construct();
	}
	
	
	/**
	 * Get Properties
	 * Get Path Object Properties
	 * @return RAMLPathObject
	 */
	private function getProperties()
	{
		return $this->master->getPathObject($this->path);
	}
	
	
	/**
	 * Get Action Properties
	 * Get the current Action Object
	 * @return RAMLDataObject
	 */
	private function getActionProperties()
	{
		return $this->master->action();
	}
	
	
	/**
	 * Add Child
	 * Add a Child to the Path Object
	 * @param string $absolutePath
	 * @param string $relativePath
	 * @return RAMLPathObject
	 */
	public function addChild($absolutePath, $relativePath)
	{
		$absolutePath = $this->master->handlePlaceHolders(str_replace('//', '/', $absolutePath));
		$relativePath = $this->master->handlePlaceHolders(str_replace('//', '/', $relativePath));
		$this->children[$absolutePath] = $relativePath;
		return $this;
	}
	
	
	/**
	 * Get Children
	 * Returns a Path Objects Child Paths
	 * @return array
	 */
	public function getChildren()
	{
		return $this->children;
	}
	
	
	/**
	 * Add Verb
	 * Adds a Verb/ Action to the RAMLPathObject
	 * @param string $key
	 * @return RAMLPathObject
	 */
	public function addVerb($key)
	{
		$this->verbs[] = strtoupper($key);
		return $this;
	}
	
	
	/**
	 * Get Verbs
	 * Return a list of Verbs 
	 * @return array
	 */
	public function getVerbs()
	{
		return $this->verbs;
	}
	
	
	/**
	 * Get Responses
	 * Returns a list of responses (XML, JSON, etc) for an endpoint
	 * @return array
	 */
	public function getResponses()
	{
		$responses = array();

		foreach ($this->getActionProperties()->get('responses')->toArray() as $code => $value) {
			$code = ltrim($code, 'c');
			
			if (isset($value['description']) && count($value) == 1) {
				$responses[$code][] = array('type' => $value['description']);
			}
			
			if (isset($value['body']['example'])) {
				$responses[$code][] = array('type' => 'Standard Response', 'example' => $value['body']['example'], 'schema' => array());
			} 
			
			if (isset($value['body']['application/json']) && is_string($value['body']['application/json'])) {
				$responses[$code][] = array('type' => 'application/json', 'example' => $value['body']['application/json']);
			}
			
			if (isset($value['body']['application/json']) && is_string($value['body']['application/xml'])) {
				$responses[$code][] = array('type' => 'application/xml', 'example' => $value['body']['application/xml']);
			} else {
				$t = 0;
				foreach ($value['body'] as $rkey => $rvalue) {
					$rexample = isset($rvalue['example']) ? $rvalue['example'] : null;
					$rschema = isset($rvalue['schema']) ? $rvalue['schema'] : null;
					$responses[$code][] = array('type' => $rkey, 'example' => $example, 'schema' => $rschema);
				}
			}
		}
			
		return $responses;
	}
	
}
