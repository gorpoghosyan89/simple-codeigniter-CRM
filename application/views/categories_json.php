<?php 

$response = array();

foreach($categories->result() as $row) {


		$request = new stdClass();

		$request->service_code		= $row->category_id;     
		$request->metadata 			= $row->metadata;
		$request->type 				= $row->type;    
		$request->keywords 			= $row->keywords;
		$request->group 			= $row->group;	          
		$request->service_name 		= $row->category_name;						         						
		$request->description 		= $row->description;


		$response[] = $request;
}

header('Content-type: application/json');
print json_encode($response);



?>



