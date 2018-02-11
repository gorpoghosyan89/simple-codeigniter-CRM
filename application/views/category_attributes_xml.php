<?php 


$this->load->helper('xml');
$dom = xml_dom();

$service_definition = xml_add_child($dom, 'service_definition');

	$service_code = xml_add_child($service_definition, 'service_code', $category_id);

	foreach($attributes->result() as $row) {

	$attribute = xml_add_child($service_definition, 'attribute');

	xml_add_child($attribute, 'variable', $row->variable);
	xml_add_child($attribute, 'code', $row->attribute_id);
	xml_add_child($attribute, 'datatype', $row->datatype);
	xml_add_child($attribute, 'required', $row->required);
	xml_add_child($attribute, 'datatype_description', $row->datatype_description);
	xml_add_child($attribute, 'order', $row->order);
	xml_add_child($attribute, 'description', $row->description);
	xml_add_child($attribute, 'values', json_decode($row->values));	

	}

xml_print($dom);



?>



