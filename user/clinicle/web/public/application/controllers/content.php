<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

    include_once("web_init.php");

class Content extends CI_Controller {
	private $_apiUser;
    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -  
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in 
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
	
	public function getApiUser() {
		return $this->_apiUser;
	}
	
	private function _isMaintenanceMode() {
		$add = ADD::sys();
            
		if ($add->config('maintenance') && !$this->ion_auth->is_admin()) {
			$data['title'] = "Essential Maintenance";
			$data['header'] = $this->load->view('header', $data, TRUE);
			$data['message'] = $add->config('maintenance');

			$this->load->view('maintenance', $data);
			return true;
		}
	}
	
	private function _addNotificationMessage(&$data) {
		if (ADD::sys()->config('notification')) {
			$data['notify'] = ADD::sys()->config('notification');
		}
	}
	
	private function _urlHasApiKey() {
		$apiKey = $this->input->get("apikey");
		if ($apiKey) {
			$user = $this->ion_auth->authenticate_user_with_apikey($apiKey);
			if(!$user) {
				ob_get_clean();
				$json = json_encode(array("error"=>1,"error_text"=>"Not able to authenticate user. API key '$apiKey' does not match"));
				$this->output->set_content_type("application/json")->set_output($json);
				return true;
			}
			$this->_apiUser = $user;
		}
		return false;
	}
	
	private function _setupSystem() {
		$add = ADD::sys();
        $add->setParams($this->input->get(NULL, TRUE));


		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == "POST") {
			$this->_clearNotifications();
		}
		
		init_add_from_ci($this);
		
		$web = ADD::web();

        if($this->session->userdata('add_messages')) {
            $web->setMessages($this->session->userdata('add_messages'));
        }
        if($this->session->userdata('add_warnings')) {
            $web->setWarnings($this->session->userdata('add_warnings'));
        }
	}
	
	public function _clearNotifications() {
		//$this->session->set_userdata('add_messages', null);
       // $this->session->set_userdata('add_warnings', null);
	}
	public function _isRedirected($output) {
		if (isset($output['redirect'])) {
		    CLE::info("redirect:".$output['redirect']);
			$web = ADD::web();
			$this->session->set_userdata('add_messages', $web->getMessages());
			$this->session->set_userdata('add_warnings', $web->getWarnings());
			redirect($output['redirect']);
			return true;
		}
	}
	
	public function _isOutputContent($output) {
		if (!isset($output['content'])) return false;
		$content = $output['content'];
		ob_get_clean();
		
		if($content->headers()) {
			foreach($content->headers() as $header) {
				$this->output->set_header($header);
			}
		}
		$this->output->set_content_type($content->mimeType())->set_output($content->output());
		return true;
	}
	public function _dealWithAutoRefresh() {
		$add = ADD::sys();
		if ($add->hasState(ADD_System::AUTOREFRESH)) {
			$refreshUri = $this->session->userdata("add_refresh_uri");
			if($_SERVER["REQUEST_URI"]!=$refreshUri) {
				$this->session->set_userdata("add_refresh_count",0);
				$this->session->set_userdata("add_refresh_uri",$_SERVER["REQUEST_URI"]);
			}
			else {
				$refreshCount = $this->session->userdata("add_refresh_count");
				$this->session->set_userdata("add_refresh_count",$refreshCount+1);
				if($refreshCount * $add->config('in_use_refresh_time')>=$add->config('auto_logout')) {
					$dest = urlencode($_SERVER["REQUEST_URI"]);
					redirect('auth/logout' . ($dest ? "?dest=$dest" : ""), 'refresh');
				}
			}
		}
		else {
			$this->session->set_userdata("add_refresh_count",0);
		}
	}
	
	public function _loadViewContent($output,$data) {
		$view = ADD_Util::val('view',$output,'content');
		$status = ADD_Util::val('status',$output);
		$this->output->set_header('Content-Type:text/html;charset=utf-8');
		if($status) $this->output->set_status_header($status);
		
		$this->load->view($view, $data);
	}
	
	public function _passwordExpired() {
		if($this->session->userdata('password_expired')) {
			redirect("/user/change");
		}
	}
	
    public function index() {
	
        try {
            $data = array();
            $this->load->library('session');
            $this->load->helper('url');
            $this->load->library('ion_auth');
			
			$this->_addNotificationMessage($data);
            
			if($this->_isMaintenanceMode($data)) return;
			
			if($this->_passwordExpired()) return;
			
			$add = ADD::sys();
            $add->setState('admin', $this->ion_auth->is_admin());
			
			if($this->_urlHasApiKey()) return;
			
			$this->_setupSystem();
			
			$web = ADD::web();
			$output = $web->createOutput(array(
                "path" => $this->uri->uri_string(),
                "method" => $_SERVER['REQUEST_METHOD'],
                "values" => $this->input->post(NULL, TRUE)
            ));

            $registry = $add->getService(\ClinicLE\System\Service\CoreServices::FACTORY_REGISTRY);
            $registry->getStorage()->storeRegistry($registry);

			if($this->_isRedirected($output)) return;
			

			if ($this->_isOutputContent($output)) return;
            
			$this->_loadViewContent($output,$data);
            $this->_clearNotifications();



        } catch (Exception $ex) {
            $this->load->view('error', array("no_add" => true, "error" => $ex->getMessage()));
			$this->output->set_status_header(500);
        }
    }
}
