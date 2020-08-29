<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class balance extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Resa_model');
		$this->load->model('User_model');
		$this->load->model('Period_model');
		$this->load->model('Payment_model');
//normandie		$this->load->model('Balance_model');
	}

	function index() {
	}
	
	public function get() {
		//$this->output->enable_profiler(TRUE);
		
//normandie		$data = $this->Balance_model->getBalance($_GET['year'], $_GET['month'], $_GET['user_id']);
		echo $this->my_json_encode($data);
	}
	
	
	// TODO check version of php
	public function my_json_encode($data) {
		switch ($type = gettype($data)) {
			case 'NULL':
				return 'null';
			case 'boolean':
				return ($data ? 'true' : 'false');
			case 'integer':
			case 'double':
			case 'float':
				return '"' . $data . '"';
			case 'string':
				return '"' . addslashes($data) . '"';
			case 'object':
				$data = get_object_vars($data);
			case 'array':
				$output_index_count = 0;
				$output_indexed = array();
				$output_associative = array();
				foreach ($data as $key => $value) {
					$output_indexed[] = $this->my_json_encode($value);
					$output_associative[] = $this->my_json_encode($key) . ':' . $this->my_json_encode($value);
					if ($output_index_count !== NULL && $output_index_count++ !== $key) {
						$output_index_count = NULL;
					}
				}
				if ($output_index_count !== NULL) {
					return '[' . implode(',', $output_indexed) . ']';
				} else {
					return '{' . implode(',', $output_associative) . '}';
				}
			default:
				return ''; // Not supported
		}
	}	
}
?>