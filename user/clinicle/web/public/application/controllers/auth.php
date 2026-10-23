<?php
defined('BASEPATH') OR exit('No direct script access allowed');
include("web_init.php");
use ClinicLE\System\Service\CoreServices;
use ClinicLE\System\Service\Interpolate;
class Auth extends CI_Controller {

    function __construct() {
    	try {
	        parent::__construct();
			$this->system = ADD::sys();
			$this->load->library('ion_auth');
	        $this->load->library('form_validation');
	        $this->load->helper('url');
	        $this->load->library('carabiner');
	
            $addCss = array(
                    array('bootstrap/css/bootstrap.min.css','screen'),
                    array('css/jquery-ui.css','screen'),
                    array('bootstrap/css/bootstrap-responsive.min.css','screen'),
                    array('css/add.css','screen')
            );
            $this->carabiner->css($addCss);
				
	        // Load MongoDB library instead of native db driver if required
	        $this->config->item('use_mongodb', 'ion_auth') ?
	                        $this->load->library('mongo_db') :
	                        $this->load->database();
	
	        $this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));
	
	        $this->lang->load('auth');
	        $this->load->helper('language');
	        $add = ADD::sys();
	        if (!ADD::sys()->config('maintenance')) init_add_from_ci($this);
				
	        }
		catch(Exception $ex) {
				$this->load->view('error',array("no_add"=>true,"error"=>$ex->getMessage()));
			}
    }
        //redirect if needed, otherwise display the user list
        function index() {
/*
            if (!$this->ion_auth->logged_in()) {
                //redirect them to the login page
                redirect('auth/login', 'refresh');
 * 
 */
            if (!$this->ion_auth->is_admin()) {
				
                //redirect them to the home page because they must be an administrator to view this
                redirect('/', 'refresh');
            } else {
                //set the flash data error message if there is one
                $this->data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

                //list the users
                $users = $this->ion_auth->users()->result();
                foreach($users as $user) {
					$roles = $this->ion_auth->get_user_roles($user->id)->result();
					$userRoles[$user->id]=implode(", ",array_map(function($value) { return $value->name; },$roles));
				}
				$this->data['users']=$users;
				$this->data['userRoles']=$userRoles;
				/*
				foreach ($this->data['users'] as $k => $user) {
                    $this->data['users'][$k]->groups = $this->ion_auth->get_users_groups($user->id)->result();
                }
				*/
                $this->_render_page('user/list', $this->data);
            }
        }

        //log the user in
        function login() {
            $add = ADD::sys();
            if ($add->config('maintenance') && (!$this->input->get('override') && !$this->input->post('override'))) {
                $data['title'] = "Essential Maintenance";
                $data['header'] = $this->load->view('header', $data, TRUE);
                $data['message'] = $add->config('maintenance');

                $this->load->view('maintenance', $data);
                return;
            }
            if ($add->config('notification')) {
                $this->data['notify'] = $add->config('notification');
            }
            $this->data['title'] = "Login";

            //validate form input
            $this->form_validation->set_rules('identity', 'Identity', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required');

            if ($this->ion_auth->logged_in()) {
                redirect('/', 'refresh');
            }

            if ($this->form_validation->run() == true) {
                //check to see if the user is logging in
                //check for "remember me"
                $remember = (bool) $this->input->post('remember');

                if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                    //if the login is successful
                    //redirect them back to the home page
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    $user = $this->ion_auth->user()->row();
                    $add = ADD::sys();
                    $addSessId = ADD_Util::token();
					
                    
					$this->session->set_userdata("add_sessionid",$addSessId);
					$accessManager = ADD::sys()->getAccessManager();
					$accessManager->setSessionData($this->session->all_userdata());
					$accessManager->setUser($user);
					
					if (!$user->last_changed) {
                        $this->session->set_userdata('password_expired', 1);
                        redirect('user/change', 'refresh');
                    }

                    if (time() > $user->last_changed + (3 * 30 * 24 * 60 * 60)) {
                        $this->session->set_userdata('password_expired', 2);
                        redirect('user/change', 'refresh');
                    }

                    if (time() > $user->last_changed + ((3 * 30 * 24 * 60 * 60) - (24 * 60 * 60))) {
                        redirect('user/change?willexpire=1', 'refresh');
                    }
					
                    $dest = $this->input->post('dest');
                    
                   	$web = ADD::web();
					$web->doUserLogin(ADD::sys()->getAccessManager());
					
					$this->session->set_userdata('add_messages', $web->getMessages());
					$this->session->set_userdata('add_warnings', $web->getWarnings());
			 		redirect($dest ? $this->input->post('dest') : '/', 'refresh');
                } else {
                    //if the login was un-successful
                    //redirect them back to the login page
                    $this->session->set_userdata('add_errors', $this->ion_auth->errors());
                    redirect('/', 'refresh'); //use redirects instead of loading views for compatibility with MY_Controller libraries
                }
            } else {
                //the user is not logging in so display the login page
                //set the flash data error message if there is one
                $errors = (validation_errors()) ? validation_errors() : $this->session->userdata('add_errors');
				$this->session->set_userdata('add_errors',$errors);
                $this->data['message'] = $errors;

                $this->data['identity'] = array('name' => 'identity',
                    'id' => 'identity',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('identity'),
                );
                $this->data['password'] = array('name' => 'password',
                    'id' => 'password',
                    'type' => 'password',
                );

                $this->data['override'] = array('name' => 'override',
                    'id' => 'override',
                    'type' => 'hidden',
                    'value' => $this->input->get('override'),
                );


                redirect('/', 'refresh');
            }
        }

		//flag to see if logged in
        function loggedin() {
			$loggedIn = $this->ion_auth->logged_in();
			$content = array("logged_in"=>$loggedIn);
			$this->output->set_content_type("application/json")->set_output(json_encode($content));
        }
		
        //log the user out
        function logout() {
        	if(!$this->ion_auth->logged_in()) redirect('auth/login', 'refresh');
			
            $add = ADD::sys();
            $this->data['title'] = "Logout";

            ADD::web()->doUserLogout(ADD::sys()->getAccessManager());
            
            $logout = $this->ion_auth->logout();
			
			$this->session->sess_destroy();
			
            //redirect them to the login page
            //$this->session->set_flashdata('message', $this->ion_auth->messages());
			redirect('/', 'refresh');
        }

        //activate the user
        function activate($id, $code = false) {
            if ($code !== false) {
                $activation = $this->ion_auth->activate($id, $code);
            } else if ($this->ion_auth->is_admin()) {
                $activation = $this->ion_auth->activate($id);
            }

            if ($activation) {
                //redirect them to the auth page
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("auth", 'refresh');
            } else {
                //redirect them to the forgot password page
                $this->session->set_flashdata('message', $this->ion_auth->errors());
                redirect("auth/forgot_password", 'refresh');
            }
        }

        //deactivate the user
        function deactivate($id = NULL) {
            $id = $this->config->item('use_mongodb', 'ion_auth') ? (string) $id : (int) $id;

            $this->load->library('form_validation');
            $this->form_validation->set_rules('confirm', $this->lang->line('deactivate_validation_confirm_label'), 'required');
            $this->form_validation->set_rules('id', $this->lang->line('deactivate_validation_user_id_label'), 'required|alpha_numeric');

            if ($this->form_validation->run() == FALSE) {
                // insert csrf check
                $this->data['csrf'] = $this->_get_csrf_nonce();
                $this->data['user'] = $this->ion_auth->user($id)->row();

                $this->_render_page('auth/deactivate_user', $this->data);
            } else {
                // do we really want to deactivate?
                if ($this->input->post('confirm') == 'yes') {
                    // do we have a valid request?
                    if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
                        show_error($this->lang->line('error_csrf'));
                    }

                    // do we have the right userlevel?
                    if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                        $this->ion_auth->deactivate($id);
                    }
                }

                //redirect them back to the auth page
                redirect('auth', 'refresh');
            }
        }

         //create a new user
        function create_user() {
        	$add = ADD::sys();
            $this->data['title'] = "Create User";

            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                redirect('auth', 'refresh');
            }

            //validate form input
            $this->form_validation->set_rules('username', $this->lang->line('create_user_validation_username_label'), 'required|xss_clean|callback_username_check');
            $this->form_validation->set_rules('first_name', $this->lang->line('create_user_validation_fname_label'), 'required|xss_clean');
            $this->form_validation->set_rules('last_name', $this->lang->line('create_user_validation_lname_label'), 'required|xss_clean');
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email');
            $this->form_validation->set_rules('phone', $this->lang->line('create_user_validation_phone_label'), 'xss_clean');
            $this->form_validation->set_rules('company', $this->lang->line('create_user_validation_company_label'), 'xss_clean');

			$addConf = $add->getConfig();
				
			$siteTitle = $addConf->getSetting('site_title','Airway Disease Database');
            $siteName=$addConf->getSetting('site_name','ADD');
			$siteHost=$addConf->getSetting('site_host','lrbaddlive.lrbru.xuhl-tr.nhs.uk');
			$siteAdminEmail=$addConf->getSetting('site_admin_email','robert.free@uhl-tr.nhs.uk');
			$cueFile = $add->getEnvironment()->getConfigPath()."/tpl/create_user_email.tpl";
			if(file_exists($cueFile)) {
				$createUserEmail = file_get_contents($cueFile);
			}
			else {
				$createUserEmail = "A new account has been created for you in [{site_name}]. The temporary login details are:\n"
                    . "Username:[{user}]\nPassword:[{pass}]\n\n"
                    . "To access {{site_name}} please go to this web site: http://[{site_host}].\n"
                    . "If you have problems logging in please email [{admin_email}]";
			}
			if ($this->form_validation->run() == true) {
                $username = $this->input->post('username');
                $email = $this->input->post('email');
                $username = $this->input->post('username');
                $password = substr(hash('sha512', rand()), 0, 8);
                $emailMsg = $this->input->post('email_msg');



                $additional_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                );
                $userRoles = array();
                $postRoles = $this->input->post('roles');
                $allRoles = $this->ion_auth->roles()->result();

                foreach ($allRoles as $role) {
                    if (isset($postRoles[$role->id]) && $postRoles[$role->id] == "on") {
                        $userRoles[] = $role->id;
                    }
                }

                //$additional_data['roles'] = implode(",", $userRoles);

                $user_id = $this->ion_auth->register($username, $password, $email, $additional_data, array());
				
				
                if ($user_id) {
					$this->ion_auth->update_user_roles($user_id,$userRoles);
				
                    //$this->ion_auth->update_user_groups($user_id, $userRoles);
                    $user = $this->ion_auth->user($user_id)->row();
                    //check to see if we are creating the user
                    //redirect them back to the admin page
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
					$add = ADD::sys();
					
                    //redirect("auth", 'location');
                    $siteTitle = $add->getConfig()->getSetting('site_title','Airways Disease Database');
					
					$cuInterpolate = $i = new Interpolate();
					$emailMsg = $cuInterpolate->render($emailMsg,array_merge($additional_data, array("user"=>$username,"pass"=>$password,"site_host"=>$siteHost,"admin_email"=>$siteAdminEmail,"site_title"=>$siteTitle,"site_name"=>$siteName)));
            
                    $sent = mail($email, "New $siteTitle user account", $emailMsg, "From: $siteTitle <lrbru-noreply@uhl-tr.nhs.uk>");

                    if ($sent) {
                        $this->session->set_flashdata('message', "An email was sent to " . $user->first_name . " " . $user->last_name . " (" . $email . ") with login details.  It is displayed below in case it is not received:<br/><pre>" . $emailMsg . "</pre>");
                    } else {
                        $emailContent = "An email could not be sent to " . $user->first_name . " " . $user->last_name . " (" . $email . "). It is displayed below as information:<br/><pre>" . $emailMsg . "</pre>";
                        $this->session->set_flashdata('message', $emailContent);
                    }
                } else {
                    $this->session->set_flashdata('message', "An unexpected error occurred. Unable to create new user");
                }
                redirect("auth", 'location');
            } else {
                //display the create user form
                //set the flash data error message if there is one
                $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

                $this->data['email_msg'] = array(
                    'name' => 'email_msg',
                    'id' => 'email_msg',
                    'cols'=>'80',
                    'rows'=>'5',
                    'style'=>'width:600px',
                    'value' => $createUserEmail
                );
                $this->data['username'] = array(
                    'name' => 'username',
                    'id' => 'username',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('username'),
                );
                $this->data['first_name'] = array(
                    'name' => 'first_name',
                    'id' => 'first_name',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('first_name'),
                );
                $this->data['last_name'] = array(
                    'name' => 'last_name',
                    'id' => 'last_name',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('last_name'),
                );
                $this->data['email'] = array(
                    'name' => 'email',
                    'id' => 'email',
                    'type' => 'text',
                    'value' => $this->form_validation->set_value('email'),
                );
                $roles = $this->ion_auth->roles()->result();
                $roleData = array();
                foreach ($roles as $role) {
                    $roleData[$role->name] = array(
                        'name' => 'roles[' . $role->id . ']',
                        'id' => $role->id,
                        'type' => 'checkbox',
                        'value' => 'on',
                    );
                }
                $this->data['roles'] = $roleData;

                $this->_render_page('user/create_user', $this->data);
            }
        }

        //edit a user
        function edit_user($id) {
        	$add=ADD::sys();
            $this->data['title'] = "Edit User";

            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                redirect('auth', 'refresh');
            }

            $user = $this->ion_auth->user($id)->row();
            $groups = $this->ion_auth->groups()->result_array();
            $currentGroups = $this->ion_auth->get_users_groups($id)->result();

            //validate form input
            $this->form_validation->set_rules('username', $this->lang->line('edit_user_validation_username_label'), 'required|xss_clean');
            $this->form_validation->set_rules('first_name', $this->lang->line('edit_user_validation_fname_label'), 'required|xss_clean');
            $this->form_validation->set_rules('last_name', $this->lang->line('edit_user_validation_lname_label'), 'required|xss_clean');
            $this->form_validation->set_rules('phone', $this->lang->line('edit_user_validation_phone_label'), 'xss_clean');
            $this->form_validation->set_rules('company', $this->lang->line('edit_user_validation_company_label'), 'xss_clean');
			$this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email');
            
            if (isset($_POST) && !empty($_POST)) {
                
                if ($this->form_validation->run() === TRUE) {
                    $data = array(
                        'username' => $this->input->post('username'),
                        'first_name' => $this->input->post('first_name'),
                        'last_name' => $this->input->post('last_name'),
                    	'email' => $this->input->post('email'),
					);

                    $userRoles = array();
                    $postRoles = $this->input->post('roles');
                    $allRoles = $this->ion_auth->roles()->result();

                    foreach ($allRoles as $role) {
                        if (isset($postRoles[$role->id]) && $postRoles[$role->id] == "on") {
                            $userRoles[] = $role->id;
                        }
                    }
                    $this->ion_auth->update_user_roles($id, $userRoles);

                    $data['roles'] = implode(",", $userRoles);
					$this->ion_auth->update($user->id, $data);
					
                    //update the password if it was posted
                    
                    //redirect them back to the admin page
                    $this->session->set_flashdata('message', "User Saved");
                    
					if ($this->input->post('reset_password')) {
						
						$resetEmailer = $this->system->getService(CoreServices::USER_PASSWORD_EMAILER);
						$userData = $resetEmailer->createPasswordData();
						$this->ion_auth->update($user->id, $userData);

						$resetEmailer->sendResetEmail($user);
						$this->session->set_flashdata('message', "An email was sent to " . $user->first_name . " " . $user->last_name . " (" . $user->email . ") containing a new temporary password.)<br/>It is displayed below as information:<br/><pre>" . $resetEmailer->getLastResetInfo()."</pre>");
                    }
					
                    redirect("auth", 'refresh');
                }
            }

            //display the edit user form
            $this->data['csrf'] = $this->_get_csrf_nonce();

            //set the flash data error message if there is one
            $this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

            //pass the user to the view
            $this->data['user'] = $user;
            $this->data['groups'] = $groups;
            $this->data['currentGroups'] = $currentGroups;

            $this->data['username'] = array(
                'name' => 'username',
                'id' => 'username',
                'type' => 'text',
                'value' => $this->form_validation->set_value('username', $user->username),
            );
            $this->data['first_name'] = array(
                'name' => 'first_name',
                'id' => 'first_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('first_name', $user->first_name),
            );
            $this->data['last_name'] = array(
                'name' => 'last_name',
                'id' => 'last_name',
                'type' => 'text',
                'value' => $this->form_validation->set_value('last_name', $user->last_name),
            );
            $this->data['company'] = array(
                'name' => 'company',
                'id' => 'company',
                'type' => 'text',
                'value' => $this->form_validation->set_value('company', $user->company),
            );
            $this->data['phone'] = array(
                'name' => 'phone',
                'id' => 'phone',
                'type' => 'text',
                'value' => $this->form_validation->set_value('phone', $user->phone),
            );
             $this->data['reset_password'] = array(
                'name' => 'reset_password',
                'id' => 'reset_password',
                'type' => 'submit',
                'value' => 'on',
                'content'=>'<i class="icon-user"></i>&nbsp;Reset password',
				 'class'=>'btn'
            );
			$this->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'value' => $this->form_validation->set_value('email', $user->email),
            );
				
            $roles = $this->ion_auth->roles()->result();
            $userRoles = $this->ion_auth->get_user_roles($id)->result();
            $urByName = array();
            foreach ($userRoles as $ur) {
                $urByName[$ur->name] = true;
            }

            $roleData = array();
            foreach ($roles as $role) {
                $roleItem = array(
                    'name' => 'roles[' . $role->id . ']',
                    'id' => $role->id,
                    'type' => 'checkbox',
                    'value' => 'on',
                );
                if (isset($urByName[$role->name]))
                    $roleItem['checked'] = 'checked';
                $roleData[$role->name] = $roleItem;
            }
            $this->data['roles'] = $roleData;
            $this->_render_page('auth/edit_user', $this->data);
        }

        function _get_csrf_nonce() {
            $this->load->helper('string');
            $key = random_string('alnum', 8);
            $value = random_string('alnum', 20);
            $this->session->set_flashdata('csrfkey', $key);
            $this->session->set_flashdata('csrfvalue', $value);

            return array($key => $value);
        }

        function _valid_csrf_nonce() {
            if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
                    $this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue')) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        function _render_page($view, $data = null, $render = false) {
			ADD::web()->createBaseContent();
            $this->viewdata = (empty($data)) ? $this->data : $data;

            $view_html = $this->load->view($view, $this->viewdata, $render);

            if (!$render)
                return $view_html;
        }

    }
    
