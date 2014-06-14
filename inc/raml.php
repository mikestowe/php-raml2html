<?php
class RAML extends RAMLDataObject
{
	
	private $keys = array(
		'traits',
	);
		
	private $verbs = array();
	private $paths = array();
	private $traits = array();
	private $currentPath = '/';
	private $currentVerb = null;
	
	
	
	public function __construct($verbs = array())
	{
		$this->setMaster($this);
		$this->verbs = $verbs;
		parent::__construct();
	}
	
	
	public function buildFromArray($array)
	{
		$this->paths['/'] = new RAMLPathObject($this, '/');
		
		foreach ($array as $key => $value) {
			if (is_array($value) && substr($key, 0, 1) == '/') {
				$this->paths['/']->addChild('/' . $key, $key);
				$this->generatePathData($key, $value);
			} elseif ($key == 'traits') {
				$this->traits[key($value)] = $value[(key($value))];
			} elseif (in_array($key, $this->verbs)) {
				$this->paths[$key]->addVerb($key);
				$this->set(strtoupper($key), $value);
			} else {
				$this->set($key, $value);
			}
		}
	}
	
	
	public function generatePathData($key, $value) {
		$this->paths[$key] = new RAMLPathObject($this, $key);
		
		foreach ($value as $skey => $svalue) {
			if (is_array($svalue) && substr($skey, 0, 1) == '/') {
				$this->paths[$key]->addChild($key . $skey, $skey);
				var_dump($this->paths[$key]->getChildren());
				$this->generatePathData($key . $skey, $svalue);
				unset($value[$skey]);
			} elseif ($skey == 'is') {
				/*
				if (is_array($svalue)) {
					foreach($svalue as $ivalue) {
						if (!isset($value[$ivalue])) {
							$value[$ivalue] = $this->traits[$ivalue];
						} elseif (is_array($this->traits[$ivalue])) {
							$value[$ivalue] = array_merge($value[$ivalue], $this->traits[$ivalue]);
						}
					}
				} else {
					if (!isset($value[$svalue])) {
						$value[$svalue] = $this->traits[$svalue];
					} elseif (is_array($this->traits[$svalue])) {
						$value[$svalue] = array_merge($value[$svalue], $this->traits[$svalue]);
					}
				}
				*/
			} elseif (in_array($skey, $this->verbs)) {
				$this->paths[$key]->addVerb($skey);
				unset($value[$skey]);
				$value[strtoupper($skey)] = $svalue;
			}
		}
		
		$this->paths[$key]->setData($value);
	}
	
	
	public function dump()
	{
		var_dump($this->paths);
	}
	
	
	public function setVerbs(array $verbs)
	{
		array_change_key_case ($verbs, ARRAY_KEY_UPPERCASE);
		$this->verbs = $verbs;
	}
	
	public function getVerbs()
	{
		return $this->verbs;
	}
	
	public function isActionValid($action)
	{
		// Path must be valid before testing action
		if(!$this->isPathValid($this->getCurrentPath())) {
			return false;
		}
		
		$t = strtoupper($action);
		if (in_array($t, $this->path()->getVerbs())) {
			return true;
		}
		return false;
	}
	
	public function setCurrentAction($action)
	{
		if ($this->isActionValid($action)) {
			$this->currentVerb = strtoupper($action);
			return $this;
		}
		
		throw new Exception('Invalid Verb');
	}
	
	public function getCurrentAction()
	{
		return $this->currentVerb;
	}
	
	public function action()
	{
		return $this->path()->get($this->getCurrentAction());
	}
	
	public function isPathValid($path)
	{
		return isset($this->paths[$path]);
	}
	
	public function getPathObject($path)
	{
		if (isset($this->paths[$path])) {
			return $this->paths[$path];
		}
		return false;
	}
	
	public function currentPath()
	{
		return $this->getPathObject($this->getCurrentPath());
	}
	
	public function setCurrentPath($path)
	{
		$this->currentPath = $path;
		return $this;
	}
	
	public function getCurrentPath()
	{
		return $this->currentPath;
	}
	
	public function getChildPaths($path)
	{
		return $this->paths[$path]->getChildren();
	}
	
	public function path()
	{
		return $this->getPathObject($this->getCurrentPath());
	}

	
	
}
