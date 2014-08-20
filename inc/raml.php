<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 1.1beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

namespace RAML2HTML;  

 /**
  * RAML Class
  * @package RAML2HTML
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
	private $resources = array();
	private $schemas = array();
	private $examples = array();
	private $currentPath = '/';
	private $currentVerb = null;
	private $includePath = '../raml/';
	
	
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
		$array = $this->handleIncludes($array);
		$this->paths['/'] = new RAMLPathObject($this, '/');
		
		// Handle Base Data
		foreach ($array as $key => $value) {
			if (is_array($value) && substr($key, 0, 1) == '/') {
				continue;
			} elseif ($key == 'resourceTypes') {
				foreach ($value as $v) {
					$this->resources[key($v)] = $v[key($v)];
				}
			} elseif ($key == 'traits') {
				foreach ($value as $v) {
					$this->traits[key($v)] = $v[key($v)];
				}
			} elseif ($key == 'schemas') {
				foreach ($value as $v) {
					$this->schemas[key($v)] = $v[key($v)];
				}
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
				
				if (isset($value['responses'])) {
					foreach ($value['responses'] as $k => $v) {
						$cleanV['c' . $k] = $v;
					}
					unset($value['responses']);
					$value['responses'] = $cleanV;
				}
				
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
				
				if (isset($svalue['responses'])) {
					foreach ($svalue['responses'] as $k => $v) {
						$cleanV['c' . $k] = $v;
					}
					unset($svalue['responses']);
					$svalue['responses'] = $cleanV;
				}
				
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
			if ($this->get($matches[2], false)) {
				$t = str_replace($matches[1], $this->get($matches[2]), $string);
				return $t;
			} elseif ($this->get('base', false) && $this->get('base')->get($matches[2], false)) {
				$t = str_replace($matches[1], $this->get('base')->get($matches[2]), $string);
				return $t;
			}
		}
		
		return $string;
	}
	
	
	/**
	 * Handle Schemas
	 * Replaces Schema references
	 * @param string
	 * @return string
	 */
	 public function handleSchemas($string)
	 {
	 	if (!isset($this->schemas[$string])) {
	 		return $string;
	 	}
		
		return $this->schemas[$string];
	 }
	 
	 
	 /**
	 * Handle Traits
	 * Replaces Traits references
	 * @param string
	 * @return string
	 */
	 public function handleTraits($string)
	 {
	 	// Placeholder
	 }
	 
	 
	 /**
	 * Handle Resources
	 * Replaces ResouceType references
	 * @param string
	 * @return string
	 */
	 public function handleResourceTypes($string)
	 {
	 	// Placeholder
	 }
	
	
	/**
	 * Handle Includes
	 * Handles the Includes within the Array
	 * @param array $array
	 * @return array
	 */
	public function handleIncludes($array)
	{
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->handleIncludes($value);
				
			} elseif (is_string($value) && preg_match('/^\!include ([a-z0-9_\.\/\-]+)/i', $value, $matches)) {
				$ext_t = explode('.', $matches[1]);
				$ext = strtolower(array_pop($ext_t));
				
				if (in_array($ext, array('yaml', 'raml'))) {
					$t = spyc_load_file($this->includePath . $matches[1]);
					$array = array_merge($t, $array);
					unset($array[$key]);
				} else {
					$array[$key] = file_get_contents($this->includePath . $matches[1]);
				}
			}
		}

		return $array;
	}
	
	
	/**
	 * Ping Status
	 * Ping the Server to find out if it's online or not
	 * ##### SHOULD BE A GET REQUEST #####
	 * @param string $url      defaults to baseUri
	 * @Param array  $headers  defaults to empty array
	 * @param int    $expire   defaults to 300 seconds or 5 minutes
	 * @return string (online | offline)
	 */
	public function pingStatus($url = 'default', $headers = array(), $expire = 300, $notifyEmail = false)
	{
		global $cacheTimeLimit;
		
		if ($url == 'default') {
			$url = $this->get('baseUri');
		}
		
		$status = false;
		if ($cacheTimeLimit && function_exists('apc_fetch')) {
			$status = apc_fetch('RAMLStatus' . md5($url));
		}
		
		if ($status) {
			return $status;
		}
		
		// Insert CURL with Optional Headers
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		$output = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		
		$status = 'offline';
		if (substr($http_status, 0, 1) == '2') {
			$status = 'online';
		}
		
		if ($notifyEmail && $status == 'offline') {
			mail($notifyEmail, $this->title . ' API is Down!', 'The server at ' . $url . ' failed to be queried successfully.', 'FROM: ' . $notifyEmail);
		}
		
		if ($cacheTimeLimit && function_exists('apc_store')) {
			apc_store('RAMLStatus' . md5($url), $status, $cacheTimeLimit);
		}
		
		return $status;
	}


	/**
	 * Set Include Path
	 * Required for multi-directory RAML files
	 * @param string $path     path of the RAML files
	 * @return RAML
	 */
	public function setIncludePath($path)
	{
		$this->includePath = realpath($path) . '/';
		return $this;
	}
}
