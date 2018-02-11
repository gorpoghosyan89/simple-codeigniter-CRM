<?php 

$this->load->helper('xml');
$dom = xml_dom();
$requests = xml_add_child($dom, 'service_requests');

function dateformat($datetime) {
	$datetime = ($datetime == '0000-00-00 00:00:00') ? '' : date("Y-m-d\TH:i:s\Z",strtotime($datetime));
	return $datetime;
}

foreach($query->result() as $row) {

	$request = xml_add_child($requests, 'request');
	
	xml_add_child($request, 'service_request_id', $row->report_id);
	xml_add_child($request, 'status', $row->status_name);
	xml_add_child($request, 'status_notes', $row->status_notes);
	//xml_add_child($request, 'service_name', $row->category_name);
	xml_add_child($request, 'service_code', $row->category_id);
	xml_add_child($request, 'description', stripslashes($row->description));
	xml_add_child($request, 'agency_responsible', $row->agency_responsible);
	xml_add_child($request, 'service_notice', $row->service_notice);
	xml_add_child($request, 'requested_datetime', dateformat($row->requested_datetime));
	xml_add_child($request, 'updated_datetime', dateformat($row->updated_datetime));
	xml_add_child($request, 'expected_datetime', dateformat($row->expected_datetime));
	xml_add_child($request, 'address', $row->address);
	xml_add_child($request, 'address_id', $row->address_id);
	xml_add_child($request, 'zipcode', $row->postal_code);
	xml_add_child($request, 'lat', $row->lat);
	xml_add_child($request, 'long', $row->long);
}

xml_print($dom);

?>



