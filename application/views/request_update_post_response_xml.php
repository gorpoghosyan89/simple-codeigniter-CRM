<?php 

$this->load->helper('xml');
$dom = xml_dom();
$updates = xml_add_child($dom, 'service_request_updates');
$update = xml_add_child($updates, 'request_update');
xml_add_child($update, 'update_id', $new_update_id);

xml_print($dom);

?>



