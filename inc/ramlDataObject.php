<?php
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
	
	public function setData(array $data = array())
	{
		$this->data = $data;
	}
	
	public function set($key, $value)
	{
		$this->data[$key] = $value;
	}
	
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
