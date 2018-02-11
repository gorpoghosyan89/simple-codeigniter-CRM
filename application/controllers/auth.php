<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->library('saml_auth');
        $this->load->library('session');
        $this->load->library('form_validation');
        $this->load->database();
        $this->load->helper('url');
    }

    public function login()
    {
        if ('development' == ENVIRONMENT) {
            return $this->loginDev();
        }
        $as = new SimpleSAML_Auth_Simple('max');
        $as->requireAuth(array(
            'saml:AuthnContextClassRef' => 'https://max.gov/icam/2015/10/securityLevels/securePlus2',
        ));

        $attributes = $as->getAttributes();

        $userdata = array();
        $userdata['username'] = $attributes['maxFirstName'][0] . ' ' . $attributes['maxLastName'][0];
        $userdata['email'] = $attributes['maxEmail'][0];
        $userdata['name_full'] = $attributes['maxFirstName'][0] . ' ' . $attributes['maxLastName'][0];
        $userdata['first_name'] = $attributes['maxFirstName'][0];
        $userdata['last_name'] = $attributes['maxLastName'][0];
        $userdata['pre_approved_admin'] = false;


        $pre_approved_admins = $this->config->item('pre_approved_admins');

        if (in_array($userdata['email'], $pre_approved_admins)) {
            $userdata['pre_approved_admin'] = true;
        }

        $userdata['provider_url'] = 'max.gov';

        if ($this->saml_auth->login(array_merge($userdata, $attributes))) {
            redirect('admin');
            return;
        }

        redirect('/');
    }

    public function loginDev()
    {
        $userdata = array(
            'username' => 'Dev Smith',
            'email' => 'dev@data.gov',
            'first_name' => 'Dev',
            'last_name' => 'Smith',
            'pre_approved_admin' => true,
            'provider_url' => 'dev'
        );
        if ($this->saml_auth->login($userdata)) {
            redirect('admin');
            return;
        }

        redirect('/');
    }

    public function acs()
    {
        $_SERVER['PATH_INFO'] = '/max';
        include(dirname(dirname(__DIR__)) . '/vendor/simplesamlphp/simplesamlphp/modules/saml/www/sp/saml2-acs.php');
    }

    public function logout()
    {

        $this->session->sess_destroy();
        redirect('/');
    }

    //redirect if needed, otherwise display the user list
    function index()
    {

        if (!$this->saml_auth->logged_in()) {
            //redirect them to the login page
            redirect('auth/login', 'refresh');
        } elseif (!$this->saml_auth->is_admin()) {
            //redirect them to the home page because they must be an administrator to view this
            redirect($this->config->item('base_url'), 'refresh');
        } else {
            //set the flash data error message if there is one
            $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

            //list the users
            $this->data['users'] = $this->saml_auth->order_by('last_login')->users()->result();
            $admin_group = $this->saml_auth->where('name', 'admin')->group()->row();
            $users = $admins = array();

            foreach ($this->data['users'] as $k => $user) {
//                $this->data['users'][$k]->groups = $this->saml_auth->get_users_groups($user->id)->result();
                $user->groups = $this->saml_auth->get_users_groups($user->id)->result();
                if (in_array($admin_group, $user->groups)) {
                    $user->is_admin = true;
                    $admins[] = $user;
                } else {
                    $user->is_admin = false;
                    $users[] = $user;
                }
            }
            $this->data['users'] = array_merge($admins, $users);

            $this->load->view('auth/index', $this->data);
        }
    }

    //create a new user
    function edit_user($id = null)
    {
        return $this->manage_user('edit', $id);
    }

    //create a new user
    function manage_user($action = 'create', $id = null)
    {

        $this->data['action'] = $action;
        $this->data['title'] = ucfirst($action) . ' User';

        if (!$this->saml_auth->logged_in() || !$this->saml_auth->is_admin()) {
            redirect('auth', 'refresh');
        }

        // If we're editing, load existing data
        if ($action == 'edit' && (!empty($id) OR $id == '0')) {
            $user_data = (array)$this->saml_auth->user($id)->row();
            $user = array();

            foreach ($user_data as $user_field => $user_value) {
                $user[$user_field] = array(
                    'name' => $user_field,
                    'id' => $user_field,
                    'type' => 'text',
                    'value' => $user_value
                );
            }

            $user['user_groups'] = $this->saml_auth->get_users_groups($id)->result();
            $user['all_groups'] = $this->saml_auth->groups()->result();
            $this->data = array_merge($this->data, $user);
            return $this->load->view('auth/manage_user', $this->data);
        }

        $id = ($this->input->post('user_id')) ? $this->input->post('user_id') : $id;
        $action = ($this->input->post('action')) ? $this->input->post('action') : $action;

        //validate form input
        $this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
//        $this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');

//		if ($action == 'create') {
//			$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'saml_auth') . ']|max_length[' . $this->config->item('max_password_length', 'saml_auth') . ']|matches[password_confirm]');
//			$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');
//		}

        if ($this->form_validation->run() == true) {
            $username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
//            $email = $this->input->post('email');

            if ($action == 'create') {
//                $password = $this->input->post('password');
            } else {
                $new_groups = $this->input->post('groups');
            }

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'company' => $this->input->post('company'),
//                'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
            );

        }

        // If we're receiving an update for an existing record
        if ($this->form_validation->run() == true && $action == 'update') {

            // run a diff on existing groups
            $previous_groups = $this->saml_auth->get_users_groups($id)->result();
            foreach ($previous_groups as $previous_group) {

                // remove missing groups
                if (empty($new_groups[$previous_group->id])) {
                    // remove this group
                    $this->saml_auth->remove_from_group($previous_group->id, $id);
                } else {
                    unset($new_groups[$previous_group->id]);
                }

            }

            // add new groups - for some reason add_to_group isn't working with a single bulk operation using an array
            if (!empty($new_groups)) {
                $new_group_ids = array_keys($new_groups);
                foreach ($new_group_ids as $new_group_id) {
                    $this->saml_auth->add_to_group($new_group_id, $id);
                }
            }

            $additional_data['username'] = $username;
//            $additional_data['email'] = $email;

            $this->saml_auth->update($id, $additional_data);
            $this->session->set_flashdata('message', "<p>User Updated</p>");
            redirect("auth", 'refresh');
            exit;

        }


//		if ($this->form_validation->run() == true && $this->saml_auth->register($username, $password, $email, $additional_data))
//		{ //check to see if we are creating the user
//			//redirect them back to the admin page
//			$this->session->set_flashdata('message', "<p>User Created</p>");
//			redirect("auth", 'refresh');
//			exit;
//		}

        //display the create user form
        //set the flash data error message if there is one
        $this->data['message'] = (validation_errors() ? validation_errors() : ($this->saml_auth->errors() ? $this->saml_auth->errors() : $this->session->flashdata('message')));

        $this->data['first_name'] = array('name' => 'first_name',
            'id' => 'first_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('first_name'),
        );
        $this->data['last_name'] = array('name' => 'last_name',
            'id' => 'last_name',
            'type' => 'text',
            'value' => $this->form_validation->set_value('last_name'),
        );
        $this->data['email'] = array('name' => 'email',
            'id' => 'email',
            'type' => 'text',
            'value' => $this->form_validation->set_value('email'),
        );
        $this->data['company'] = array('name' => 'company',
            'id' => 'company',
            'type' => 'text',
            'value' => $this->form_validation->set_value('company'),
        );
        $this->data['phone1'] = array('name' => 'phone1',
            'id' => 'phone1',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone1'),
        );
        $this->data['phone2'] = array('name' => 'phone2',
            'id' => 'phone2',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone2'),
        );
        $this->data['phone3'] = array('name' => 'phone3',
            'id' => 'phone3',
            'type' => 'text',
            'value' => $this->form_validation->set_value('phone3'),
        );
        $this->data['password'] = array('name' => 'password',
            'id' => 'password',
            'type' => 'password',
            'value' => $this->form_validation->set_value('password'),
        );
        $this->data['password_confirm'] = array('name' => 'password_confirm',
            'id' => 'password_confirm',
            'type' => 'password',
            'value' => $this->form_validation->set_value('password_confirm'),
        );
        $this->load->view('auth/manage_user', $this->data);

    }

//
//	//log the user in
//	function login()
//	{
//		$this->data['title'] = "Login";
//
//		//validate form input
//		$this->form_validation->set_rules('identity', 'Identity', 'required');
//		$this->form_validation->set_rules('password', 'Password', 'required');
//
//		if ($this->form_validation->run() == true)
//		{ //check to see if the user is logging in
//			//check for "remember me"
//			$remember = (bool) $this->input->post('remember');
//
//			if ($this->saml_auth->login($this->input->post('identity'), $this->input->post('password'), $remember))
//			{ //if the login is successful
//				//redirect them back to the home page
//				$this->session->set_flashdata('message', $this->saml_auth->messages());
//				redirect('admin', 'location');
//			}
//			else
//			{ //if the login was un-successful
//				//redirect them back to the login page
//				$this->session->set_flashdata('message', $this->saml_auth->errors());
//				redirect('auth/login', 'location'); //use redirects instead of loading views for compatibility with MY_Controller libraries
//			}
//		}
//		else
//		{  //the user is not logging in so display the login page
//			//set the flash data error message if there is one
//			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
//
//			$this->data['identity'] = array('name' => 'identity',
//				'id' => 'identity',
//				'type' => 'text',
//				'value' => $this->form_validation->set_value('identity'),
//			);
//			$this->data['password'] = array('name' => 'password',
//				'id' => 'password',
//				'type' => 'password',
//			);
//
//			$this->load->view('auth/login', $this->data);
//		}
//	}
//
//	//log the user out
//	function logout()
//	{
//		$this->data['title'] = "Logout";
//
//		//log the user out
//		$logout = $this->saml_auth->logout();
//
//		//redirect them back to the page they came from
//		redirect('auth', 'refresh');
//	}
//
//	//change password
//	function change_password()
//	{
//		$this->form_validation->set_rules('old', 'Old password', 'required');
//		$this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'saml_auth') . ']|max_length[' . $this->config->item('max_password_length', 'saml_auth') . ']|matches[new_confirm]');
//		$this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');
//
//		if (!$this->saml_auth->logged_in())
//		{
//			redirect('auth/login', 'refresh');
//		}
//
//		$user = $this->saml_auth->user()->row();
//
//		if ($this->form_validation->run() == false)
//		{ //display the form
//			//set the flash data error message if there is one
//			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
//
//			$this->data['min_password_length'] = $this->config->item('min_password_length', 'saml_auth');
//			$this->data['old_password'] = array(
//				'name' => 'old',
//				'id'   => 'old',
//				'type' => 'password',
//			);
//			$this->data['new_password'] = array(
//				'name' => 'new',
//				'id'   => 'new',
//				'type' => 'password',
//				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
//			);
//			$this->data['new_password_confirm'] = array(
//				'name' => 'new_confirm',
//				'id'   => 'new_confirm',
//				'type' => 'password',
//				'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
//			);
//			$this->data['user_id'] = array(
//				'name'  => 'user_id',
//				'id'    => 'user_id',
//				'type'  => 'hidden',
//				'value' => $user->id,
//			);
//
//			//render
//			$this->load->view('auth/change_password', $this->data);
//		}
//		else
//		{
//			$identity = $this->session->userdata($this->config->item('identity', 'saml_auth'));
//
//			$change = $this->saml_auth->change_password($identity, $this->input->post('old'), $this->input->post('new'));
//
//			if ($change)
//			{ //if the password was successfully changed
//				$this->session->set_flashdata('message', $this->saml_auth->messages());
//				$this->logout();
//			}
//			else
//			{
//				$this->session->set_flashdata('message', $this->saml_auth->errors());
//				redirect('auth/change_password', 'refresh');
//			}
//		}
//	}
//
//	//forgot password
//	function forgot_password()
//	{
//		$this->form_validation->set_rules('email', 'Email Address', 'required');
//		if ($this->form_validation->run() == false)
//		{
//			//setup the input
//			$this->data['email'] = array('name' => 'email',
//				'id' => 'email',
//			);
//			//set any errors and display the form
//			$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
//			$this->load->view('auth/forgot_password', $this->data);
//		}
//		else
//		{
//			//run the forgotten password method to email an activation code to the user
//			$forgotten = $this->saml_auth->forgotten_password($this->input->post('email'));
//
//			if ($forgotten)
//			{ //if there were no errors
//				$this->session->set_flashdata('message', $this->saml_auth->messages());
//				redirect("auth/login", 'refresh'); //we should display a confirmation page here instead of the login page
//			}
//			else
//			{
//				$this->session->set_flashdata('message', $this->saml_auth->errors());
//				redirect("auth/forgot_password", 'refresh');
//			}
//		}
//	}
//
//	//reset password - final step for forgotten password
//	public function reset_password($code)
//	{
//		$user = $this->saml_auth->forgotten_password_check($code);
//
//		if ($user)
//		{  //if the code is valid then display the password reset form
//
//			$this->form_validation->set_rules('new', 'New Password', 'required|min_length[' . $this->config->item('min_password_length', 'saml_auth') . ']|max_length[' . $this->config->item('max_password_length', 'saml_auth') . ']|matches[new_confirm]');
//			$this->form_validation->set_rules('new_confirm', 'Confirm New Password', 'required');
//
//			if ($this->form_validation->run() == false)
//			{//display the form
//				//set the flash data error message if there is one
//				$this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
//
//				$this->data['min_password_length'] = $this->config->item('min_password_length', 'saml_auth');
//				$this->data['new_password'] = array(
//					'name' => 'new',
//					'id'   => 'new',
//				'type' => 'password',
//					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
//				);
//				$this->data['new_password_confirm'] = array(
//					'name' => 'new_confirm',
//					'id'   => 'new_confirm',
//					'type' => 'password',
//					'pattern' => '^.{'.$this->data['min_password_length'].'}.*$',
//				);
//				$this->data['user_id'] = array(
//					'name'  => 'user_id',
//					'id'    => 'user_id',
//					'type'  => 'hidden',
//					'value' => $user->id,
//				);
//				$this->data['csrf'] = $this->_get_csrf_nonce();
//				$this->data['code'] = $code;
//
//				//render
//				$this->load->view('auth/reset_password', $this->data);
//			}
//			else
//			{
//				// do we have a valid request?
//				if ($this->_valid_csrf_nonce() === FALSE || $user->id != $this->input->post('user_id')) {
//
//					//something fishy might be up
//					$this->saml_auth->clear_forgotten_password_code($code);
//
//					show_404();
//
//				} else {
//					// finally change the password
//					$identity = $user->{$this->config->item('identity', 'saml_auth')};
//
//					$change = $this->saml_auth->reset_password($identity, $this->input->post('new'));
//
//					if ($change)
//					{ //if the password was successfully changed
//						$this->session->set_flashdata('message', $this->saml_auth->messages());
//						$this->logout();
//					}
//					else
//					{
//						$this->session->set_flashdata('message', $this->saml_auth->errors());
//						redirect('auth/reset_password/' . $code, 'refresh');
//					}
//				}
//			}
//		}
//		else
//		{ //if the code is invalid then send them back to the forgot password page
//			$this->session->set_flashdata('message', $this->saml_auth->errors());
//			redirect("auth/forgot_password", 'refresh');
//		}
//	}
//
//
//	//activate the user
//	function activate($id, $code=false)
//	{
//		if ($code !== false)
//			$activation = $this->saml_auth->activate($id, $code);
//		else if ($this->saml_auth->is_admin())
//			$activation = $this->saml_auth->activate($id);
//
//		if ($activation)
//		{
//			//redirect them to the auth page
//			$this->session->set_flashdata('message', $this->saml_auth->messages());
//			redirect("auth", 'refresh');
//		}
//		else
//		{
//			//redirect them to the forgot password page
//			$this->session->set_flashdata('message', $this->saml_auth->errors());
//			redirect("auth/forgot_password", 'refresh');
//		}
//	}
//
//	//deactivate the user
//	function deactivate($id = NULL)
//	{
//		// no funny business, force to integer
//		$id = (int) $id;
//
//		$this->load->library('form_validation');
//		$this->form_validation->set_rules('confirm', 'confirmation', 'required');
//		$this->form_validation->set_rules('id', 'user ID', 'required|is_natural');
//
//		if ($this->form_validation->run() == FALSE)
//		{
//			// insert csrf check
//			$this->data['csrf'] = $this->_get_csrf_nonce();
//			$this->data['user'] = $this->saml_auth->user($id)->row();
//
//			$this->load->view('auth/deactivate_user', $this->data);
//		}
//		else
//		{
//			// do we really want to deactivate?
//			if ($this->input->post('confirm') == 'yes')
//			{
//				// do we have a valid request?
//				if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id'))
//				{
//					show_404();
//				}
//
//				// do we have the right userlevel?
//				if ($this->saml_auth->logged_in() && $this->saml_auth->is_admin())
//				{
//					$this->saml_auth->deactivate($id);
//				}
//			}
//
//			//redirect them back to the auth page
//			redirect('auth', 'refresh');
//		}
//	}
//
//	//create a new user
//	function edit_user($id = null) {
//		return $this->manage_user('edit', $id);
//	}
//
//	//create a new user
//	function create_user() {
//		return $this->manage_user('create');
//	}
//
//	//create a new user
//	function manage_user($action = 'create', $id = null) {
//
//		$this->data['action'] = $action;
//		$this->data['title'] = ucfirst($action) . ' User';
//
//		if (!$this->saml_auth->logged_in() || !$this->saml_auth->is_admin())
//		{
//			redirect('auth', 'refresh');
//		}
//
//		// If we're editing, load existing data
//		if ($action == 'edit' && (!empty($id) OR $id == '0')) {
//			$user_data = (array) $this->saml_auth->user($id)->row();
//			$user = array();
//
//			foreach ($user_data as $user_field => $user_value) {
//				$user[$user_field] =  array(
//											'name' => $user_field,
//											'id' => $user_field,
//											'type' => 'text',
//											'value' => $user_value
//											);
//			}
//
//			$user['user_groups'] = $this->saml_auth->get_users_groups($id)->result();
//			$user['all_groups'] = $this->saml_auth->groups()->result();
//			$this->data = array_merge($this->data, $user);
//			return $this->load->view('auth/manage_user', $this->data);
//		}
//
//		$id 	= ($this->input->post('user_id')) ? $this->input->post('user_id') : $id;
//		$action = ($this->input->post('action')) ? $this->input->post('action') : $action;
//
//		//validate form input
//		$this->form_validation->set_rules('first_name', 'First Name', 'required|xss_clean');
//		$this->form_validation->set_rules('last_name', 'Last Name', 'required|xss_clean');
//		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email');
//
//		if ($action == 'create') {
//			$this->form_validation->set_rules('password', 'Password', 'required|min_length[' . $this->config->item('min_password_length', 'saml_auth') . ']|max_length[' . $this->config->item('max_password_length', 'saml_auth') . ']|matches[password_confirm]');
//			$this->form_validation->set_rules('password_confirm', 'Password Confirmation', 'required');
//		}
//
//		if ($this->form_validation->run() == true)
//		{
//			$username = strtolower($this->input->post('first_name')) . ' ' . strtolower($this->input->post('last_name'));
//			$email = $this->input->post('email');
//
//			if ($action == 'create') {
//				$password = $this->input->post('password');
//			} else {
//				$new_groups = $this->input->post('groups');
//			}
//
//			$additional_data = array('first_name' => $this->input->post('first_name'),
//				'last_name' => $this->input->post('last_name'),
//				'company' => $this->input->post('company'),
//				'phone' => $this->input->post('phone1') . '-' . $this->input->post('phone2') . '-' . $this->input->post('phone3'),
//			);
//
//		}
//
//		// If we're receiving an update for an existing record
//		if ($this->form_validation->run() == true && $action == 'update') {
//
//			// run a diff on existing groups
//			$previous_groups = $this->saml_auth->get_users_groups($id)->result();
//			foreach ($previous_groups as $previous_group) {
//
//				// remove missing groups
//				if(empty($new_groups[$previous_group->id])) {
//					// remove this group
//					$this->saml_auth->remove_from_group($previous_group->id, $id);
//				} else {
//					unset($new_groups[$previous_group->id]);
//				}
//
//			}
//
//			// add new groups - for some reason add_to_group isn't working with a single bulk operation using an array
//			if (!empty($new_groups)) {
//				$new_group_ids = array_keys($new_groups);
//				foreach ($new_group_ids as $new_group_id) {
//					$this->saml_auth->add_to_group($new_group_id, $id);
//				}
//			}
//
//			$additional_data['username'] = $username;
//			$additional_data['email'] = $email;
//
//			$this->saml_auth->update($id, $additional_data);
//			$this->session->set_flashdata('message', "<p>User Updated</p>");
//			redirect("auth", 'refresh');
//			exit;
//
//		}
//
//
//		if ($this->form_validation->run() == true && $this->saml_auth->register($username, $password, $email, $additional_data))
//		{ //check to see if we are creating the user
//			//redirect them back to the admin page
//			$this->session->set_flashdata('message', "<p>User Created</p>");
//			redirect("auth", 'refresh');
//			exit;
//		}
//
//		 //display the create user form
//		//set the flash data error message if there is one
//		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->saml_auth->errors() ? $this->saml_auth->errors() : $this->session->flashdata('message')));
//
//		$this->data['first_name'] = array('name' => 'first_name',
//			'id' => 'first_name',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('first_name'),
//		);
//		$this->data['last_name'] = array('name' => 'last_name',
//			'id' => 'last_name',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('last_name'),
//		);
//		$this->data['email'] = array('name' => 'email',
//			'id' => 'email',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('email'),
//		);
//		$this->data['company'] = array('name' => 'company',
//			'id' => 'company',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('company'),
//		);
//		$this->data['phone1'] = array('name' => 'phone1',
//			'id' => 'phone1',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('phone1'),
//		);
//		$this->data['phone2'] = array('name' => 'phone2',
//			'id' => 'phone2',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('phone2'),
//		);
//		$this->data['phone3'] = array('name' => 'phone3',
//			'id' => 'phone3',
//			'type' => 'text',
//			'value' => $this->form_validation->set_value('phone3'),
//		);
//		$this->data['password'] = array('name' => 'password',
//			'id' => 'password',
//			'type' => 'password',
//			'value' => $this->form_validation->set_value('password'),
//		);
//		$this->data['password_confirm'] = array('name' => 'password_confirm',
//			'id' => 'password_confirm',
//			'type' => 'password',
//			'value' => $this->form_validation->set_value('password_confirm'),
//		);
//		$this->load->view('auth/manage_user', $this->data);
//
//	}
//
//	function _get_csrf_nonce()
//	{
//		$this->load->helper('string');
//		$key = random_string('alnum', 8);
//		$value = random_string('alnum', 20);
//		$this->session->set_flashdata('csrfkey', $key);
//		$this->session->set_flashdata('csrfvalue', $value);
//
//		return array($key => $value);
//	}
//
//	function _valid_csrf_nonce()
//	{
//		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
//				$this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
//		{
//			return TRUE;
//		}
//		else
//		{
//			return FALSE;
//		}
//	}

}
