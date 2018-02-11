<?php 

$this->load->helper('xml');
$dom = xml_dom();
$requests = xml_add_child($dom, 'service_requests');

foreach($query->result() as $row) {

	$request = xml_add_child($requests, 'request');

	xml_add_child($request, 'service_request_id', $row->report_id);
	xml_add_child($request, 'token', $row->token);
	xml_add_child($request, 'service_notice', $row->service_notice);
	xml_add_child($request, 'account_id', $row->account_id);
}

xml_print($dom);

?>



