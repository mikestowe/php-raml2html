<?php
/**
  * RAML2HTML for PHP -- A Simple API Docs Script for RAML & PHP
  * @version 0.2beta
  * @author Mike Stowe <me@mikestowe.com>
  * @link https://github.com/mikestowe/php-raml2html
  * @link http://www.mikestowe.com/2014/05/raml-2-html.php
  * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2
  */

namespace RAML;  

 /**
  * RAML Data Object Class
  * @package RAML
  */
class RAMLDataObject
{
	protected $master;
	private $data = array();
	
	
	/**
	 * Construct
	 * @param array $data
	 * @return void
	 */
	public function __construct(array $data = array())
	{
		$this->data = $data;
	}
	
	
	/**
	 * Set Master
	 * Sets the Master Object (RAML)
	 * @param RAML $master
	 * @return RAMLDataObject
	 */
	public function setMaster($master)
	{
		$this->master = $master;
		return $this;
	}
	
	
	/**
	 * Set Data
	 * Sets the data
	 * @param array $data
	 * @return RAMLDataObject
	 */
	public function setData(array $data = array())
	{
		$this->data = $data;
		return $this;
	}
	
	
	/**
	 * Set
	 * Set a key, value pair
	 * @param string $key
	 * @param mixed  $value
	 * @return RAMLDataObject
	 */
	public function set($key, $value)
	{
		$this->data[$key] = $value;
		return $this;
	}
	
	
	/**
	 * Get
	 * Get Value based on Key
	 * @param string $dataKey
	 * @return mixed
	 */
	public function get($dataKey)
	{
		if (!isset($this->data[$dataKey])) {
			$dataKey = strtoupper($dataKey);
		}
		
		if (!isset($this->data[$dataKey])) {
			return new RAMLDataObject();
			// shoudl return false
		} elseif (is_array($this->data[$dataKey])) {
			$t = new RAMLDataObject($this->data[$dataKey]);
			$t->setMaster($this->master);
			return $t;
			// convert to preg_match_all
		} elseif (is_string($this->data[$dataKey]) && preg_match('/^\!include ([a-z_\.\/]+)/i', $this->data[$dataKey], $matches)) {
			$ext = array_pop(explode('.', $matches[1]));
			if (in_array($ext, 'yaml', 'raml')) {
				$t = new RAMLDataOject(spyc_load_file($matches[1]));
				$t->setMaster($this);
				return $t;
			}
			
			return file_get_contents($matches[1]);
		}
		
		return $this->master->handlePlaceHolders($this->data[$dataKey]);
	}
	
	
	/**
	 * To Array
	 * returns a data object as an array
	 * @return array
	 */
	public function toArray()
	{
		return $this->data;
	}
	
	
	/**
	 * To String
	 * return a value string
	 * @return string
	 */
	public function toString()
	{
		return (string) current($this->data);
	}
	
	
	/**
	 * Is Array
	 * returns a bool as to whether or not the data type is an array
	 * @return bool
	 */
	public function isArray()
	{
		if (count($this->data) > 1) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * Is String
	 * returns a bool as to whether or not the data type is a string
	 * @return bool
	 */
	public function isString()
	{
		if (count($this->data) == 1 && is_string(current($this->data))) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * Is Int
	 * returns a bool as to whether or not the data type is an Int
	 * @return bool
	 */
	public function isInt()
	{
		if (count($this->data) == 1 && is_int(current($this->data))) {
			return true;
		}
		return false;
	}
	
	
	/**
	 * Get
	 * Get Value based on Key
	 * @param string $dataKey
	 * @return mixed
	 */
	public function __get($key)
	{
		return $this->get($key);
	}
	
	
	/**
	 * Set
	 * Set a key, value pair
	 * @param string $key
	 * @param mixed  $value
	 * @return RAMLDataObject
	 */
	public function __set($key, $value) 
	{
		$this->set($key, $value);
	}
	
	
	/**
	 * To String
	 * return a value string
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}
}
