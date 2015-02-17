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
  * RAML Data Object Class
  * @package RAML2HTML
  */
class RAMLDataObject
{
	protected $master;
	private $data = array();
	
	public function __construct(array $data = array())
	{
		$this->data = $data;
	}
	
	public function setMaster($master)
	{
		$this->master = $master;
	}
	
	public function setData($data = array())
	{
		$this->data = $data;
	}
	
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}
	
	/**
	 * Get
	 * Get Value based on Key
	 * @param string $dataKey
	 * @return mixed
	 */
	public function get($dataKey, $default = '[RAMLDataObject]')
	{
		if (!isset($this->data[$dataKey])) {
			$dataKey = strtoupper($dataKey);
		}
		
		if (!isset($this->data[$dataKey])) {			
			if ($default != '[RAMLDataObject]') {
				return $default;
			}
			return new RAMLDataObject();
			// shoudl return false
		} elseif (is_array($this->data[$dataKey])) {
			$t = new RAMLDataObject($this->data[$dataKey]);
			$t->setMaster($this->master);
			return $t;
			// convert to preg_match_all
		} elseif (is_string($this->data[$dataKey]) && preg_match('/^\!include ([a-z_\.\/]+)/i', $this->data[$dataKey], $matches)) {
			$ext = array_pop(explode('.', $matches[1]));
			if (in_array($ext, array('yaml', 'raml'))) {
				$t = new RAMLDataObject(spyc_load_file($matches[1]));
				$t->setMaster($this);
				return $t;
			}
			
			return file_get_contents($matches[1]);
		} elseif ($dataKey == 'schema') {
			$this->data[$dataKey] = $this->master->handleSchema($this->data[$dataKey]);
		}
		
		return $this->master->handlePlaceHolders($this->data[$dataKey]);
	}
	
	public function toArray()
	{
		return $this->data;
	}
	
	public function toString()
	{
		return (string) current($this->data);
	}
	
	public function isArray()
	{
		if (count($this->data) > 1) {
			return true;
		}
		return false;
	}
	
	public function isString()
	{
		if (count($this->data) == 1 && is_string(current($this->data))) {
			return true;
		}
		return false;
	}
	
	public function isInt()
	{
		if (count($this->data) == 1 && is_int(current($this->data))) {
			return true;
		}
		return false;
	}
	
	public function __get($key)
	{
		return $this->get($key);
	}
	
	public function __set($key, $value) 
	{
		$this->set($key, $value);
	}
	
	public function __toString()
	{
		return $this->toString();
	}
}
