<?php
class RAMLPathObject extends RAMLDataObject
{
	
	private $path;
	private $children = array();
	private $verbs = array();
	
	public function __construct($master, $path)
	{
		$this->master = $master;
		$this->path = $path;
		parent::__construct();
	}
	
	private function getProperties()
	{
		return $this->master->getPathObject($this->path);
	}
	
	private function getActionProperties()
	{
		return $this->master->action();
	}
	
	public function addChild($absolutePath, $relativePath)
	{
		$absolutePath = str_replace('//', '/', $absolutePath);
		$relativePath = str_replace('//', '/', $relativePath);
		$this->children[$absolutePath] = $relativePath;
		return $this;
	}
	
	public function getChildren()
	{
		return $this->children;
	}
	
	
	public function addVerb($key)
	{
		$this->verbs[] = strtoupper($key);
	}
	
	public function getVerbs()
	{
		return $this->verbs;
	}
	
	
	// Handle Responses More Effectively
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
