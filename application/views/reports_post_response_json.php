<?php 


$response = array();

foreach($query->result() as $row) {

	$request = new stdClass();

	$request->service_request_id 			= $row->report_id;      
	$request->token 						= $row->token;
	$request->service_notice 				= $row->service_notice;     
	$request->account_id 					= $row->account_id;

	$response[] = $request;
}


header('Content-type: application/json');
print json_encode($response);


?>



