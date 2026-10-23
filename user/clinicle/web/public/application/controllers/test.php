<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

    include_once("web_init.php");

class Test extends CI_Controller {
	
    public function index() {
		$this->load->view('test');
    }
}
