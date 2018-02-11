<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends CI_Controller {

	function __construct() {

		parent::__construct();

		$this->config->load('fms_endpoint', FALSE, TRUE);
		/* Standard Libraries */
		$this->load->database();

		$this->load->helper('url');

		$this->load->helper('fms_endpoint');
		$this->load->library('Saml_auth');
		$this->load->library('grocery_CRUD');

		if (!$this->saml_auth->logged_in()) {
			redirect('auth/login');
		}
	}


	function index() {

		try{
			if($this->config->item('default_report_columns')){
				$default_columns = explode(',', $this->config->item('default_report_columns'));
			} else {
				$default_columns = array('report_id','status', 'requested_datetime','agency_responsible','category_id','description','updated_datetime');
			}
			$crud = $this->_set_common_report_crud($default_columns);
			$methodname = $this->router->fetch_method();
		  $state = $crud->getState();
			if($methodname == "index" && $state == "export"){
				$this->load->helper('csv');
				/*$this->db->select('report_id AS ID, status AS Status, requested_datetime AS Received, agency_responsible AS AgencyResponsible, category_id AS Category, description AS Description, updated_datetime AS LastUpdated');
				$query = $this->db->get('reports');*/
				$this->db->select('report_id AS ID, statuses.status_name AS Status');
				$this->db->select('DATE_FORMAT(requested_datetime, "%W %M %e %Y %l:%i %p") AS Received', FALSE);
				$this->db->select('agencies.name AS AgencyResponsible, categories.category_name AS Category, reports.description AS Description');
				$this->db->select('DATE_FORMAT(updated_datetime, "%W %M %e %Y %l:%i %p") AS LastUpdated', FALSE);
				$this->db->from('reports');
				$this->db->join('statuses', 'reports.status = statuses.status_id', 'left');
				$this->db->join('agencies', 'reports.agency_responsible = agencies.url_slug', 'left');
				$this->db->join('categories', 'reports.category_id = categories.category_id', 'left');
				$this->db->order_by('requested_datetime', 'DESC');
				$query = $this->db->get();
				$filename = 'export-'.date('Y-m-d_Hi') . '.csv';
				query_to_csv_ci($query, TRUE, $filename);
			}else{
				//load agency list and category List
				$querya = $this->get_agencies();
				$queryb = $this->get_categories();
				$queryc = $this->get_report_columns();

				$output = $crud->render();
				$output->agencieslist = $querya;
				$output->categorieslist = $queryb;
				$output->columnslist = $queryc;
				$this->_admin_output($output);
			}

		} catch(Exception $e) {
			show_error($e->getMessage().' --- '.$e->getTraceAsString());
		}
	}

	function reports() {
	  $crud = $this->_set_common_report_crud(array());
	  // explicitly list all fields (was missing out report-id)
	  $methodname = $this->router->fetch_method();
	  $state = $crud->getState();
	  if($methodname == "reports" && $state == "export"){
	    $this->load->helper('csv');
	    $query = $this->db->get('reports');
	    $filename = date('Y-m-d_Hi') . '_detailed_report.csv';
	    query_to_csv_ci($query, TRUE, $filename);
	  }else{
	    //$crud->callback_before_update(array($this,'_set_modified_time'));
	    $output = $crud->render();
	    $this->_admin_output($output);
	  }
	}

	// show a single report (anticipate this is for printing)
	function _set_modified_time($post_array, $primary_key) {

	}

	// show a single report (anticipate this is for printing)
	function report($id) {

		if(!$this->saml_auth->is_admin()) {
			$where_group = $this->filter_query_permissions();
		}

		$this->db->select('reports.*,
			statuses.is_closed, statuses.status_name,
			priorities.prio_name,
			categories.category_name,
			open311_clients.name AS open311_clients_name
		');
		$this->db->from('reports');
		$this->db->join('priorities', 'reports.priority = priorities.prio_value');
		$this->db->join('categories', 'reports.category_id = categories.category_id');
		$this->db->join('statuses', 'reports.status = statuses.status_id');
		$this->db->join('open311_clients', 'reports.source_client = open311_clients.id', 'left outer');
		$this->db->where('report_id', $id);

		if (!empty($where_group)) {
			$this->db->where('agency_responsible', $where_group[0]);
		}

		$query = $this->db->get();
		if ($query->num_rows()==1) {
			$image_url = $query->row()->media_url;
			if (! preg_match('/\.(gif|jpe?g|png)$/', $image_url)) {
				$image_url = false;
			}

			$report_record = $query->row();
			$report_record->description = stripcslashes($report_record->description);

			$this->load->vars(array(
				'report' => $report_record,
				'external_link' => $this->_get_external_url(null, $report_record),
				'image_url' => $image_url));
			$output = array('output' => $this->load->view('report', '', true));
			$this->_admin_output($output);
		} else {
			show_error("Record $id not found", 404);
		}
	}

	function reports_csv() {
		$this->load->helper('csv');

		if(!$this->saml_auth->is_admin()) {
			$where_group = $this->filter_query_permissions();

			if (!empty($where_group)) {
				$this->db->where('agency_responsible', $where_group[0]);
			}

		}

		$query = $this->db->get('reports');

		$filename = date('Y-m-d_Hi') . '_report.csv';
		query_to_csv_ci($query, TRUE, $filename);
	}
	function reports_dyn_csv() {
	    $this->load->helper('csv');
	    //$this->db->select('report_id AS ID, status AS Status, requested_datetime AS Received, agency_responsible AS Agency Responsible, category_id AS Category, description AS Description, updated_datetime AS Last Updated');
	    $agency =  strtolower($this->input->post('agency'));
	    $category = strtolower($this->input->post('category'));
	    $orderby = strtolower($this->input->post('orderby'));
			$startdate = strtolower($this->input->post('startdate'));
			$enddate = strtolower($this->input->post('enddate'));
	    if(!$this->saml_auth->is_admin()) {
	        $where_group = $this->filter_query_permissions();
	        if (!empty($where_group)) {
	            $agency = $where_group[0];
	        }
	    }
	    if((!empty($agency))&&($agency!="all")){
	        $this->db->where('agency_responsible', $agency);
	    }
	    if((!empty($category))&&($category!="all")){
	        $this->db->where('category_id', $category);
	    }
			if(((!empty($startdate))||($startdate!=""))&&((!empty($enddate))||($enddate!=""))){
					$this->db->where('requested_datetime >=', $startdate." 00:00:00");
					$this->db->where('requested_datetime <=', $enddate." 23:59:59");
			}
	    if((empty($orderby))||($orderby=="na")){
	        $orderby = "report_id";
	    }
	    $this->db->order_by($orderby, 'ASC');
	    $query = $this->db->get('reports');
	    $filename = date('Y-m-d_Hi') . '_advanced_report.csv';
	    query_to_csv_ci($query, TRUE, $filename);
	}

	function lastActivity(){
		//$checksession = $this->session->userdata('session_id');
		$lastactivity = $this->session->userdata('last_activity');
		$now = time();
		$activity = array(
								'lastactivity'=>$lastactivity,
								'currenttime'=>$now
							);
		$stringver = json_encode($activity);
		echo $stringver;
	}
	
	function agencies() {
		$crud = new grocery_CRUD();

		$crud->set_theme('twitter-bootstrap');

		$crud->set_table('agencies');
		$crud->required_fields('name');

		if (!($this->saml_auth->is_admin() || is_config_true($this->config->item('can_edit_agencies')))) {
			$crud->unset_delete();
			$crud->unset_add();
			$crud->unset_edit();
		}

		$crud->set_subject('Agency');
		$output = $crud->render();

		$this->_admin_output($output);
	}


	function groups() {

		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		}

		$crud = new grocery_CRUD();

		$crud->set_theme('twitter-bootstrap');

		$crud->set_table('groups');
		$crud->required_fields('name');

		if (!($this->saml_auth->is_admin() || is_config_true($this->config->item('can_edit_groups')))) {
			$crud->unset_delete();
			$crud->unset_add();
			$crud->unset_edit();
		}

		$crud->set_subject('Group');
		$output = $crud->render();

		$this->_admin_output($output);
	}

	function categories() {
		$crud = new grocery_CRUD();

		$crud->set_theme('twitter-bootstrap');

		$crud->set_table('categories');
		$crud->unset_texteditor('description'); # maybe don't unset this one
		$crud->unset_texteditor('status_notes','keywords');
		$crud->required_fields('category_id','category_name');
		$crud->set_rules('category_id', 'Category Identifier', 'trim|alpha_numeric');

		if (!($this->saml_auth->is_admin() || is_config_true($this->config->item('can_edit_categories')))) {
			$crud->unset_delete();
			$crud->unset_add();
			$crud->unset_edit();
		} else {
			$crud->callback_edit_field('keywords', array($this,'_text_keywords_field'));
		}

		$crud->set_subject('Open311 category');
		$output = $crud->render();

		$this->_admin_output($output);
	}

	function category_attributes() {
		$crud = new grocery_CRUD();

		$crud->set_theme('twitter-bootstrap');
		$crud->set_table('category_attributes');
		//$crud->set_model('attributes_join');
		$crud->set_relation('category_id','categories','category_name',null,'category_name ASC');
		$crud->display_as ( 'category_id' , "Category");

		$crud->unset_texteditor('datatype_description','description', 'values');

		$crud->field_type('order','integer');
		$crud->field_type('description','string');
		$crud->field_type('datatype_description','string');

		$crud->field_type('variable','dropdown',
			array('true' => 'True', 'false' => 'False'));
		$crud->field_type('required','dropdown',
			array('true' => 'True', 'false' => 'False'));


		$crud->field_type('datatype','dropdown',
			array(  'string' => 'Text',
					'text' => 'Textbox',
					'number' => 'Number',
					'datetime' => 'Date Picker',
					'singlevaluelist' => 'Dropdown Menu',
					'multivaluelist' => 'Multi-select List'));


		$crud->required_fields('category_id', 'attribute_id','variable', 'datatype', 'required', 'description', 'order', 'description');


		if (!($this->saml_auth->is_admin() || is_config_true($this->config->item('can_edit_categories')))) {
			$crud->unset_delete();
			$crud->unset_add();
			$crud->unset_edit();
		} else {
			$crud->callback_edit_field('keywords', array($this,'_text_keywords_field'));
		}

		$crud->set_subject('Open311 Service Definition');
		$output = $crud->render();

		$this->_admin_output($output);
	}



	function request_updates() {

		if (!$this->saml_auth->is_admin()) {
			//redirect them to the home page because they must be an administrator to view this
			redirect($this->config->item('base_url'), 'refresh');
		}

		$crud = new grocery_CRUD();
		$crud->set_theme('twitter-bootstrap');
		$crud->set_table('request_updates');
		$crud->unset_texteditor('update_desc');
		if (!($this->saml_auth->is_admin())) {
			$crud->unset_delete();
			$crud->unset_edit();
			$where_group = $this->filter_query_permissions();
		}

		$crud->unset_add(); // disabled: should only be created by editing a report
		$crud->columns('id', 'report_id', 'is_outbound', 'status_id', 'updated_at','update_desc', 'old_status_id', 'external_update_id');
		$crud->set_subject('Service request updates');
		$crud->set_relation('status_id','statuses',
			'<span class="fmse-status-{is_closed}">{status_name}</span>',null,'status_name ASC');
		$crud->callback_column('report_id', array($this, '_report_id_link_field'));
		$crud->callback_column('is_outbound', array($this, '_yes_no_field'));
		$crud->display_as('id', 'Update ID');
		$crud->display_as('report_id', 'Report');
		$crud->display_as('old_status_id', 'Old status');
		$crud->display_as('status_id', 'New status');
		$crud->display_as('update_desc', 'Description of update');
		$crud->display_as('is_outbound', 'Outbound?');
		$crud->display_as('remote_update_id', 'Remote<br/>update id');

		$output = $crud->render();

		$this->_admin_output($output);
	}

	function settings() {
		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		} else {
			$crud = new grocery_CRUD();
			$crud->set_theme('twitter-bootstrap'); /* text wraps for the long descriptions in databables, not flexigrid */
			$crud->set_table('config_settings');
			$crud->display_as('desc', 'Explanation');
			$crud->callback_column('desc', array($this, '_full_description'));
			$crud->unset_texteditor('name','value');
			$crud->edit_fields('name', 'desc', 'value');
			$crud->callback_edit_field('value', array($this,'_text_value_field'));  // the default (textarea) is too big for any current setttings
			$crud->callback_edit_field('name', array($this,'_read_only_name_field'));  // read-only during edit
			$crud->callback_edit_field('desc', array($this,'_read_only_desc_field'));  // read-only during edit
			$crud->set_subject("configuration setting");
			//$crud->unset_jquery();
			$output = $crud->render();
			$this->_admin_output($output);
		}
	}

	function statuses() {
		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		} else {
			$crud = new grocery_CRUD();
			$crud->set_theme('twitter-bootstrap');
			$crud->set_table('statuses');
			$crud->set_subject("problem status");
			$crud->unset_texteditor('description');
			$output = $crud->render();
			$this->_admin_output($output);
		}
	}

	function api_keys() {
		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		} else {
			$crud = new grocery_CRUD();
			$crud->set_theme('twitter-bootstrap');
			$crud->set_table('api_keys');
			$crud->set_subject("API key");
			$crud->set_relation('client_id','open311_clients',
				'<a href="/admin/open311_clients/{id}">{name}</a>', null,'name ASC');
			$crud->display_as('client_id', 'Client');
			$crud->unset_texteditor('notes');
			$output = $crud->render();
			$this->_admin_output($output);
		}
	}

	function open311_clients() {
		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		} else {
			$crud = new grocery_CRUD();
			$crud->set_theme('twitter-bootstrap');
			$crud->set_table('open311_clients');
			$crud->set_subject("Open311 client");
			$crud->unset_texteditor('notes','client_url');
			$crud->callback_edit_field('client_url', array($this,'_text_client_url_field'));
			$output = $crud->render();
			$this->_admin_output($output);
		}
	}

	function spam() {

		if (!$this->saml_auth->is_admin()) {
			redirect('admin/');
		} else {
			$this->load->file('./application/libraries/Akismet.php');
			$selection = $this->input->post("bulk_spam_filter", TRUE);
			$output = array();


			if($selection == 'mark_all_new_as_spam') {

				$count = 0;

				$this->db->select('reports.*', FALSE);
				$this->db->select('statuses.status_name, statuses.status_name AS status_name', FALSE);

				$this->db->where('status_name', 'new');
				$this->db->join('statuses', 'reports.status = statuses.status_id');

				$query = $this->db->get('reports');

				foreach ($query->result() as $result) {

					// Mark as spam
					if($this->config->item('akismet_key')) {

						$wordPressAPIKey = $this->config->item('akismet_key');
						$blogURL = $this->config->item('akismet_siteurl');

						$akismet = new Akismet($blogURL ,$wordPressAPIKey);
						$akismet->setCommentAuthor($result->first_name . ' ' . $result->last_name);
						$akismet->setCommentAuthorEmail($result->email);
						$akismet->setCommentContent($result->description);

						$akismet->submitSpam();
					}

					// Delete the report
					$this->db->delete('reports', array('report_id' => $result->report_id));
					$count++;
				}

				$output['count'] = $count;
				$output['notice'] = $count . " new messages have been marked as spam";
			}

			$this->load->view('spam.php', $output);
		}
	}

	function about() {
		$output = array('output' => $this->load->view('about', '', true));
		$this->load->view('admin_view.php', $output);
	}

	function help() {
		$output = array('output' => $this->load->view('help', '', true));
		$this->load->view('admin_view.php', $output);
	}

	function open311() {
		$output = array('output' => $this->load->view('open311', '', true));
		$this->load->view('admin_view.php', $output);
	}

	// There is some magic here: using xxx_report_id because running callback_column directly
	// on report_id breaks other field renders (such as actions) that contain report_id.
	function _set_common_report_crud($columns) {
		$crud = new grocery_CRUD();

		// default columns excludes: token address_id simply because FMS/FMS-endpoint doesn't use them
		$default_columns = array('report_id',
								 'status',
								 'requested_datetime',
								 'updated_datetime',
								 'expected_datetime',
								 'priority',
								 'category_id',
								 'media_url',
								 'status_notes',
								 'description',
								 'agency_responsible',
								 'service_notice',
								 'address',
								 'postal_code',
								 'lat', 'long',
								 'device_id',
								 'source_client',
								 'account_id',
								 'first_name',
								 'last_name',
								 'email',
								 'phone',
								 'engineer');
		$columns = $columns? $columns : $default_columns;
		foreach ($columns as &$colname) {
			if ($colname == 'report_id') {
				$colname = 'xxx_report_id';
			}
		}
		$crud->columns($columns);
		$crud->order_by('requested_datetime','desc');
		$crud->edit_fields($default_columns);
		$crud->set_theme('twitter-bootstrap');
		$crud->set_table('reports');
		$crud->set_subject('Report');

		$crud->set_relation('category_id','categories','category_name',null,'category_name ASC');
		$crud->set_relation('priority','priorities','prio_name',null,'prio_value ASC');
		$crud->set_relation('status','statuses','status_name',null,'status_name ASC');

		$crud->set_primary_key('url_slug','agencies');
		$crud->set_relation('agency_responsible','agencies','name',null,'name ASC');

		$crud->set_relation('source_client','open311_clients',
			'<a href="admin/open311_clients/{id}">{name}</a>', null,'name ASC');

		$crud->display_as('client_id', 'Client');
		$crud->callback_column('media_url',array($this,'_linkify'));
		$crud->callback_column('external_id', array($this, '_get_external_url'));
		$crud->callback_edit_field('media_url', array($this,'_text_media_url_field'));

		$crud->display_as('requested_datetime', 'Received')
			->display_as('updated_datetime', 'Last Updated')
			->display_as('expected_datetime', 'Expected')
			->display_as('category_id', 'Category')
			->display_as('media_url', 'Media URL');
		$external_id_col_name = config_item('external_id_col_name');
		$crud->display_as('external_id', empty($external_id_col_name)?'External ID':$external_id_col_name);
		$crud->unset_texteditor('description', 'address', 'status_notes', 'service_notice');
		$crud->add_action('View', site_url('assets/fms-endpoint/images/report.png'), 'admin/report');

		$crud->callback_column('xxx_report_id', array($this, '_report_id_link_field'));
		$crud->callback_column('requested_datetime', array($this, '_report_datetime_field'));
		$crud->callback_column('updated_datetime', array($this, '_report_datetime_field'));
		$crud->callback_column('description', array($this, '_report_description_field'));
		$crud->callback_column($this->unique_field_name('status'), array($this, '_report_status_button'));
		$crud->callback_column($this->unique_field_name('category_id'), array($this, '_report_category_button'));

		$crud->display_as('xxx_report_id', 'ID');
		$crud->callback_edit_field('xxx_report_id', array($this, '_read_only_report_id_field'));

		//$crud->fields($default_columns);
		//$crud->field_type('updated_datetime','invisible');
		//$crud->callback_before_update(function($post_array){
		//    $post_array['updated_datetime'] = date('Y-m-d H:i:s');
		//    return $post_array;
		//});

		$crud->callback_field('updated_datetime',array($this, '_set_update_time'));
		$crud->callback_field('requested_datetime',array($this, '_set_requested_datetime'));

		$crud->field_type('report_id','readonly');

		$crud->callback_before_update(array($this,'_fix_zero_prio_callback'));
		$crud->callback_after_update(array($this, '_check_for_status_update_after'));

		if (!$this->saml_auth->is_admin()) {
			$crud->unset_delete();
		}

		// If we're not an admin restrict list to reports we have permissions for
		if(!$this->saml_auth->is_admin()) {
			return $this->filter_query_permissions($crud);
		} else {
			return $crud;
		}

	}

	function filter_query_permissions($crud = null) {
		return $crud;
		$user_groups = $this->saml_auth->get_users_groups()->result();
		$where_group = array();
		foreach ($user_groups as $user_group) {
			if ($user_group->name !== 'members') {
				$where_group[] = $user_group->name;
			}
		}
		if (!empty($where_group)) {
			if(!empty($crud)) {
				$crud->where('agency_responsible', $where_group[0]);
				return $crud;
			} else {
				return $where_group;
			}
		} else {
			if(!empty($crud)) {
				$crud->where('agency_responsible', '');
				return $crud;
			}
		}
	}

	function delete_selection() {
	   $id_array = array();
	   $selection = $this->input->post("selection", TRUE);
	   $id_array = explode("|", $selection);

	   foreach($id_array as $item) {
		  if($item != '') {
			$this->db->where('report_id', $item);
			$this->db->delete('reports');
		  }
	   }
	}

	function unique_field_name($field_name) {
		return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not with a number
	}

	function get_agencies()
	{
	    $this->db->select('name, url_slug');
	    $this->db->from('agencies');
	    $query = $this->db->get();
	    return $query;
	}
	function get_categories()
	{
	    $this->db->select('category_name, category_id');
	    $this->db->from('categories');
	    $query = $this->db->get();
	    return $query;
	}
	function get_report_columns()
	{
	    return $this->db->list_fields('reports');
	}
	function _set_update_time($value, $primary_key){
		$current_value   = (!empty($value)) ? date('l F j, Y \a\t g:i a', strtotime($value)) : 'No updates yet';
		$timestamp_field = "<input id='updated_datetime' name='updated_datetime' type='hidden' value='".date('Y-m-d H:i:s')."' />" . $current_value;
		return $timestamp_field;
	}

	function _set_requested_datetime($value, $primary_key){
		$timestamp_field = "<input id='$primary_key' name='$primary_key' type='hidden' value='$value' />" . date('l F j, Y \a\t g:i a', strtotime($value));
		return $timestamp_field;
	}

	// force the default priority (0) since the groceryCRUD drop-down for priority doesn't
	// seem to auto-select an option if it's zero... hence it's returned from the form as
	// NULL
	function _fix_zero_prio_callback($post_array, $primary_key) {
		if ($post_array['priority'] == null) {
			$post_array['priority'] = FMSE_DEFAULT_REPORT_PRIORITY;
		}

		// get the current status (from the db) and store it in $post_array, cos we'll check it
		// immediately after the save to see if it's changed
		$this_record = $this->db->get_where('reports', array('report_id' => $primary_key))->row();
		$post_array['old_status']=$this_record->status; // for detecting status changes
		return $post_array;
	}

	function _admin_output($output = null) {
		if(array_key_exists("agencieslist", $output)){
			$output->currmethod = $this->router->fetch_method();
		}
		$this->load->view('admin_view.php', $output);
	}

	// field button
	function _report_status_button($value, $row) {

		if ($value == "new") {
			$class = "btn-danger";
		} else if ($value == "open") {
			$class = "btn-primary";
		} else if ($value == "closed") {
			$class = "btn-success";
		} else {
			$class = "btn-default";
		}

		return '<span class="btn ' . $class . '">' . $value . '</span>';
	}

	// category type
	function _report_category_button($value, $row) {

		if ($value == "New Data Request") {
			$class = "btn-info";
		} else if ($value == "New Data Issue") {
			$class = "btn-warning";
		} else {
			$class = "btn-default";
		}

		return '<span class="btn ' . $class . '">' . $value . '</span>';
	}

	// make the ID a link to the report
	function _report_id_link_field($value, $row) {
		$rid = $row->report_id;
		return '<a href="' . site_url("admin/report/$rid" ) . '" class="report-id-link">' . $rid . "</a>";
	}

	// change date format
	function _report_datetime_field($value, $row) {
		$datetime = $value;
		if(!empty($value)) {
			return date('M j - g:ia', strtotime($value));
		} else {
			return '';
		}

	}

	// format description field
	function _report_description_field($value, $row) {
		if(!empty($value)) {
			return htmlentities(stripcslashes($value));
		} else {
			return '';
		}

	}


	function _read_only_report_id_field($value, $primary_key) {
		return '<input type="hidden" value="' . $value . '" ' . ' name="' . $primary_key . '"/>' . $value;
	}
	function _read_only_name_field($value, $primary_key) { return $this->_read_only_field('name', $value); }
	function _read_only_desc_field($value, $primary_key) { return $this->_read_only_field('desc', $value); }
	function _read_only_updated_datetime($value, $primary_key) { return $this->_read_only_field('updated_datetime', $value); }
	function _read_only_requested_datetime($value, $primary_key) { return $this->_read_only_field('requested_datetime', $value); }

	function _read_only_field($name, $value) {
		return '<input type="hidden" value="' . $value . '" name="' . $name . '"/>' . $value;
	}

	function _text_value_field($value, $primary_key) { return $this->_text_field('value', $value); }
	function _text_media_url_field($value, $primary_key) { return $this->_text_field('media_url', $value); }
	function _text_client_url_field($value, $primary_key) { return $this->_text_field('client_url', $value); }
	function _text_keywords_field($value, $primary_key) { return $this->_text_field('keywords', $value); }
	function _text_field($name, $value) {
		return '<input class="form-control" type="text" value="' . $value . '" name="' . $name . '"/>';
	}
	function _yes_no_field($value, $primary_key) { return ($value? "yes" : "no"); }

	// turn the value (assumed to be a good URL) into a link
	// class of link varies if it looks like this is a fixmystreet-like link, heheh
	function _linkify($url, $row, $link_text='link') {
		$retval = '';
		if ($url) {
			$css_class = (preg_match('/https?:\/\/(\\w*\\.)*fixmy/', $url))? 'fmse-web-link-fms':'fmse-web-link';
			$retval = '<a href="' . $url . '" class="' . $css_class . '" target="_blank">' . $link_text . '</a>';
		}
		return $retval;
	}

	function _full_description($value, $row) {
		return $value = wordwrap($row->desc, strlen($row->desc), "<br>", true);
	}

	function _get_external_url($value, $row) {
		$client_lookup = $this->db->get_where("open311_clients", array('id' => $row->source_client));
		$url = '';
		if ($client_lookup->num_rows()==0) {
			return "";
		} else if (!empty($row->external_id)) {
			$url = $client_lookup->row()->client_url;
			$url = preg_replace('/%id%/', $row->external_id, $url);
			$url = $this->_linkify($url, $row, $row->external_id);
		}
		return $url;
	}

	function _check_for_status_update_after($post_array,$primary_key) {
		if ($post_array['old_status'] != $post_array['status']) {
			// create a status change record
			$desc = "Status changed";
			$status_lookup = $this->db->get_where("statuses", array('status_id' => $post_array['status']));
			if ($status_lookup->num_rows()==1) {
				$desc = "Marked as \"" . $status_lookup->row()->status_name . '"';
			} // else fail silently?
			$org = $this->config->item('organisation_name');
			if  (! empty($org) ) {
				$desc = "$desc by $org";
			}
			$request_update = array(
				'report_id' => $primary_key,
				'status_id' => $post_array['status'],
				'old_status_id' => $post_array['old_status'],
				'changed_by' => 0, // TODO this should be user ID
				// 'changed_by_name' => ? // TODO this should be user email, etc
				'is_outbound' => 1, // this is an *outbound* update, because we're sending it out to the client
				'update_desc' => $desc //for now, don't send FMS empty descriptions, because it doesn't digest them nicely
			);
			$this->db->insert('request_updates', $request_update);
		}
		return true;
	}
}

?>
