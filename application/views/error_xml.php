<?php 

	$this->load->helper('xml');
	$dom = xml_dom();
	$error = xml_add_child($dom, 'error');
	xml_add_child($error, 'code', $code);
	xml_add_child($error, 'description', $description);
	xml_print($dom);

?>



