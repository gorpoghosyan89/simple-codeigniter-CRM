<?php 


$this->load->helper('xml');
$dom = xml_dom();
$services = xml_add_child($dom, 'services');


foreach($categories->result() as $row) {

$service = xml_add_child($services, 'service');

xml_add_child($service, 'service_code', $row->category_id);
xml_add_child($service, 'metadata', $row->metadata);
xml_add_child($service, 'type', $row->type);
xml_add_child($service, 'keywords', $row->keywords);
xml_add_child($service, 'group', $row->group);
xml_add_child($service, 'service_name', $row->category_name);
xml_add_child($service, 'description', $row->description);

}

xml_print($dom);



?>



