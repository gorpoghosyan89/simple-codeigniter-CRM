<?php

class Category_attributes extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library('saml_auth');
		$this->load->helper('xml');
		$this->load->helper('fms_endpoint');
	    open311_enabled_or_error();	
	}

	function index() {
		$data['categories'] = $this->db->get('category_attributes');
		$this->load->view('category_attributes_xml', $data);
	}
}

?>
