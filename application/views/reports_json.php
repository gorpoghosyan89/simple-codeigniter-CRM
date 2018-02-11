<?php 

function dateformat($datetime) {
	$datetime = (($datetime == '0000-00-00 00:00:00') || ($datetime == null)) ? null : date("Y-m-d\TH:i:s\Z",strtotime($datetime));
	return $datetime;
}

$response = array();

foreach($query->result() as $row) {

		$request = new stdClass();

		$request->service_request_id			= $row->report_id;               
		$request->status						= $row->status_name;            
		$request->status_notes					= $row->status_notes;           
		$request->service_code					= $row->category_id;  
		$request->description					= stripslashes($row->description);  			          
		$request->agency_responsible			= $row->agency_responsible; 						         						
		$request->service_notice				= $row->service_notice; 
		$request->requested_datetime			= dateformat($row->requested_datetime); 
		$request->updated_datetime				= dateformat($row->updated_datetime); 						         
		$request->expected_datetime				= dateformat($row->expected_datetime);      
		$request->address						= $row->address;            
		$request->address_id					= $row->address_id;     
		$request->zipcode						= $row->postal_code;              
		$request->lat							= $row->lat;       
		$request->long							= $row->long;	

		$response[] = $request;
}

header('Content-type: application/json');
print json_encode($response);

?>



