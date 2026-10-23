<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("web_init.php");
use ClinicLE\System\Service\CoreServices;

class User extends CI_Controller {
	function __construct()
	{
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->library('ion_auth');
		$this->load->database();
		$this->load->helper('url');

		$this->lang->load('auth');
		$this->load->helper('language');

		init_add_from_ci($this);
		
		ADD::web()->createBaseContent();
	}

	
	function create_role() {
		$roleId = $this->ion_auth->create_role($this->input->get('role_name'));
		ADD::web()->createBaseContent();
		redirect("/user/edit_role/".$roleId);
	}
	function edit_role($id) {
		$add = ADD::sys();
		$data = array();
		list($uniquePerms,$actualPerms)=$add->getAccessManager()->getAllPermissions();
		$data['uniquePerms']=$uniquePerms;
		$data['actualPerms']=$actualPerms;
		//$currRoleId = $this->input->get('id');
		$data['currRoleId']=$id;
		$allGroups=$this->ion_auth->groups();
		$groupByName = array();

		foreach($allGroups->result() as $group) {
			$groupByName[$group->name]=$group;
		}
		$currGroupsByName = array();
		$currRole = $this->ion_auth->role($id)->row();
		$data['currRole']=$currRole;
		
		if ($currRole) {
			$roleGroups = $this->ion_auth->get_role_groups($currRole);
			if ($roleGroups) {
				
				foreach($roleGroups->result() as $roleGroup) {
					$currGroupsByName[$roleGroup->name] = 1;
				}
			}
		}
		$data['message']=$this->ion_auth->errors();
		$data['currGroups'] = $currGroupsByName;
		
		ADD::web()->createBaseContent();
		$this->load->view("user/edit_role", $data);
	}
	function store_role($id) {
		$add = ADD::sys();
		list($uniquePerms,$actualPerms)=$add->getAccessManager()->getAllPermissions();
		
		$allGroups=$this->ion_auth->groups();
		$groupByName = array();

		foreach($allGroups->result() as $group) {
			$groupByName[$group->name]=$group->id;
		}

		$post = $this->input->post(NULL,true);

		//$this->ion_auth->remove_from_group(NULL,$currUserId);
		$roleGroups = array();
		foreach($actualPerms as $modName => $modPerms) {
			foreach($modPerms as $pageName => $pagePerms) {
				foreach($pagePerms as $pagePerm => $flag) {
					$permType = $pagePerm."_".$modName."_".$pageName;
					$userPerm = $this->input->post($permType);
					if ($userPerm!="on") continue;

					if(!isset($groupByName[$permType])) {
						$groupId = $this->ion_auth->create_group($permType,$permType);
						$groupByName[$permType]=$groupId;
					}
					$roleGroups[]=$permType;
				}
			}
		}
		$this->ion_auth->update_role($id,$roleGroups,$groupByName);
		
		redirect("/user/edit_role/".$id);
	}
//extract possible roles from modules and pages
	function roles()
	{
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}
		
		$currRoleId = $this->input->get('id');
		$data = array();
		$data['currRoleId']=$currRoleId;
		$data['title']="Roles";
		
		$add = ADD::sys();
		$data['allRoles'] = $this->ion_auth->roles()->result();
		$roleMembers = array();
		foreach($data['allRoles'] as $role) {
			$roleMemberList = $this->db->where("role_id",$role->id)->join("users_roles","users.id=users_roles.user_id")->get("users")->result();
			$roleMembers[$role->id] = array_map(function($value) {
				return $value->username;
			},$roleMemberList);
		}
		$data['roleMembers']=$roleMembers;
		
		
		if ($this->input->get('create')) {
			$roleId = $this->ion_auth->create_role($this->input->get('role_name'));
			redirect("/user/roles?id=".$roleId);
		}
		
		if ($this->input->post('postback')==1) {
			
			
		}
		
		$currRole = $this->ion_auth->role($currRoleId)->row();
		$data['currRole']=$currRole;
		
		$currGroupsByName = array();
		if ($currRole) {
			$roleGroups = $this->ion_auth->get_role_groups($currRole);
			if ($roleGroups) {
				
				foreach($roleGroups->result() as $roleGroup) {
					$currGroupsByName[$roleGroup->name] = 1;
				}
			}
		}
		$data['message']=$this->ion_auth->errors();
		$data['currGroups'] = $currGroupsByName;
		ADD::web()->createBaseContent();
           
		$this->load->view("user/roles", $data);
	}

	
	function change()
	{
		$add = ADD::sys();
		
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}
		$this->form_validation->set_rules('current_password', "Current password", 'required|callback_db_pwd_check');
		$this->form_validation->set_rules('new_password', "New password", 'required|min_length[8]|max_length[12]|callback_pwd_valid_check');
		$this->form_validation->set_rules('confirm_password', "Confirm password", 'required|matches[new_password]');
	
		$user = $this->ion_auth->user()->row();
		
		$data['title']="Change password for ".$user->username;
		
		if ($this->session->userdata('password_expired')==1) $data['info']="Your current password is temporary. Please enter a new one below.";
		if ($this->session->userdata('password_expired')==2) $data['info']="Your current password has now expired. Please enter a new one below.";
		if ($this->input->get('willexpire')) $data['info']="Your current password will expire in the next 24 hours. Please enter a new one below.";
		
		if ($this->form_validation->run() == TRUE) {
			$reset = $this->ion_auth->reset_password($user->username,$this->input->post('new_password'));
			if ($reset) {
				$this->ion_auth->update($user->id,array("last_changed"=>time()));
				$this->session->set_userdata('add_messages',array("Your password was changed successfully!"));
				$this->session->unset_userdata('password_expired');
				ADD::sys()->getAccessManager()->setUser($user);
				$web = ADD::web();
				$web->doUserLogin(ADD::sys()->getAccessManager());
					
				redirect("/");
			}
			else {
				
				$data['message']=$this->ion_auth->errors();
				$this->load->view("user/change",$data);
				return;
			}
		}
		$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
		
		
		$this->load->view("user/change", $data);
		
	}
        
        //reset password
	function forgotten()
	{
		$add = ADD::sys();
		$data = array();
		if ($this->ion_auth->logged_in()) {
			redirect('/', 'refresh');
		}
        if($this->session->userdata('password_expired')) {
			redirect("/user/change");
		}
                $this->form_validation->set_rules('identity', "Identity", 'required');
                
                if ($this->form_validation->run() == TRUE) {
                    if($this->populateResetUser() && $this->populateResetQuestion($this->resetUser->username)) {
                        $data['identity']=$this->resetUser->username;
                        $data['reset_question']=$this->resetQuestion;
                        $this->load->view("user/forgot_step2",$data);
                        return;
                    }
                }

		$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
                $this->load->view("user/forgot_step1",$data);

	}
        
        function populateResetUser() {
            $this->resetUser = $this->db->select("users.*")->from("users")->where("users.username",$this->input->post('identity'))->get()->row();
            if (!$this->resetUser)
            {
                $this->ion_auth->set_error('reset_question_user_not_defined');
                return false;
            }
            if (!isset($this->resetUser->reset_question_id))
            {
                $this->session->set_userdata('add_warnings',array("Sorry - you have not previously set up your password reset question!"));
                redirect("/","refresh");
            }
            $this->session->set_userdata('reset_user',$this->resetUser->username);
            return true;
        }
        
        function populateResetQuestion($identity) {
            $this->resetQuestion = $this->db->select("r.*")->from("users u left join reset_questions r on r.id = u.reset_question_id")->where("u.username",$identity)->get()->row();
            if(!$this->resetQuestion) {
                 $this->ion_auth->set_error('reset_question_not_defined');
                 return false;
            }
            return true;
        }
        function forgotten2() {
            if ($this->ion_auth->logged_in()) {
                    redirect('/', 'refresh');
            }
            if($this->session->userdata('password_expired')) {
                    redirect("/user/change");
            }
            $data = array();
            $this->form_validation->set_rules('identity', "Identity");   
            $this->form_validation->set_rules('reset_answer', "Reset answer", 'required');
            
            if($this->populateResetUser()) {
                if ($this->populateResetQuestion($this->input->post('identity'))) {
                    $data['reset_question']=$this->resetQuestion;
                    if($this->form_validation->run() == TRUE) {
                        
                        $enteredHashedAnswer = sha1($this->resetUser->salt.$this->input->post('reset_answer'));
                        $userHashedAnswer = $this->resetUser->reset_answer_hash;
                        error_log("enteredAnswer:$enteredHashedAnswer (".$this->input->post('reset_answer').") -> $userHashedAnswer");
                        $reset = $enteredHashedAnswer === $userHashedAnswer;
                        if($reset === true) {
							$resetEmailer = ADD::sys()->getService(CoreServices::USER_PASSWORD_EMAILER);
							$userData = $resetEmailer->createPasswordData();
							$this->session->set_userdata('add_messages',array($resetEmailer->getUserMessage()));
                               
                            $this->ion_auth->update($this->resetUser->id, $userData);
                            $resetEmailer->sendResetEmail($this->resetUser);
							
                            redirect('/','refresh');
                        }
                        else {
                            $this->ion_auth->set_error('reset_answer_does_not_match');
                        }
                    }
                }
            }
            
            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
                
            $this->load->view("user/forgot_step2",$data);       
        }
        //reset password
	function question()
	{
		$add = ADD::sys();
		
		if (!$this->ion_auth->logged_in()) {
			redirect('auth/login', 'refresh');
		}
                if($this->session->userdata('password_expired')) {
			redirect("/user/change");
		}
		$this->form_validation->set_rules('reset_answer',"Reset answer",'required|min_length[6]|max_length[30]');
		$this->form_validation->set_rules('reset_question',"Reset question",'required');
		
                $user = $this->ion_auth->user()->row();
                $data['questions'] = $this->db->select("reset_questions.*")->get('reset_questions')->result();
		
                $user = $this->ion_auth->user()->row();
		$data['title']="Reset password question for ".$user->username;
		
		if ($this->form_validation->run() == TRUE) {
			$change = $this->ion_auth->change_question($user->username, $this->input->post('reset_question'),$this->input->post('reset_answer'));
                        
			if ($change) {
                            $this->session->set_userdata('add_messages',array("Your reset question was updated successfully!"));
                            redirect("/");
			}
			else {
                            $data['message']=$this->ion_auth->errors();
                            $this->load->view("user/question",$data);
                            return;
			}
		}
		$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
		
		
		$this->load->view("user/question", $data);
		
	}

	function db_pwd_check($pass) {
		$user = $this->ion_auth->user()->row();
		if (!$this->ion_auth->hash_password_db($user->id,$pass)) {
			$this->form_validation->set_message('db_pwd_check', 'The %s field does not match the entry in the database');
			return false;
		}
		return true;
	}
	
	function pwd_valid_check($pass) {
		if (!preg_match("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/",$pass)) {
			$this->form_validation->set_message('pwd_valid_check', 'The %s field should contain at least 1 upper-case character and 1 number');
			return false;
		}
		return true;
	}
	

}
