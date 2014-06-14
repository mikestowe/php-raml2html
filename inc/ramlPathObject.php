<?php
class RAMLPathObject extends RAMLDataObject
{
	
	private $children = array();
	private $verbs = array();
	
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
		
	}
	
	
}
