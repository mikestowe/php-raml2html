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
  * RAML Class
  * @package RAML
  */
class RAML extends RAMLDataObject
{
	
	private $keys = array(
		'traits',
	);
		
	private $verbs = array();
	private $paths = array();
	private $traits = array();
	private $base = array();
	private $currentPath = '/';
	private $currentVerb = null;
	
	
	/**
	 * Constructing Method
	 * Setups class properties
	 * @param array $verbs
	 * @return void
	 */
	public function __construct($verbs = array())
	{
		$this->setMaster($this);
		$this->verbs = $verbs;
		parent::__construct();
	}
	
	
	/**
	 * Build RAML Object from Array
	 * Used to take a parsed array and build the RAML Object
	 * @param array $array
	 * @return void
	 */
	public function buildFromArray($array)
	{
		$this->paths['/'] = new RAMLPathObject($this, '/');
		
		// Handle Base Data
		foreach ($array as $key => $value) {
			if (is_array($value) && substr($key, 0, 1) == '/') {
				continue;
			} elseif ($key == 'resourceTypes') {
				$this->base = $value[0]['base'];
			} elseif ($key == 'traits') {
				$this->traits[key($value)] = $value[(key($value))];
			} elseif (in_array(strtoupper($key), $this->verbs)) {
				$this->paths[$key]->addVerb($key);
				$this->set(strtoupper($key), $value);
			} else {
				$this->set($key, $value);
			}
			
			unset($array[$key]);
		}
		
		foreach ($this->base as $key => $value) {
			$cleanKey = str_replace('?', '', $key);
			if (in_array($cleanKey, $this->verbs)) {
				$cleanKey = strtoupper($cleanKey);
				$cleanV = array();
				foreach ($value['responses'] as $k => $v) {
					$cleanV['c' . $k] = $v;
				}
				unset($value['responses']);
				$value['responses'] = $cleanV;
			}
			unset($this->base[$key]);
			$this->base[$cleanKey] = $value;
		}
		
		// Handle Paths
		foreach ($array as $key => $value) {
			$this->paths['/']->addChild('/' . $key, $key);
			$this->generatePathData($key, $value);
		}
	}
	
	
	/**
	 * Generata Path Data
	 * Build Path Objects
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function generatePathData($key, $value) {
		$key = $this->handlePlaceHolders($key);
		
		$this->paths[$key] = new RAMLPathObject($this, $key);
		
		foreach ($value as $skey => $svalue) {
			$skey = $this->handlePlaceHolders($skey);
			
			if (is_array($svalue) && substr($skey, 0, 1) == '/') {
				$this->paths[$key]->addChild($this->formatParentPath($key) . $skey, $skey);
				$this->generatePathData($this->formatParentPath($key) . $skey, $svalue);
				unset($value[$skey]);
			} elseif ($skey == 'is') {
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
			} elseif (in_array($skey, $this->verbs)) {
				$this->paths[$key]->addVerb($skey);
				$cleanV = array();
				foreach ($svalue['responses'] as $k => $v) {
					$cleanV['c' . $k] = $v;
				}
				unset($svalue['responses']);
				$svalue['responses'] = $cleanV;
				unset($value[$skey]);
				$value[strtoupper($skey)] = $svalue;
			}
		}
		
		$value = array_merge_recursive($value, $this->base);
		
		$this->paths[$key]->setData($value);
	}
	
	
	/**
	 * Dump
	 * Debug Method - TBR
	 * @return array
	 */
	public function dump()
	{
		var_dump($this->paths);
	}
	
	
	/**
	 * SetVerbs
	 * Sets the list of allowable verbs
	 * @param array $verbs
	 * @return RAML
	 */
	public function setVerbs(array $verbs)
	{
		array_change_key_case ($verbs, ARRAY_KEY_UPPERCASE);
		$this->verbs = $verbs;
		return $this;
	}
	
	
	/**
	 * GetVerbs
	 * Returns a list of allowable verbs
	 * @return array
	 */
	public function getVerbs()
	{
		return $this->verbs;
	}
	
	
	/**
	 * IsActionValid
	 * Returns whether or not an action can be performed on a path
	 * @param string $action
	 * @return bool
	 */
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
	
	
	/**
	 * SetCurrentAction
	 * Sets the Current Action for use by RAML Object
	 * @param string
	 * @return RAML
	 */
	public function setCurrentAction($action)
	{
		if ($this->isActionValid($action)) {
			$this->currentVerb = strtoupper($action);
			return $this;
		}
		
		throw new Exception('Invalid Verb');
	}
	
	
	/**
	 * GetCurrentAction
	 * Returns the current action as set by setCurrentAction
	 * @return string
	 */
	public function getCurrentAction()
	{
		return $this->currentVerb;
	}
	
	
	/**
	 * Action
	 * Shortcut for accessing the CurrentAction Object
	 * @return RAMLDataObject
	 */
	public function action()
	{
		return $this->path()->get($this->getCurrentAction());
	}
	
	
	/**
	 * IsPathValid
	 * Determines whether or not a path is valid
	 * @param string $path
	 * @return bool
	 */
	public function isPathValid($path)
	{
		return isset($this->paths[$path]);
	}
	
	
	/**
	 * GetPathObject
	 * Gets a RAMLPathObject for the defined Path
	 * @param string $path
	 * @return RAMLPathObject | bool
	 */
	public function getPathObject($path)
	{
		if (isset($this->paths[$path])) {
			return $this->paths[$path];
		}
		return false;
	}
	
	
	/**
	 * SetCurrentPath
	 * Set the current path
	 * @param string
	 * @return RAML
	 */
	public function setCurrentPath($path)
	{
		$this->currentPath = $path;
		return $this;
	}
	
	
	/**
	 * GetCurrentPath
	 * Returns the current path as defined in string type
	 * @return string
	 */
	public function getCurrentPath()
	{
		return $this->currentPath;
	}
	
	
	/**
	 * GetChildPaths
	 * Returns a paths children paths as strings
	 * @param string $path
	 * @return array
	 */
	public function getChildPaths($path)
	{
		return $this->paths[$path]->getChildren();
	}
	
	
	/**
	 * Path
	 * Gets the current path object
	 * @return RAMLPathObject
	 */
	public function path()
	{
		return $this->getPathObject($this->getCurrentPath());
	}
	
	
	/**
	 * FormatParentPath
	 * Removes unwanted extensions from parent path
	 * @param string
	 * @return string
	 */
	public function formatParentPath($string)
	{
		return preg_replace('/\.[a-z]{3,4}$/i', '', $string);
	}
	
	
	/**
	 * HandlePlaceHolders
	 * Replaces {} with defined text
	 * @param string
	 * @return string
	 */
	public function handlePlaceHolders($string)
	{
		if (is_string($string) && preg_match('/.*({(.+)}).*/', $string, $matches)) {
			if ($this->get($matches[2])) {
				$t = str_replace($matches[1], $this->get($matches[2]), $string);
				return $t;
			} elseif ($this->get('base') && $this->get('base')->get($matches[2])) {
				$t = str_replace($matches[1], $this->get('base')->get($matches[2]), $string);
				return $t;
			}
		}
		
		return $string;
	}
}
