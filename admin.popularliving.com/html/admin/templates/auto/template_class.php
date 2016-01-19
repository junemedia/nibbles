<?php

class Template {
	public $output;
	public $file;
	public $values = array();
	
	public function  __construct($file) {
		$this->file = $file;
		$this->output = file_get_contents($this->file);
	}
	
	public function set($key,$value) {
		$this->values[$key] = $value;
	}
	
	public function output() {
		foreach ($this->values AS $key => $value) {
			$find = "[$key]";
			$this->output = str_replace($find,$value,$this->output);
		}
		return $this->output;
	}
}

?>